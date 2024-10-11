<?php

namespace Telegram\Conversations;

use App\Models\TgUser;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as Markup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as Button;

class ContactConversation extends Conversation
{
    public Nutgram $bot;

    public function __construct(Nutgram $bot)
    {
        $this->bot = $bot;
    }

    public function delete_key()
    {
        $buttons = Markup::make();
        $buttons->addRow(Button::make(
            text: '❌ Отмена',
            callback_data: 'delete'
        ));

        return $buttons;
    }

    public function start(Nutgram $bot, $base64)
    {
        $ex = explode('/', base64_decode($base64));
        $this->bot->setUserData('user_id', $ex[0]);
        $this->bot->setUserData('order_id', $ex[1]);

        $this->bot->sendMessage(
            text: "✍️ Введите сообщение, которое будет отправлено клиенту:",
            parse_mode: ParseMode::HTML,
            reply_markup: $this->delete_key()
        );

        $this->next('message');
    }

    public function message()
    {
        $user_id = $this->bot->getUserData('user_id');
        $order_id = $this->bot->getUserData('order_id');

        $user = TgUser::user($user_id);

        $this->bot->editMessageReplyMarkup(
            message_id: $this->bot->message()->message_id - 1,
        );

        $this->bot->sendMessage(
            chat_id: $user_id,
            text: __('message.order_contact', [
                'order_id' => $order_id,
            ], $user->language),

            parse_mode: ParseMode::HTML
        );

        $this->bot->copyMessage(
            chat_id: $user_id,
            from_chat_id: $this->bot->message()->chat->id,
            message_id: $this->bot->message()->message_id,
            reply_markup: $this->bot->message()->reply_markup
        );

        $this->bot->sendMessage(
            text: "✅ Ваше сообщение будет отправлено клиенту:",
            parse_mode: ParseMode::HTML
        );

        $this->bot->deleteUserData('user_id');
        $this->bot->deleteUserData('order_id');

        $this->end();
    }
}