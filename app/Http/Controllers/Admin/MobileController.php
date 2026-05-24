<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use App\Models\Admin\Invoice;
use App\Models\Admin\Subscription;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    /**
     * Mobile Clients View
     */
    public function clients(Request $request)
    {
        $query = Clients::where('is_active', 1);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $totalCount = $query->count();

        $query->withSum('invoices as remaining_balance', 'remaining_amount')
            ->orderBy('id', 'desc');

        $clients = $query->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('dashbord.mobile_view.partials.clients_list', compact('clients'))->render(),
                'total' => $totalCount
            ]);
        }

        return view('dashbord.mobile_view.clients', compact('clients', 'totalCount'));
    }

    /**
     * Mobile Search Clients (Returns Partial HTML)
     */
    public function searchClients(Request $request)
    {
        $query = Clients::where('is_active', 1);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $totalCount = $query->count();

        $query->withSum('invoices as remaining_balance', 'remaining_amount')
            ->orderBy('id', 'desc');

        $clients = $query->paginate(10);

        if ($request->ajax() || $request->has('page')) {
            return response()->json([
                'html' => view('dashbord.mobile_view.partials.clients_list', compact('clients'))->render(),
                'total' => $totalCount
            ]);
        }

        return view('dashbord.mobile_view.clients', compact('clients', 'totalCount'));
    }

    /**
     * Mobile Invoices View
     */
    public function invoices(Request $request)
    {
        $query = Invoice::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        if ($request->get('review') === 'mine') {
            $query->where('status', 'paid')
                ->whereHas('revenues', function ($q) {
                    $q->where('collected_by', auth()->guard('admin')->id());
                });
            if ($request->filled('date_from')) {
                $query->whereDate('paid_date', '>=', $request->get('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('paid_date', '<=', $request->get('date_to'));
            }
            $query->orderBy('paid_date', 'desc');
        } else {
            $query->whereIn('status', ['unpaid', 'partial']);
        }

        $invoices = $query->with(['client', 'subscription', 'revenues.user']);
        if ($request->get('review') !== 'mine') {
            $invoices = $invoices
                ->orderByRaw("CASE tbl_invoices.status WHEN 'unpaid' THEN 0 WHEN 'partial' THEN 1 WHEN 'paid' THEN 2 ELSE 3 END")
                ->orderBy('due_date', 'asc');
        }
        $invoices = $invoices->paginate(10);

        if ($request->ajax()) {
            return view('dashbord.mobile_view.partials.invoices_list', compact('invoices'))->render();
        }

        return view('dashbord.mobile_view.invoices', compact('invoices'));
    }

    /**
     * Mobile Search Invoices (Returns Partial HTML)
     */
    public function searchInvoices(Request $request)
    {
        $query = Invoice::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%")
                ->orWhereHas('client', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        if ($request->get('review') === 'mine') {
            $query->where('status', 'paid')
                ->whereHas('revenues', function ($q) {
                    $q->where('collected_by', auth()->guard('admin')->id());
                });
            if ($request->filled('date_from')) {
                $query->whereDate('paid_date', '>=', $request->get('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('paid_date', '<=', $request->get('date_to'));
            }
            $query->orderBy('paid_date', 'desc');
        } else {
            $query->whereIn('status', ['unpaid', 'partial']);
        }

        $invoices = $query->with(['client', 'subscription', 'revenues.user']);
        if ($request->get('review') !== 'mine') {
            $invoices = $invoices
                ->orderByRaw("CASE tbl_invoices.status WHEN 'unpaid' THEN 0 WHEN 'partial' THEN 1 WHEN 'paid' THEN 2 ELSE 3 END")
                ->orderBy('due_date', 'asc');
        }
        $invoices = $invoices->paginate(10);

        if ($request->ajax()) {
            return view('dashbord.mobile_view.partials.invoices_list', compact('invoices'))->render();
        }

        return view('dashbord.mobile_view.invoices', compact('invoices'));
    }

    /**
     * Mobile Client Details View with Invoices Tabs
     */
    public function clientDetails($id)
    {
        $client = Clients::withSum('invoices as remaining_balance', 'remaining_amount')
            ->findOrFail($id);

        // Get unpaid invoices (status: unpaid or partial)
        $unpaidInvoices = Invoice::where('client_id', $id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Get paid invoices
        $paidInvoices = Invoice::where('client_id', $id)
            ->where('status', 'paid')
            ->with(['client', 'revenues.user'])
            ->orderBy('paid_date', 'desc')
            ->get();

        return view('dashbord.mobile_view.client_details', compact('client', 'unpaidInvoices', 'paidInvoices'));
    }
}
