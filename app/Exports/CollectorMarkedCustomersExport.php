<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class CollectorMarkedCustomersExport implements WithMultipleSheets
{
    public function __construct(private array $groups)
    {
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->groups as $group) {
            $sheets[] = new CollectorMarkedCustomersSheet($group);
        }

        if (empty($sheets)) {
            $sheets[] = new CollectorMarkedCustomersSheet([
                'name' => 'No Collectors',
                'markers' => [],
                'customers' => [],
            ]);
        }

        return $sheets;
    }
}

class CollectorMarkedCustomersSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    public function __construct(private array $group)
    {
    }

    public function collection(): Collection
    {
        return collect($this->group['customers'] ?? [])->values();
    }

    public function headings(): array
    {
        return [
            '#',
            'Customer ID',
            'Customer Name',
            'Due Amount',
            'Due Date',
            'Phone',
            'Address 1',
            'Address 2',
            'Matched Markers',
            'Outstanding Invoices',
            'Status',
            'Notes / Collector Tracking',
        ];
    }

    public function map($row): array
    {
        return [
            $row['row_number'] ?? '',
            $row['id'] ?? '',
            $row['name'] ?? '',
            number_format((float) ($row['total_amount'] ?? 0), 2),
            $row['first_due_date_formatted'] ?? '-',
            $row['phone'] ?: '-',
            $row['address1'] ?: '-',
            $row['address2'] ?: '-',
            implode(', ', $row['matched_markers'] ?? []),
            (int) ($row['invoice_count'] ?? 0),
            !empty($row['conflict']) ? 'CONFLICT: matches multiple collectors' : 'OK',
            '',
        ];
    }

    public function title(): string
    {
        $title = trim((string) ($this->group['name'] ?? 'Collector')) ?: 'Collector';
        $title = preg_replace('/[\\\\\/\?\*\[\]\:]/', '-', $title);
        return mb_substr($title, 0, 31) ?: 'Collector';
    }
}
