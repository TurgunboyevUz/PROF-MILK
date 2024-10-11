<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'products',
        'shipping_price',
        'total_price',
        'payment_method',
        'payment_status',
        'payment_state',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(TgUser::class);
    }

    public function products()
    {
        return json_decode($this->products);
    }

    public function transaction()
    {
        return $this->hasOne(PaymeTransaction::class);
    }
}
