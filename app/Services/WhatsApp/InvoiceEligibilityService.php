<?php

namespace App\Services\WhatsApp;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Centralized invoice filtering for WhatsApp notifications.
 *
 * All WhatsApp send paths should use getEligibleInvoices()
 * to ensure invoices are only included when due_date <= today.
 */
class InvoiceEligibilityService
{
    /**
     * Get invoices eligible for WhatsApp reminder.
     * Only includes invoices where due_date <= today (due or overdue).
     *
     * @param int $clientId
     * @return \Illuminate\Support\Collection
     */
    public static function getEligibleInvoices($clientId)
    {
        return DB::table('tbl_invoices')
            ->where('client_id', $clientId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<=', Carbon::today())
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get ALL unpaid invoices (for admin views, reports, manual inspection).
     * Does NOT filter by due_date — use when admin needs full visibility.
     *
     * @param int $clientId
     * @return \Illuminate\Support\Collection
     */
    public static function getAllUnpaid($clientId)
    {
        return DB::table('tbl_invoices')
            ->where('client_id', $clientId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Check if a client has any eligible (due/overdue) invoices.
     *
     * @param int $clientId
     * @return bool
     */
    public static function hasEligibleInvoices($clientId)
    {
        return DB::table('tbl_invoices')
            ->where('client_id', $clientId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<=', Carbon::today())
            ->exists();
    }

    /**
     * Get the count of eligible invoices for a client.
     *
     * @param int $clientId
     * @return int
     */
    public static function eligibleCount($clientId)
    {
        return (int) DB::table('tbl_invoices')
            ->where('client_id', $clientId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<=', Carbon::today())
            ->count();
    }
}
