<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientsRemainingExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Collection $rows;
    protected string $currency;

    public function __construct(Collection $rows, string $currency)
    {
        $this->rows = $rows;
        $this->currency = $currency;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            trans('clients.name'),
            trans('clients.phone'),
            trans('clients.client_type'),
            trans('clients.subscription'),
            trans('reports.total_invoices'),
            trans('reports.total_amount') . ' (' . $this->currency . ')',
            trans('reports.total_paid') . ' (' . $this->currency . ')',
            trans('reports.total_remaining') . ' (' . $this->currency . ')',
            trans('reports.last_due_date'),
            trans('clients.status'),
        ];
    }

    public function map($row): array
    {
        $clientTypeKey = $row->client_type ? 'clients.' . $row->client_type : null;
        $clientType = $clientTypeKey && trans()->has($clientTypeKey)
            ? trans($clientTypeKey)
            : ($row->client_type ?? '-');

        $status = $row->is_active == '1'
            ? trans('clients.active')
            : trans('clients.inactive');

        return [
            $row->name,
            $row->phone ?? '-',
            $clientType,
            $row->subscription_name ?? '-',
            $row->invoices_count ?? 0,
            number_format($row->total_amount ?? 0, 2),
            number_format($row->total_paid ?? 0, 2),
            number_format($row->total_remaining ?? 0, 2),
            $row->latest_due_date ? \Carbon\Carbon::parse($row->latest_due_date)->format('Y-m-d') : '-',
            $status,
        ];
    }
}

