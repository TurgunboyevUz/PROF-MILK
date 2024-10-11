<?php

namespace Telegram\Conversations;

use App\Models\Bulk;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as Markup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as Button;

class BulkConversation extends Conversation
{
    public function back_key()
    {
        return Markup::make()
            ->addRow(Button::make("Назад", callback_data: 'home_admin'));
    }

    public function type_key()
    {
        return Markup::make()
            ->addRow(Button::make("Простой", callback_data: 'copy_type'))
            ->addRow(Button::make("Forward", callback_data: 'forward_type'))
            ->addRow(Button::make("Назад", callback_data: 'home_admin'));
    }

    public function update_key()
    {
        return Markup::make()
            ->addRow(Button::make("Обновлять", callback_data: 'update_bulk'))
            ->addRow(Button::make("Назад", callback_data: 'home_admin'));
    }

    public function start(Nutgram $bot)
    {
        $bot->editMessageText(
            text: "Отправьте сообщение для отправки пользователям:",
            parse_mode: ParseMode::HTML,
            reply_markup: $this->back_key()
        );

        $this->next('choose_type');
    }

    public function choose_type(Nutgram $bot)
    {
        $bot->setUserData('chat_id', $bot->chatId());
        $bot->setUserData('message_id', $bot->messageId());
        $bot->setUserData('reply_markup', $bot->message()->reply_markup);

        $bot->sendMessage(
            text: "Как отправить сообщение пользователям, напоминаю, что после выбора типа отправки начнется процесс отправки сообщений пользователям!",
            parse_mode: ParseMode::HTML,
            reply_markup: $this->type_key()
        );

        $this->next('confirmation');
    }

    public function confirmation(Nutgram $bot)
    {
        if($bot->callbackQuery()->data == 'copy_type'){
            $type = 'copy';
        }

        if($bot->callbackQuery()->data == 'forward_type'){
            $type = 'forward';
        }

        $chat_id = $bot->getUserData('chat_id');
        $message_id = $bot->getUserData('message_id');
        $reply_markup = $bot->getUserData('reply_markup');

        Bulk::create([
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => serialize($reply_markup),
            'type' => $type
        ]);

        $bot->editMessageText(
            text: "Процесс отправки сообщения начался.",
            parse_mode: ParseMode::HTML,
            reply_markup: $this->update_key()
        );

        $this->end();
    }
}