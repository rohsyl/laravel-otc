<?php

namespace rohsyl\LaravelOtc\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use rohsyl\LaravelOtc\Database\Factories\OtcTokenFactory;

class OtcToken extends Model
{
    use HasFactory;

    public $fillable = [
        'related_id',
        'related_type',
        'code',
        'code_valid_until',
        'token',
        'token_valid_until',
        'ip',
    ];

    protected $casts = [
        'code_valid_until' => 'datetime',
        'token_valid_until' => 'datetime',
    ];

    public function related() {
        return $this->morphTo('related');
    }

    protected static function newFactory()
    {
        return OtcTokenFactory::new();
    }
}
