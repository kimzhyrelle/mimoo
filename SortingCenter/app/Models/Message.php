<?php

namespace App\Models;

use App\Models\Concerns\HasListFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory, HasListFilters;

    protected $fillable = [
        'conversation_id', 'sender', 'body', 'parcel_id', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function parcel(): BelongsTo
    {
        return $this->belongsTo(Parcel::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('sender', 'them')->whereNull('read_at');
    }

    public function markAsRead(): void
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * @return array<int, string>
     */
    protected function listSearchable(): array
    {
        return ['body'];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function listSearchRelations(): array
    {
        return [
            'conversation' => ['name'],
            'parcel' => ['tracking_no'],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    protected function listSortable(): array
    {
        return [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
        ];
    }

    protected function listStatusColumn(): string
    {
        return '';
    }

    protected function listDateColumn(): string
    {
        return 'created_at';
    }
}
