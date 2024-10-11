<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Nutgram\Laravel\Facades\Telegram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use Throwable;

class Bulk extends Model
{
    use HasFactory;

    protected $fillable = [
        'success',
        'failed',
        'chat_id',
        'message_id',
        'reply_markup',
        'type',
        'active'
    ];

    public static function current()
    {
        return self::where('active', 1)->first();
    }

    public static function copyMessage($from_chat_id, $chat_id, $message_id, $reply_markup)
    {
        try{
            Telegram::copyMessage($chat_id, $from_chat_id, $message_id, reply_markup: $reply_markup);

            return true;
        }catch(Throwable $e){
            Log::error($e->getMessage());

            return false;
        }
    }

    public static function forwardMessage($from_chat_id, $chat_id, $message_id, $reply_markup)
    {
        try{
            Telegram::forwardMessage($chat_id, $from_chat_id, $message_id);

            return true;
        }catch(Throwable $e){
            Log::error($e->getMessage());
            
            return false;
        }
    }
}
