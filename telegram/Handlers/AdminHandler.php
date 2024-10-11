<?php

namespace Telegram\Handlers;

use App\Models\Bulk;
use App\Models\TgUser;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as Button;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as Markup;

use function Termwind\parse;

class AdminHandler
{
    public Nutgram $bot;

    public function __construct(Nutgram $bot)
    {
        $this->bot = $bot;
    }

    public function bulk_key(){
        return Markup::make()
            ->addRow(Button::make("Рассылка", callback_data: 'bulk'));
    }

    public function update_key()
    {
        return Markup::make()
            ->addRow(Button::make("Обновлять", callback_data: 'update_bulk'))
            ->addRow(Button::make("Назад", callback_data: 'home_admin'));
    }

    public function dashboard()
    {
        $users = TgUser::count();

        $this->bot->sendMessage(
            text: "Добро пожаловать в админ панель!\n\nКоличество пользователей: " . $users,
            parse_mode: ParseMode::HTML,
            reply_markup: $this->bulk_key()
        );
    }

    public function home_admin()
    {
        $users = TgUser::count();

        $this->bot->editMessageText(
            text: "Добро пожаловать в админ панель!\n\nКоличество пользователей:" . $users,
            parse_mode: ParseMode::HTML,
            reply_markup: $this->bulk_key()
        );

        $this->bot->deleteUserData('chat_id');
        $this->bot->deleteUserData('message_id');
        $this->bot->deleteUserData('reply_markup');

        $this->bot->endConversation();
    }

    public function update_bulk()
    {
        $bulk = Bulk::current();

        if(!$bulk){
            $this->bot->answerCallbackQuery(
                text: "Это сообщение уже отправлено!",
                show_alert: true
            );

            sleep(2);

            $this->bot->message()->delete();
        }else{
            $this->bot->editMessageText(
                text: "Отправлено: " . $bulk->success . "\Не отправлено: " . $bulk->failed,
                parse_mode: ParseMode::HTML,
                reply_markup: $this->update_key()
            );
        }
    }
}