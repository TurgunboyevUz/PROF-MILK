<?php

namespace Telegram\Handlers;

use App\Models\Category;
use App\Models\TgUser;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as Button;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as Markup;

class CartHandler
{
    public $user;
    public $cart;
    public Nutgram $bot;

    public function __construct(Nutgram $bot)
    {
        $this->user = TgUser::user($bot->chatId());
        $this->bot = $bot;

        app()->setLocale($this->user->language);
    }

    public function formatPrice($price)
    {
        return number_format($price, 0, '.', ' ');
    }

    public function main_key()
    {
        $buttons = Markup::make();

        $buttons->addRow(Button::make(text: __('button.products'), callback_data: 'products'));
        $buttons->addRow(Button::make(text: __('button.shopping_cart') . ' (' . $this->user->carts()->count() . ')', callback_data: 'carts'));
        $buttons->addRow(Button::make(text: __('button.change_language'), callback_data: 'language'));

        return $buttons;
    }

    public function addtocart_key()
    {
        $buttons = Markup::make();
        $categories = Category::limit(8)->get();

        foreach ($categories as $category) {
            $buttons->addRow(Button::make(text: $category->name(), callback_data: 'cg_' . $category->id));
        }

        $buttons->addRow(Button::make(text: __('button.checkout'), callback_data: 'carts'));

        $count = Category::count();
        if ($count > 8) {
            $row = [];
            $row[] = Button::make(text: '1/' . ceil($count / 8), callback_data: 'null');
            $row[] = Button::make(text: '➡️', callback_data: 'cgs/2');

            $buttons->addRow(...$row);
        }

        $buttons->addRow(Button::make(text: __('button.home'), callback_data: 'home'));

        return $buttons;
    }

    public function carts_key($page)
    {
        $buttons = Markup::make();
        $carts = $this->user->carts()->offset(($page - 1) * 8)->limit(8)->get();

        if ($carts->isEmpty()) {
            return null;
        }

        foreach ($carts as $cart) {
            $buttons->addRow(
                Button::make(text: $cart->product->name(), callback_data: 'pd_' . $cart->product_id),
                Button::make(text: '❌', callback_data: 'rem/' . $cart->id)
            );
        }

        $count = $this->user->carts()->count();
        if ($count > 8) {
            $row = [];
            if ($page > 1 and ceil($count / 8) != $page) {
                $row[] = Button::make(text: '⬅️', callback_data: 'cts/' . ($page - 1));
            }

            $row[] = Button::make(text: $page . '/' . ceil($count / 8), callback_data: 'null');

            if ($page > 1 and ceil($count / 8) == $page) {
                $row[] = Button::make(text: '⬅️', callback_data: 'cts/' . ($page - 1));
            }

            if ($page < ceil($count / 8)) {
                $row[] = Button::make(text: '➡️', callback_data: 'cts/' . ($page + 1));
            }

            $buttons->addRow(...$row);
        }

        $buttons->addRow(
            Button::make(
                text: __('button.back'),
                callback_data: 'home'
            ),

            Button::make(
                text: __('button.clear_cart'),
                callback_data: 'rem/all'
            )
        );

        $buttons->addRow(
            Button::make(
                text: __('button.place_order'),
                callback_data: 'checkout'
            )
        );

        return $buttons;
    }

    public function cart_list()
    {
        $str = '';

        $base_price = 0;

        foreach ($this->user->carts as $cart) {
            $product = $cart->product;

            $price = $product->price * $cart->quantity;
            $base_price += $price;

            $str .= $product->category->name()." ~ " . $product->name() . PHP_EOL;
            $str .= $this->formatPrice($product->price) . " ✖️ " . $cart->quantity . " = " . $this->formatPrice($price) . PHP_EOL . PHP_EOL;
        }

        if($base_price < config('nutgram.min_price'))
        {
            $delivery_price = config('nutgram.delivery_price');
        }else{
            $delivery_price = 0;
        }

        return [
            'list' => $str,
            'total' => $base_price + $delivery_price,
            'delivery' => $delivery_price
        ];
    }























    public function addtocart()
    {
        extract($this->bot->getUserData('params'));
        $this->bot->deleteUserData('params');

        if ($quantity == 0) {
            return $this->bot->answerCallbackQuery(
                text: __('message.error_unable_add'), //eng kam miqdor 1 ga teng
                show_alert: true
            );
        }

        if ($cart = $this->user->carts()->where('product_id', $product_id)->first()) {
            $cart->update(['quantity' => $quantity]);
        } else {
            $this->user->carts()->create([
                'product_id' => $product_id,
                'quantity' => $quantity,
            ]);
        }

        $this->bot->editMessageReplyMarkup();
        $this->bot->sendMessage(
            text: __('message.cart_added'),
            parse_mode: ParseMode::HTML,
            reply_markup: $this->addtocart_key()
        );
    }

    public function carts_page($page)
    {
        $buttons = $this->carts_key($page);

        if (is_null($buttons)) {
            return $this->bot->answerCallbackQuery(
                text: __('message.cart_empty'), //nothing not found
                show_alert: false
            );
        }

        $list = $this->cart_list();
        
        $cart = $list['list'];
        $total = $list['total'];
        $delivery = $list['delivery'];

        $this->bot->message()->delete();

        $this->bot->sendMessage(
            text: __('message.cart_list', [
                'cart_list' => $cart,
                'delivery_price' => $this->formatPrice($delivery),
                'total_price' => $this->formatPrice($total),
            ]),
            parse_mode: ParseMode::HTML,
            reply_markup: $buttons
        );
    }

    public function carts()
    {
        $this->carts_page(1);
    }













    public function remove($cart_id)
    {
        if ($cart_id == 'all') {
            $this->user->carts()->delete();

            return $this->bot->editMessageText(
                text: __('message.cart_cleaned'),
                parse_mode: ParseMode::HTML,
                reply_markup: $this->main_key()
            );
        }

        if ($this->user->carts()->count() == 1) {
            $this->remove('all');
        } else {
            $this->user->carts()->where('id', $cart_id)->delete();
            $this->carts_page(1);
        }
    }
}
