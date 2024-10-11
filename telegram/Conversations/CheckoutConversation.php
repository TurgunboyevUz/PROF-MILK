<?php

namespace Telegram\Conversations;

use App\Models\Order;
use App\Models\TgUser;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as Button;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as Markup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup as Keyboard;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove as Remove;

class CheckoutConversation extends Conversation
{
    public $user;
    public array $params = [];
    public Nutgram $bot;

    public function __construct(Nutgram $bot)
    {
        $this->user = TgUser::user($bot->chatId());
        $this->bot = $bot;

        $this->language();
    }

    public function language()
    {
        app()->setLocale($this->user->language);
    }

    protected function getSerializableAttributes(): array
    {
        return [
            'params' => $this->params,
        ];
    }

    public function formatPrice($price)
    {
        return number_format($price, 0, '.', ' ');
    }

    public function cart_list()
    {
        $str = '';

        $base_price = 0;

        foreach ($this->user->carts as $cart) {
            $product = $cart->product;

            $price = $product->price * $cart->quantity;
            $base_price += $price;

            $str .= $product->category->name() . " ~ " . $product->name() . PHP_EOL;
            $str .= $this->formatPrice($product->price) . " ✖️ " . $cart->quantity . " = " . $this->formatPrice($price) . PHP_EOL . PHP_EOL;
        }

        if ($base_price < config('nutgram.min_price')) {
            $delivery_price = config('nutgram.delivery_price');
        } else {
            $delivery_price = 0;
        }

        return [
            'list' => $str,
            'base' => $base_price,
            'total' => $base_price + $delivery_price,
            'delivery' => $delivery_price,
        ];
    }

    public function main_key()
    {
        $buttons = Markup::make();

        $buttons->addRow(Button::make(text: __('button.products'), callback_data: 'products'));
        $buttons->addRow(Button::make(text: __('button.shopping_cart') . ' (' . $this->user->carts()->count() . ')', callback_data: 'carts'));
        $buttons->addRow(Button::make(text: __('button.change_language'), callback_data: 'language'));

        return $buttons;
    }

    public function back_key()
    {
        $buttons = Markup::make();
        $buttons->addRow(Button::make(text: __('button.back'), callback_data: 'home'));

        return $buttons;
    }

    public function phone_key()
    {
        $buttons = Keyboard::make(true);
        $buttons->addRow(KeyboardButton::make(__('button.send_phone'), request_contact: true));
        $buttons->addRow(KeyboardButton::make(__('button.back')));

        return $buttons;
    }

    public function location_key()
    {
        $buttons = Keyboard::make(true);
        $buttons->addRow(KeyboardButton::make(__('button.send_location'), request_location: true));
        $buttons->addRow(KeyboardButton::make(__('button.back')));

        return $buttons;
    }

    public function skip_key()
    {
        $buttons = Markup::make();
        $buttons->addRow(Button::make(text: __('button.skip'), callback_data: 'skip'));
        $buttons->addRow(Button::make(text: __('button.back'), callback_data: 'home'));

        return $buttons;
    }

    public function confirm_key()
    {
        $buttons = Markup::make();
        $buttons->addRow(Button::make(text: __('button.confirm'), callback_data: 'confirm'));
        $buttons->addRow(Button::make(text: __('button.cancel'), callback_data: 'home'));

        return $buttons;
    }

    public function payment_key($url)
    {
        $buttons = Markup::make();

        $buttons->addRow(Button::make(__('button.payme'), url: $url));
        $buttons->addRow(Button::make(__('button.cash'), callback_data: 'cash'));
        $buttons->addRow(Button::make(__('button.back'), callback_data: 'home'));

        return $buttons;
    }

    public function admin_confirm_key($chat_id, $order_id)
    {
        $buttons = Markup::make();

        $buttons->addRow(Button::make(
            text: __('button.confirm'),
            callback_data: 'admin_confirm/' . $order_id . '/' . $chat_id
        ));

        $buttons->addRow(Button::make(
            text: __('button.cancel'),
            callback_data: 'admin_cancel/' . $order_id . '/' . $chat_id
        ));

        return $buttons;
    }

    public function back()
    {
        $this->language();

        if ($this->bot->message()->text == __('button.back')) {
            $this->bot->sendMessage(
                text: '.',
                reply_markup: Remove::make(true)
            )->delete();

            $this->bot->sendMessage(
                text: __('message.welcome', [
                    'name' => $this->bot->chat()->first_name,
                ]),

                parse_mode: ParseMode::HTML,
                reply_markup: $this->main_key()
            );

            return true;
        } else {
            return false;
        }
    }

    public function start()
    {
        $this->bot->editMessageReplyMarkup();
        $this->bot->sendMessage(
            text: __('message.enter_name'),
            parse_mode: ParseMode::HTML,
            reply_markup: $this->back_key()
        );

        $this->next('name');
    }

    public function name()
    {
        if ($this->back()) {return;}

        $this->params['name'] = $this->bot->message()->text;

        $this->bot->sendMessage(
            text: __('message.enter_phone'),
            parse_mode: ParseMode::HTML,
            reply_markup: $this->phone_key()
        );

        $this->next('phone');
    }

    public function phone()
    {
        if ($this->back()) {return;}

        $phone = $this->bot->message()->text ?? $this->bot->message()->contact->phone_number;
        $phone = str_replace('+', '', $phone);

        if (is_numeric($phone) and strlen($phone) > 8) {
            $this->params['phone'] = $phone;

            $this->bot->sendMessage(
                text: __('message.enter_location'),
                parse_mode: ParseMode::HTML,
                reply_markup: $this->location_key()
            );

            $this->next('location');
        }else{
            $this->bot->sendMessage(
                text: __('message.error_invalid_number')
            );
        }
    }

    public function location()
    {
        if ($this->back()) {return;}

        $this->bot->sendMessage('.', reply_markup: Remove::make(true))->delete();

        if (isset($this->bot->message()->location)) {
            $this->params['latitude'] = $this->bot->message()->location->latitude;
            $this->params['longitude'] = $this->bot->message()->location->longitude;

            $this->bot->sendMessage(
                text: __('message.enter_city'),
                parse_mode: ParseMode::HTML,
                reply_markup: $this->back_key()
            );

            $this->next('city');
        } else {
            if (isset($this->bot->message()->text)) {
                $this->params['city'] = $this->bot->message()->text;

                $this->bot->sendMessage(
                    text: __('message.enter_comment'),
                    parse_mode: ParseMode::HTML,
                    reply_markup: $this->skip_key()
                );

                $this->next('comment');
            }
        }
    }

    public function city()
    {
        if ($this->back()) {return;}

        $this->params['city'] = $this->bot->message()->text;

        $this->bot->sendMessage(
            text: __('message.enter_comment'),
            parse_mode: ParseMode::HTML,
            reply_markup: $this->skip_key()
        );

        $this->next('comment');
    }

    public function comment()
    {
        if ($this->back()) {return;}

        $list = $this->cart_list();
        $this->params['list'] = $list;

        $cart = $list['list'];
        $base = $list['base'];
        $delivery = $list['delivery'];
        $total = $list['total'];

        if ($this->bot->callbackQuery()?->data == 'skip') {
            $this->params['comment'] = null;

            $this->bot->editMessageText(
                text: __('message.order_confirm', [
                    'name_field' => $this->params['name'],
                    'number_field' => $this->params['phone'],
                    'address' => $this->params['city'],
                    'comment_field' => '',

                    'cart_list' => $cart,
                    'cart_price' => $this->formatPrice($base),
                    'delivery_price' => $this->formatPrice($delivery),
                    'total_price' => $this->formatPrice($total),
                ]),
                parse_mode: ParseMode::HTML,
                reply_markup: $this->confirm_key()
            );
        } else {
            $this->params['comment'] = $this->bot->message()->text . PHP_EOL . PHP_EOL;

            $this->bot->sendMessage(
                text: __('message.order_confirm', [
                    'name_field' => $this->params['name'],
                    'number_field' => $this->params['phone'],
                    'address' => $this->params['city'],
                    'comment_field' => $this->params['comment'],

                    'cart_list' => $cart,
                    'cart_price' => $this->formatPrice($base),
                    'delivery_price' => $this->formatPrice($delivery),
                    'total_price' => $this->formatPrice($total),
                ]),
                parse_mode: ParseMode::HTML,
                reply_markup: $this->confirm_key()
            );
        }

        $this->next('confirmation');
    }

    public function confirmation()
    {
        if ($this->back()) {return;}

        if ($this->bot->callbackQuery()->data != 'confirm') {
            return $this->end();
        }

        $list = $this->params['list'];

        $order = new Order;
        $order->user_id = $this->user->id;
        $order->products = json_encode($this->user->carts->makeHidden('product')->toArray());
        $order->shipping_price = $list['delivery'];
        $order->total_price = $list['total'];
        $order->save();

        $payme = 'https://checkout.paycom.uz/' . base64_encode(http_build_query([
            'm' => config('payme.merchant_id'),
            'ac.' . config('payme.identity') => $order->id,
            'a' => $list['total'] * 100,
        ], 0, ';'));

        $this->bot->setUserData('order_id', $order->id);

        $this->bot->editMessageText(
            text: $this->bot->message()->text . __('message.order_after_confirm', [
                'order_id' => $order->id,
            ]),

            parse_mode: ParseMode::HTML
        );

        $message = $this->bot->sendMessage(
            text: __('message.payment_order', [
                'order_id' => $order->id,
            ]),

            parse_mode: ParseMode::HTML,
            reply_markup: $this->payment_key($payme) //Keyboardni qayta yozish kerak
        );

        $this->bot->setUserData('params', array_merge($this->params, [
            'message_id' => $message->message_id
        ]));
        $this->next('payment');
    }

    public function payment()
    {
        if ($this->back()) {return;}

        $this->user->orders()->where('id', $this->bot->getUserData('order_id'))->update([
            'payment_method' => 'cash',
            'payment_status' => 1
        ]);

        //---------cash------------
        $username = $this->bot->chat()->username;
        $username = $username ? '@' . $username : 'Не существует';
        
        if(isset($this->params['latitude'])){
            $this->bot->sendLocation($this->params['latitude'], $this->params['longitude'], config('nutgram.orders_chat'));
        }

        $this->bot->sendMessage(
            chat_id: config('nutgram.orders_chat'),
            text: __('message.order_info', [
                'order_id' => $this->bot->getUserData('order_id'),
                'name' => $this->params['name'],
                'username' => $username,
                'number_field' => $this->params['phone'],
                'address' => $this->params['city'],
                'type' => 'Наличными',
                'comment' => $this->params['comment'],
                'cart_list' => $this->params['list']['list'],
                'delivery_price' => $this->params['list']['delivery'],
                'total_price' => $this->params['list']['total'],
            ]),

            parse_mode: ParseMode::HTML,
            reply_markup: $this->admin_confirm_key($this->bot->chatId(), $this->bot->getUserData('order_id'))
        );

        $this->bot->editMessageText(
            text: __('message.payment_with_cash', [
                'order_id' => $this->bot->getUserData('order_id'),
            ])
        );

        $this->user->carts()->delete();
        $this->bot->deleteUserData('order_id');

        $this->bot->sendMessage(
            text: __('message.welcome', [
                'name' => $this->bot->chat()->first_name,
            ]),

            parse_mode: ParseMode::HTML,
            reply_markup: $this->main_key()
        );

        $this->end();
    }
}