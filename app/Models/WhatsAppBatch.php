<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppBatch extends Model
{
    protected $table = 'whatsapp_batches';

    protected $guarded = [];

    protected $casts = [
        'cancelled_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessageLog::class, 'batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
