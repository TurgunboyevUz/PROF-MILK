<?php

use App\Models\Bulk;
use App\Models\TgUser;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Nutgram\Laravel\Facades\Telegram;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('bulk', function () {
    $bulk = Bulk::current();

    if(!$bulk){
        return $this->info("Foydalanuvchilarga yuboriladigan xabar mavjud emas!");
    }else{
        $users = TgUser::limit(100)->offset($bulk->success + $bulk->failed)->get();

        if($users->isEmpty())
        {
            $bulk->update(['active'=>false]);

            Telegram::sendMessage(
                chat_id: $bulk->chat_id,
                text: "Процесс отправки сообщения завершен. Отправлено: {$bulk->succes}\nНе отправлено: {$bulk->failed}",
            );
        }

        $from_chat_id = $bulk->chat_id;
        $message_id = $bulk->message_id;
        $reply_markup = unserialize($bulk->reply_markup);

        foreach ($users as $user) {
            if($bulk->type == 'copy'){
                $send = Bulk::copyMessage($from_chat_id, $user->chat_id, $message_id, $reply_markup);
            }else{
                $send = Bulk::forwardMessage($from_chat_id, $user->chat_id, $message_id, $reply_markup);
            }

            if($send){
                $bulk->success++;
            }else{
                $bulk->failed++;
            }
        }

        $bulk->save();

        return $this->info("Yuborildi: " . $bulk->success . "\nYuborilmadi: " . $bulk->failed);
    }
});
