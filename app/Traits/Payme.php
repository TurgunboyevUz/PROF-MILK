<?php

namespace App\Traits;

use App\Exceptions\PaymeException;
use App\Models\Order;
use App\Models\Product;
use Nutgram\Laravel\Facades\Telegram;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

trait Payme
{
    public function hasParam($param)
    {
        if (is_array($param)) {
            foreach ($param as $item) {
                if (!$this->hasParam($item)) {
                    throw new PaymeException(PaymeException::JSON_RPC_ERROR);
                }
            }

            return true;
        } else {
            if (isset($this->params[$param]) && !empty($this->params[$param])) {
                return true;
            } else {
                throw new PaymeException(PaymeException::JSON_RPC_ERROR);
            }
        }
    }

    public function hasAccount($account)
    {
        if (!array_key_exists($this->account, $account)) {
            throw new PaymeException(PaymeException::USER_NOT_FOUND);
        }

        return true;
    }

    public function isValidAmount($amount): bool
    {
        if ($amount < $this->minAmount || $amount > $this->maxAmount) {
            throw new PaymeException(PaymeException::WRONG_AMOUNT);
        } else {
            return true;
        }
    }

    public function performOrder($transaction)
    {
        $order = Order::find($transaction->owner_id);

        $order->update([
            'payment_status' => true,
            'payment_method' => 'payme'
        ]);

        $user = $order->user;

        app()->setLocale($user->language);

        $chat = Telegram::getChat($user->chat_id);
        $username = $chat->username ? '@' . $chat->username : 'Не существует';

        $params = Telegram::getUserData('params', $user->chat_id);
        
        if(isset($params['latitude'])){
            Telegram::sendLocation($params['latitude'], $params['longitude'], config('nutgram.orders_chat'));
        }

        Telegram::sendMessage(
            chat_id: config('nutgram.orders_chat'),
            text: __('message.order_info', [
                'order_id' => $order->id,
                'name' => $params['name'],
                'username' => $username,
                'number_field' => $params['phone'],
                'address' => $params['city'],
                'type' => 'Наличными',
                'comment' => $params['comment'],
                'cart_list' => $params['list']['list'],
                'delivery_price' => $params['list']['delivery'],
                'total_price' => $params['list']['total'],
            ]),

            parse_mode: ParseMode::HTML,
            reply_markup: InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: __('button.confirm'),
                    callback_data: 'admin_confirm/' . $order->id . '/' . $user->chat_id
                )
            )->addRow(
                InlineKeyboardButton::make(
                    text: __('button.cancel'),
                    callback_data: 'admin_cancel/' . $order->id . '/' . $user->chat_id
                )
            )
        );

        Telegram::editMessageText(
            chat_id: $user->chat_id,
            message_id: $params['message_id'],
            text: __('message.payment_with_payme', [
                'order_id' => $order->id
            ]),

            parse_mode: ParseMode::HTML
        );

        Telegram::sendMessage(
            chat_id: $user->chat_id,
            text: __('message.welcome', [
                'name' => $chat->first_name
            ]),

            parse_mode: ParseMode::HTML,
            reply_markup: InlineKeyboardMarkup::make()
            ->addRow(InlineKeyboardButton::make(text: __('button.products'), callback_data: 'products')
            )->addRow(InlineKeyboardButton::make(text: __('button.shopping_cart') . ' (' . $user->carts()->count() . ')', callback_data: 'carts')
            )->addRow(InlineKeyboardButton::make(text: __('button.change_language'), callback_data: 'language'))
        );
        
        $user->carts()->delete();
        Telegram::deleteUserData('params', $user->chat_id);
        Telegram::deleteUserData('order_id', $user->chat_id);

        return true;
    }

    protected function microtime(): int
    {
        return (time() * 1000);
    }

    private function checkTimeout($created_time): bool
    {
        return $this->microtime() <= ($created_time + $this->timeout);
    }

    public function successCreateTransaction($createTime, $transaction, $state)
    {
        return $this->success([
            'create_time' => $createTime,
            'perform_time' => 0,
            'cancel_time' => 0,
            'transaction' => strval($transaction),
            'state' => $state,
            'reason' => null,
        ]);
    }

    public function successCheckPerformTransaction(Order $order)
    {
        $products = $order->products();
        $details = [];

        foreach ($products as $product) {
            $quantity = $product->quantity;
            $product = Product::find($product->product_id);

            $details[] = [
                'title' => $product->name_ru,
                'price' => $product->price * 100,
                'count' => (int) $quantity,
                'code' => $product->code,
                'vat_percent' => (int) $product->vat_percent,
                'package_code' => $product->package_code,
            ];
        }

        if ($order->shipping_price > 0) {
            $details[] = [
                'title' => 'Доставка',
                'price' => $order->shipping_price * 100,
                'count' => 1,
                'code' => '10112006002000000',
                'vat_percent' => 0,
                'package_code' => '1209779',
            ];
        }

        return $this->success([
            'allow' => true,
            'detail' => [
                'items' => $details,
                'receipt_type' => 0
            ]
        ]);
    }

    public function successPerformTransaction($state, $performTime, $transaction)
    {
        return $this->success([
            "state" => $state,
            "perform_time" => $performTime,
            "transaction" => strval($transaction),
        ]);
    }

    public function successCheckTransaction($createTime, $performTime, $cancelTime, $transaction, $state, $reason)
    {
        return $this->success([
            "create_time" => $createTime ?? 0,
            "perform_time" => $performTime ?? 0,
            "cancel_time" => $cancelTime ?? 0,
            "transaction" => strval($transaction),
            "state" => $state,
            "reason" => $reason,
        ]);
    }

    public function successCancelTransaction($state, $cancelTime, $transaction)
    {
        return $this->success([
            "state" => $state,
            "cancel_time" => $cancelTime,
            "transaction" => strval($transaction),
        ]);
    }

    public function successGetStatement($statement)
    {
        return $this->success([
            'transactions' => $statement
        ]);
    }
}
