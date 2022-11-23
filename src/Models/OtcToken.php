<?php

namespace rohsyl\LaravelOtc\Models;

use Illuminate\Database\Eloquent\Model;

class OtcToken extends Model
{

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
}
