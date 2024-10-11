
## Installation

Install project using git and composer

```bash
  $ git clone https://github.com/TurgunboyevUz/PROF-MILK.git

  $ cd PROF-MILK
  $ composer update
```

Setup configurations in .env file (copy from .env.example)
```env
TELEGRAM_TOKEN="API TOKEN"

PAYME_MIN_AMOUNT = 100000
PAYME_MAX_AMOUNT = 1000000000
PAYME_IDENTITY = 'order_id'
PAYME_LOGIN = 'Paycom'
PAYME_KEY = 'TEST_KEY'
PAYME_MERCHANT_ID = 'TEST_ID'
```

Setup configurations in config/nutgram.php
```
return [
    'admin' => [1804724171], // admin panelidan foydalanuvchi userlar ID raqami
    'username' => 'BOT USERNAME',

    'min_price' => 250000, // bu summadan baland buyurtma bo'lganda dostavka bepul
    'delivery_price' => 25000, // dostavka summasi
    'orders_chat' => 1804724171,// buyurtmalar kelib tushadigan guruh/kanal ID raqami
];
```

Migrate tables to database:
```bash
  $ php artisan migrate
```

Create Filament User for Dashboard:
```bash
  $ php artisan make:filament-user
```

Setup cron-job for command:
```bash
  $ php artisan bulk
```

Setup webhook for your own domain (like: https://domain.com/api/bot)

Dashboard: https://domain.com/admin

Use Filament username and password for dashboard.

Enjoy :)