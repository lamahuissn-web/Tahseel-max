<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Bonus\BonusSettings;
use App\Services\Bonus\SimpleBonusProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Feature 022 — the simplified bonus screens.
 *
 * Three screens, nothing else: the ranked eligible list (where the owner decides),
 * the "about to be eligible" forecast, and the four settings with a mandatory
 * preview before a rule change can open a financial commitment.
 *
 * Issuance itself stays in BonusController/BonusLedger: this controller never
 * writes an invoice.
 */
class SimpleBonusController extends Controller
{
    public function __construct(private readonly SimpleBonusProgressService $progress)
    {
    }

    /**
     * The follow-up screen: EVERY customer, not only the ones who already earned a month.
     * The owner asked for a page he can work from daily — so it carries the state, the
     * progress, the habit and the numbers, with tabs, a search box and paging.
     */
    public function index(Request $request)
    {
        $dataset = $this->progress->followUp();

        $tabs = [
            'all'      => 'الكل',
            'eligible' => 'مستحق',
            'verge'    => 'على وشك',
            'clean'    => 'منتظم',
            'late'     => 'متأخر',
        ];

        $tab = (string) $request->query('tab', 'all');
        if (! array_key_exists($tab, $tabs)) {
            $tab = 'all';
        }

        $search = trim((string) $request->query('q', ''));

        $rows = array_values(array_filter($dataset['rows'], function (array $row) use ($tab, $search): bool {
            $matchesTab = match ($tab) {
                'eligible' => $row['eligible'],
                'verge'    => $row['verge'],
                'clean'    => $row['status'] === 'clean',
                'late'     => $row['status'] === 'late',
                default    => true,
            };

            if (! $matchesTab) {
                return false;
            }

            if ($search === '') {
                return true;
            }

            $needle = mb_strtolower($search);

            return str_contains(mb_strtolower((string) $row['name']), $needle)
                || str_contains((string) $row['phone'], $needle);
        }));

        $perPage = 50;
        $total   = count($rows);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page    = min(max(1, (int) $request->query('page', 1)), $lastPage);

        return view('dashbord.bonus.followup', [
            'rows'         => array_slice($rows, ($page - 1) * $perPage, $perPage),
            'counts'       => $dataset['counts'],
            'eligibleCost' => $dataset['eligibleCost'],
            'vergeCost'    => $dataset['vergeCost'],
            'tabs'         => $tabs,
            'tab'          => $tab,
            'search'       => $search,
            'page'         => $page,
            'lastPage'     => $lastPage,
            'total'        => $total,
            'required'     => BonusSettings::monthsRequired(),
            'graceDays'    => BonusSettings::graceDays(),
            'enabled'      => BonusSettings::enabled(),
            'startDate'    => BonusSettings::countStartDate(),
        ]);
    }

    public function settings()
    {
        $months = BonusSettings::monthsRequired();
        $grace  = BonusSettings::graceDays();
        $start  = BonusSettings::countStartDate();

        $current = (new SimpleBonusProgressService($months, $grace, $start))->previewWith($months, $grace, $start);

        return view('dashbord.bonus.settings_simple', [
            'months'       => $months,
            'graceDays'    => $grace,
            'startDate'    => $start,
            'enabled'      => BonusSettings::enabled(),
            'invoiceValue' => BonusSettings::invoiceValue(),
            'current'      => $current,
            'preview'      => null,
        ]);
    }

    /**
     * A rule change can create a five-figure liability in one click, so a change
     * that makes customers eligible immediately is shown first and only applied
     * when the owner confirms it explicitly.
     */
    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'bonus_months_required'  => 'required|integer|min:1|max:120',
            'bonus_grace_days'       => 'required|integer|min:0|max:31',
            'bonus_count_start_date' => 'required|date_format:Y-m-d',
            'bonus_enabled'          => 'nullable|in:0,1',
        ]);

        $months = (int) $data['bonus_months_required'];
        $grace  = (int) $data['bonus_grace_days'];
        $start  = (string) $data['bonus_count_start_date'];
        $enabled = (string) ($data['bonus_enabled'] ?? '0');

        $changedRule = $months !== BonusSettings::monthsRequired()
            || $grace !== BonusSettings::graceDays()
            || $start !== BonusSettings::countStartDate();

        if ($changedRule && ! $request->boolean('confirmed')) {
            $preview = (new SimpleBonusProgressService($months, $grace, $start))->previewWith($months, $grace, $start);

            if ($preview['count'] > 0) {
                // Do not persist yet: show the cost and ask for the explicit yes.
                return view('dashbord.bonus.settings_simple', [
                    'months'       => $months,
                    'graceDays'    => $grace,
                    'startDate'    => $start,
                    'enabled'      => $enabled === '1',
                    'invoiceValue' => BonusSettings::invoiceValue(),
                    'current'      => (new SimpleBonusProgressService())->previewWith(
                        BonusSettings::monthsRequired(),
                        BonusSettings::graceDays(),
                        BonusSettings::countStartDate()
                    ),
                    'preview'      => $preview,
                ])->with('pendingSave', $data);
            }
        }

        BonusSettings::set('bonus_months_required', $months);
        BonusSettings::set('bonus_grace_days', $grace);
        BonusSettings::set('bonus_count_start_date', $start);
        BonusSettings::set('bonus_enabled', $enabled);
        BonusSettings::flush();

        return redirect()
            ->route('admin.bonus.settings')
            ->with('success', 'تم حفظ الإعدادات.');
    }

    /**
     * The list is the owner's decision screen, so it carries the name and the price
     * of each customer — a list of ids is useless to a human.
     *
     * @param  array<int,array<string,mixed>>  $rows
     * @return array<int,array<string,mixed>>
     */
    private function withClientNames(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $clients = DB::table('tbl_clients')
            ->whereIn('id', array_column($rows, 'clientId'))
            ->get(['id', 'name', 'phone', 'price'])
            ->keyBy('id');

        foreach ($rows as &$row) {
            $client = $clients[$row['clientId']] ?? null;

            $row['name']  = $client->name ?? ('#'.$row['clientId']);
            $row['phone'] = $client->phone ?? '';
            $row['price'] = (float) ($client->price ?? 0);
            $row['lastCreditedMonth'] = $row['lastCreditedMonth'] ?? null;
        }

        return $rows;
    }
}
