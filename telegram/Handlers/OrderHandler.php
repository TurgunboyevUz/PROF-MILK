<?php

namespace Telegram\Handlers;

use App\Models\TgUser;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as Markup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as Button;

class OrderHandler
{
    public Nutgram $bot;

    public function __construct(Nutgram $bot)
    {
        $this->bot = $bot;
    }

    public function contact_user($user_id, $order_id)
    {
        $buttons = Markup::make();
        $buttons->addRow(Button::make(
            text: "✏️ Связаться с клиентом",
            url: 'https://t.me/' . config('nutgram.username') . '?start=' . base64_encode($user_id . '/' . $order_id)
        ));

        return $buttons;
    }

    public function admin_confirm($id, $user_id)
    {
        $user = TgUser::user($user_id);
        
        $order = $user->orders()->where('id', $id)->first();
        $order->status = 'processing';
        $order->save();

        app()->setLocale($user->language);
        
        $this->bot->sendMessage(
            chat_id: $user_id,
            text: __('message.order_confirmed', ['order_id' => $order->id]),
            parse_mode: ParseMode::HTML
        );

        $this->bot->editMessageText(
            text: $this->bot->message()->text . PHP_EOL . PHP_EOL . "Статус: ✅",
            parse_mode: ParseMode::HTML,
            reply_markup: $this->contact_user($user_id, $id)
        );
    }

    public function admin_cancel($id, $user_id)
    {
        $user = TgUser::user($user_id);
        
        $order = $user->orders()->where('id', $id)->first();
        $order->status = 'cancelled';
        $order->save();

        app()->setLocale($user->language);
        
        $this->bot->sendMessage(
            chat_id: $user_id,
            text: __('message.order_cancelled', ['order_id' => $order->id]),
            parse_mode: ParseMode::HTML
        );

        $this->bot->editMessageText(
            text: $this->bot->message()->text . PHP_EOL . PHP_EOL . "Статус: ❌",
            parse_mode: ParseMode::HTML,
            reply_markup: $this->contact_user($user_id, $id)
        );
    }
}