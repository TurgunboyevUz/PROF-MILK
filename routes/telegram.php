<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use Illuminate\Support\Facades\Log;
use Telegram\Handlers\CartHandler;
use Telegram\Handlers\NavigationHandler;
use Telegram\Conversations\CheckoutConversation;

use SergiX44\Nutgram\Nutgram;
use Telegram\Conversations\BulkConversation;
use Telegram\Conversations\ContactConversation;
use Telegram\Handlers\AdminHandler;
use Telegram\Handlers\OrderHandler;

$bot->onCommand('start', [NavigationHandler::class, 'start']);
$bot->onCallbackQueryData('home', [NavigationHandler::class, 'home']);

$bot->onCallbackQueryData('products', [NavigationHandler::class, 'categories']);
$bot->onCallbackQueryData('carts', [CartHandler::class, 'carts']);
$bot->onCallbackQueryData('language', [NavigationHandler::class, 'language']);

$bot->onCallbackQueryData('cgs/{page}', [NavigationHandler::class, 'categories_page']); // Category Pages
$bot->onCallbackQueryData('pds/{cg_id}/{page}', [NavigationHandler::class, 'products_page']); // Product Pages

$bot->onCallbackQueryData('cg_{id}', [NavigationHandler::class, 'products']); //Choosing Category
$bot->onCallbackQueryData('pd_{id}', [NavigationHandler::class, 'product']); //Choosing Product

$bot->onCallbackQueryData('plus_{num}', [NavigationHandler::class, 'plus']); // Plus like Calculator
$bot->onCallbackQueryData('backspace', [NavigationHandler::class, 'backspace']); // Backspace

$bot->onCallbackQueryData('atc', [CartHandler::class, 'addtocart']); // Add to cart
$bot->onCallbackQueryData('cts/{page}', [CartHandler::class, 'carts_page']); // Carts Pages
$bot->onCallbackQueryData('rem/{id}', [CartHandler::class, 'remove']); // Remove Product from Cart

$bot->onCallbackQueryData('checkout', CheckoutConversation::class); // Checkout

$bot->onCommand('start {base64}', ContactConversation::class);
$bot->onCallbackQueryData('admin_confirm/{id}/{user_id}', [OrderHandler::class, 'admin_confirm']);
$bot->onCallbackQueryData('admin_cancel/{id}/{user_id}', [OrderHandler::class, 'admin_cancel']);

$bot->group(function (Nutgram $bot) {
    $bot->onCommand('dashboard', [AdminHandler::class, 'dashboard']);
    $bot->onCallbackQueryData('home_admin', [AdminHandler::class, 'home_admin']);

    $bot->onCallbackQueryData('update_bulk', [AdminHandler::class, 'update_bulk']);
    $bot->onCallbackQueryData('bulk', BulkConversation::class);
})->middleware(function (Nutgram $bot, $next) {
    $id = $bot->chatId();

    if(!in_array($id, config('nutgram.admin'))){
        return;
    }

    return $next($bot);
});