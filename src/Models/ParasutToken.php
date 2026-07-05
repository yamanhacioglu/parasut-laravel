<?php

namespace Northlab\Parasut\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $connection_key
 * @property string $access_token
 * @property string $refresh_token
 * @property string $token_type
 * @property \Illuminate\Support\Carbon $expires_at
 * @property int|null $company_id
 */
class ParasutToken extends Model
{
    protected $table = 'parasut_tokens';

    protected $fillable = [
        'connection_key',
        'access_token',
        'refresh_token',
        'token_type',
        'expires_at',
        'company_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'company_id' => 'integer',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at === null || now()->greaterThanOrEqualTo($this->expires_at);
    }
}
