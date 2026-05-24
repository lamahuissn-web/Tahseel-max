<?php

namespace App\Models\Admin;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyInvoiceGeneration extends Model
{
    use HasFactory;

    protected $table = 'tbl_monthly_invoice_generations';
    protected $guarded = [];

    public function generator()
    {
        return $this->belongsTo(Admin::class, 'generated_by');
    }
}
