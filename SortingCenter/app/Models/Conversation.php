<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasListFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use BelongsToCompany, HasFactory, HasListFilters;

    public const TYPES = ['rider', 'seller', 'admin'];

    protected $fillable = [
        'company_id', 'type', 'name', 'initials', 'online', 'parcel_id', 'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'online' => 'boolean',
            'last_message_at' => 'datetime',
        ];
    }

    public function parcel(): BelongsTo
    {
        return $this->belongsTo(Parcel::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function unreadCount(): int
    {
        return $this->messages()
            ->where('sender', 'them')
            ->whereNull('read_at')
            ->count();
    }

    public function reference(): string
    {
        return $this->parcel ? 'RE: '.$this->parcel->tracking_no : 'GENERAL';
    }

    /**
     * @return array<int, string>
     */
    protected function listSearchable(): array
    {
        return ['name', 'initials'];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function listSearchRelations(): array
    {
        return [
            'messages' => ['body'],
            'parcel' => ['tracking_no', 'buyer_name'],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    protected function listSortable(): array
    {
        return [
            'recent' => ['last_message_at', 'desc'],
            'oldest' => ['last_message_at', 'asc'],
            'name' => ['name', 'asc'],
        ];
    }

    protected function listStatusColumn(): string
    {
        return 'type';
    }

    protected function listRiderRelation(): string
    {
        return 'parcel';
    }

    protected function listAreaRelation(): string
    {
        return 'parcel';
    }

    protected function listDateColumn(): string
    {
        return 'last_message_at';
    }
}
