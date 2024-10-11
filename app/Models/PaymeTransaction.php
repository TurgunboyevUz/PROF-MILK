<?php

namespace App\Models;

use App\Enums\PaymeState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymeTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction', 'code', 'state', 'owner_id', 'amount', 'reason', 'payme_time', 'cancel_time', 'create_time', 'perform_time'
    ];

    protected $casts = [
        'state' => PaymeState::class,
        'create_time' => 'integer',
        'perform_time' => 'integer',
        'cancel_time' => 'integer',
        'reason' => 'integer',
    ];

    public static function transaction($id)
    {
        return self::where('transaction', $id)->first();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
