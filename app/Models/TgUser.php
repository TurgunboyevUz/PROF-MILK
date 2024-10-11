<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TgUser extends Model
{
    use HasFactory;

    protected $fillable = ['chat_id', 'language'];
    public $timestamps = false;

    public static function user($chat_id)
    {
        $user = self::firstOrCreate(['chat_id' => $chat_id]);

        if($user->language == null) {
            $user->language = 'uz';
        }

        return $user;
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }
}
