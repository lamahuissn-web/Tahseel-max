<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $delivery_token Durable owner of the active serialized queue attempt.
 */
class WhatsAppMessageLog extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_message_logs';

    protected $guarded = [];

    protected $casts = [
        'invoice_ids' => 'array',
        'template_variables' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the durable batch associated with this message.
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(WhatsAppBatch::class, 'batch_id');
    }

    /**
     * Get the client that owns this message log.
     */
    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    /**
     * Scope a query to only include sent messages.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope a query to only include failed messages.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include messages of a specific template type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('template_type', $type);
    }

    /**
     * Scope a query to search by client name or phone.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('client_name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }
}
