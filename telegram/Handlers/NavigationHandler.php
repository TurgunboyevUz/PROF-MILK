<?php

namespace Telegram\Handlers;

use App\Models\Category;
use App\Models\Product;
use App\Models\TgUser;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as Button;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup as Markup;

class NavigationHandler
{
    public $user;
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

    public function categories_key($page = 1)
    {
        $buttons = Markup::make();
        $categories = Category::offset(($page - 1) * 8)->limit(8)->get();

        if ($categories->isEmpty()) {
            return null;
        }

        foreach ($categories as $category) {
            $buttons->addRow(Button::make(text: $category->name(), callback_data: 'cg_' . $category->id));
        }

        $count = Category::count();

        if ($count > 8) {
            $row = [];

            if ($page > 1 and ceil($count / 8) != $page) {
                $row[] = Button::make(text: '⬅️', callback_data: 'cgs/' . ($page - 1));
            }

            $row[] = Button::make(text: $page . '/' . ceil($count / 8), callback_data: 'null');

            if ($page > 1 and ceil($count / 8) == $page) {
                $row[] = Button::make(text: '⬅️', callback_data: 'cgs/' . ($page - 1));
            }

            if($page < ceil($count / 8)) {
                $row[] = Button::make(text: '➡️', callback_data: 'cgs/' . ($page + 1));
            }

            $buttons->addRow(...$row);
        }

        $buttons->addRow(Button::make(__('button.back'), callback_data: 'home'));

        return $buttons;
    }

    public function products_key($category_id, $page = 1)
    {
        $buttons = Markup::make();
        $products = Product::where('category_id', $category_id)->offset(($page - 1) * 8)->limit(8)->get();

        if ($products->isEmpty()) {
            return null;
        }

        foreach ($products as $product) {
            $buttons->addRow(Button::make(text: $product->name(), callback_data: 'pd_' . $product->id));
        }

        $count = Product::where('category_id', $category_id)->count();
        if ($count > 8) {
            $row = [];

            if($page > 1 and ceil($count / 8) != $page) {
                $row[] = Button::make(text: '⬅️', callback_data: 'pds/' . $category_id . '/' . ($page - 1));
            }

            $row[] = Button::make(text: $page . '/' . ceil($count / 8), callback_data: 'null');

            if($page > 1 and ceil($count / 8) == $page) {
                $row[] = Button::make(text: '⬅️', callback_data: 'pds/' . $category_id . '/' . ($page - 1));
            }

            if($page < ceil($count / 8)) {
                $row[] = Button::make(text: '➡️', callback_data: 'pds/' . $category_id . '/' . ($page + 1));
            }

            $buttons->addRow(...$row);
        }

        $buttons->addRow(Button::make(__('button.back'), callback_data: 'products'));

        return $buttons;
    }

    public function product_key($product_id)
    {
        $buttons = Markup::make();
        $product = Product::find($product_id);

        $buttons->addRow(
            Button::make(
                text: '1',
                callback_data: 'plus_1'
            ),

            Button::make(
                text: '2',
                callback_data: 'plus_2'
            ),

            Button::make(
                text: '3',
                callback_data: 'plus_3'
            )
        );

        $buttons->addRow(
            Button::make(
                text: '4',
                callback_data: 'plus_4'
            ),

            Button::make(
                text: '5',
                callback_data: 'plus_5'
            ),

            Button::make(
                text: '6',
                callback_data: 'plus_6'
            )
        );

        $buttons->addRow(
            Button::make(
                text: '7',
                callback_data: 'plus_7'
            ),

            Button::make(
                text: '8',
                callback_data: 'plus_8'
            ),

            Button::make(
                text: '9',
                callback_data: 'plus_9'
            )
        );

        $buttons->addRow(
            Button::make(
                text: '0',
                callback_data: 'plus_0'
            ),

            Button::make(
                text: '⬅️',
                callback_data: 'backspace'
            )
        );

        $buttons->addRow(
            Button::make(
                text: __('button.add_to_cart'),
                callback_data: 'atc'
            )
        );

        $buttons->addRow(
            Button::make(
                text: __('button.back'),
                callback_data: 'cg_' . $product->category_id
            ),

            Button::make(
                text: __('button.shopping_cart'),
                callback_data: 'carts'
            ),

            Button::make(
                text: __('button.home'),
                callback_data: 'home'
            )
        );

        return $buttons;
    }




















    public function start()
    {
        $order_id = $this->bot->getUserData('order_id');

        if($order_id)
        {
            $this->user->orders()->where('id', $order_id)->update([
                'payment_status' => 1
            ]);
        }

        $this->bot->endConversation();
        
        $this->bot->sendMessage(
            text: __('message.welcome', [
                'name' => $this->bot->chat()->first_name
            ]),
            parse_mode: ParseMode::HTML,
            reply_markup: $this->main_key()
        );
    }

    public function home()
    {
        $this->bot->message()->delete();
        $this->start();
    }

    public function language()
    {
        $new = ($this->user->language == 'uz') ? 'ru' : 'uz';
        $this->user->update(['language' => $new]);

        app()->setLocale($new);

        $this->bot->editMessageText(
            text: __('message.welcome', [
                'name' => $this->bot->chat()->first_name
            ]),
            parse_mode: ParseMode::HTML,
            reply_markup: $this->main_key()
        );
    }













    public function categories()
    {
        $this->categories_page(1);
    }

    public function products($category_id)
    {
        $this->products_page($category_id, 1);
    }

    public function categories_page($page)
    {
        $buttons = $this->categories_key($page);
        if(is_null($buttons)) {
            return $this->bot->answerCallbackQuery(
                text: __('message.empty_categories'), //nothing not found
                show_alert: false
            );
        }


        $this->bot->editMessageText(
            text: __('message.categories', [
                'name' => $this->bot->chat()->first_name
            ]),
            parse_mode: ParseMode::HTML,
            reply_markup: $buttons
        );
    }

    public function products_page($category_id, $page)
    {
        $buttons = $this->products_key($category_id, $page);
        if(is_null($buttons)) {
            return $this->bot->answerCallbackQuery(
                text: __('message.empty_products'), //nothing not found
                show_alert: false
            );
        }


        $this->bot->message()->delete();
        $this->bot->sendMessage(
            text: __('message.select_product'),
            parse_mode: ParseMode::HTML,
            reply_markup: $this->products_key($category_id, $page)
        );
    }

    public function product($product_id)
    {
        $cart = $this->user->carts()->where('product_id', $product_id)->first();
        $quantity = (!$cart) ? 0 : $cart->quantity;

        $product = Product::find($product_id);

        $this->bot->setUserData('params', [
            'product_id' => $product_id,
            'quantity' => $quantity
        ]);

        $this->bot->message()->delete();
        $this->bot->sendPhoto(
            photo: $product->image(),
            caption: __('message.product_info', [
                'name' => $product->name(),
                'about' => $product->description(),
                'quantity' => $quantity,
                'price' => $this->formatPrice($product->price),
            ]),
            parse_mode: ParseMode::HTML,
            reply_markup: $this->product_key($product_id)
        );
    }

    public function plus($num)
    {
        extract($this->bot->getUserData('params'));

        $product = Product::find($product_id);
        if($quantity == 0){
            $quantity = $num;
        }else{
            $quantity .= $num;
        }

        $this->bot->setUserData('params', [
            'product_id' => $product_id,
            'quantity' => $quantity
        ]);

        $this->bot->editMessageCaption(
            caption: __('message.product_info', [
                'name' => $product->name(),
                'about' => $product->description(),
                'quantity' => $quantity,
                'price' => $this->formatPrice($product->price)
            ]),
            parse_mode: ParseMode::HTML,
            reply_markup: $this->product_key($product_id)
        );
    }

    public function backspace()
    {
        extract($this->bot->getUserData('params'));

        $product = Product::find($product_id);

        if($quantity != 0){
            $quantity = intval(substr($quantity, 0, -1));

            $this->bot->setUserData('params', [
                'product_id' => $product_id,
                'quantity' => $quantity
            ]);

            $this->bot->editMessageCaption(
                caption: __('message.product_info', [
                    'name' => $product->name(),
                    'about' => $product->description(),
                    'quantity' => $quantity,
                    'price' => $this->formatPrice($product->price)
                ]),
                parse_mode: ParseMode::HTML,
                reply_markup: $this->product_key($product_id)
            );
        }
    }
}