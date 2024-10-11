<?php

return [
    'welcome' => "👋Assalomu alaykum, :name!" . PHP_EOL . PHP_EOL . "👀Nima <b>buyurtma</b> qilamiz?",
    'categories' => ":name, nima buyurtma bermoqchisiz👇",
    'select_product' => "Mahsulotni tanlang va miqdorini belgilang👇",

    'product_info' => "<b>:name</b>" . PHP_EOL . PHP_EOL . "<i>:about</i>" . PHP_EOL . PHP_EOL . "<i>:price UZS</i>" . PHP_EOL . "<b>Miqdor: :quantity</b>",

    'empty_categories' => "🤷‍♂️ Biz o'z mahsulotlarimizni hali e'lon qilmadik, biroz kuting tez orada bu yerda bo'limlar paydo bo'ladi.",
    'empty_products' => "🤷‍♂️ Biz o'z mahsulotlarimizni hali e'lon qilmadik, biroz kuting tez orada bu yerda mahsulotlar paydo bo'ladi.",

    'enter_name' => "👤 Ismingizni kiriting:",
    'enter_city' => "🏙 Qaysi shaharda yashaysiz?",
    'enter_phone' => '📞 Siz bilan bog\'lana olishimiz uchun telefon raqamingizni kiriting:',
    'enter_location' => '📍 Buyurtmani kelib tushish manzilini kiriting yoki quyidagi tugma orqali lokatsiyangizni yuboring:',
    'enter_comment' => '✏️ Izoh yozishni xohlaysizmi, quyida yozib qoldiring yoki quyidagi tugma orqali buyurtmani tasdiqlashga o\'ting:',
    'order_confirm' => "✅ Buyurtma tayyor" . PHP_EOL . "👤 Ism: :name_field" . PHP_EOL . "📞 Raqam: :number_field" . PHP_EOL . "🏙 Manzil: :address" . PHP_EOL . PHP_EOL . ":comment_field:cart_list🥛 Mahsulotlar: :cart_price UZS" . PHP_EOL . "🚚 Yetkazish: :delivery_price UZS" . PHP_EOL . "💰 Jami: :total_price UZS",

    'cart_added' => '👌 Mahsulotingiz savatchaga qo\'shildi, tanlashni davom ettiramizmi?',
    'cart_empty' => '🤷‍♂️ Sizning savatingiz bo\'sh',
    'cart_cleaned' => '🛒 Savatchangiz tozalandi, yangi mahsulotlar tanlaymizmi?',
    'cart_list' => "🛒Buyurtmalaringiz:" . PHP_EOL . ":cart_list🚚 Yetkazish: :delivery_price UZS" . PHP_EOL . "💰 Jami: :total_price UZS",

    'payment_order' => "🔹 Buyurtma #:order_id" . PHP_EOL . "To'lovni Payme to'lov tizimi orqali yoki buyurtma yetkazilgach naqd pul ko'rinishida amalga oshiring:",
    'payment_with_payme' => "🔹 Buyurtma #:order_id" . PHP_EOL . "✅ To'lov muvaffaqiyatli amalga oshirildi, buyurtma haqidagi xabaringiz administratorlarga yuborildi!",
    'payment_with_cash' => "🔹 Buyurtma #:order_id" . PHP_EOL . "✅ Buyurtma  haqidagi xabaringiz administratorlarga yuborildi!",

    'order_after_confirm' => PHP_EOL . PHP_EOL . "🆕 Buyurtma Raqam: :order_id" . PHP_EOL . "🟡 Status: Qayta ishlovda",
    'order_confirmed' => "✅ Sizning :order_id raqamli buyurtmangiz tasdiqlandi, tez orada uni yetkazamiz!" . PHP_EOL . PHP_EOL . "<b>Ishonchingiz va tabiiy mahsulotlarimizni tanlaganingiz uchun rahmat 👍

Quyidagilarga alohida e'tibor berishingizni so'raymiz👇

⭐️ Soat 17:00 ga qadar berilgan buyurtmalar roʻyxatdan oʻtgan va tasdiqlangan kunning oʻzida yuboriladi.

⭐️ Soat 17:00 dan keyin berilgan buyurtmalar ertasi kuni jo'natiladi.

📣 Buyurtmalar kurer orqali 18:00 dan 22:00 gacha yetkazib beriladi (bu vaqt ichida kuryer siz ko'rsatgan telefon raqamingizga oldindan qo'ng'iroq qiladi va buyurtmani yetkazib beradi)

💁🏻‍♀️ Batafsil ma'lumot uchun @chopon_cheese ga yozishingiz mumkin</b>",
    'order_cancelled' => "❌ Sizning :order_id raqamli buyurtmangiz bekor qilindi!",

    'order_contact' => "<b>:order_id raqamli buyurtmangiz bo'yicha sizga xabar yuborildi.</b>",

    'error_invalid_number' => "🫢 Kiritilgan raqamda xatolik mavjud, iltimos uni tekshirgach qayta kiriting:",
    'error_unable_add' => "🙁 Siz hali mahsulot miqdorini belgilamadingiz!",


    'order_info' => "🔹 Новый заказ: :order_id
👤 Имя: :name
#️⃣ Username: :username
📞 Номер: :number_field
📍 Адрес: :address
🔸 Тип оплаты: :type

:comment:cart_list🚚Доставка: :delivery_price сум
💰Всего: :total_price сум"
];