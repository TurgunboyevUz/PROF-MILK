<?php

namespace App\Exceptions;

use Exception;

class PaymeException extends Exception
{
    public array $error;

    const SYSTEM_ERROR = -32400;
    const AUTH_ERROR = -32504;
    const WRONG_AMOUNT = -31001;
    const USER_NOT_FOUND = -31050;
    const JSON_RPC_ERROR = -32600;
    const TRANS_NOT_FOUND = -31003;
    const METHOD_NOT_FOUND = -32601;
    const JSON_PARSING_ERROR = -32700;
    const CANT_PERFORM_TRANS = -31008;
    const CANT_CANCEL_TRANSACTION = -31007;
    const PENDING_PAYMENT = -31099;
    const INVALID_HTTP_METHOD = -32300;

    public function __construct($error, $customMessage = null)
    {
        $this->error = [
            'code' => $error,
            'message' => $customMessage ?? $this->getErrorMessage($error)
        ];

        parent::__construct();
    }

    public function getErrorMessage($code): array
    {
        $messages = [

            self::INVALID_HTTP_METHOD => [
                "uz" => "Xato so'rov",
                "ru" => "Ошибка запроса",
                "en" => "Error request"
            ],

            self::SYSTEM_ERROR => [
                "uz" => "Ichki sestema hatoligi",
                "ru" => "Внутренняя ошибка сервера",
                "en" => "Internal server error"
            ],

            self::WRONG_AMOUNT => [
                "uz" => "Notug'ri summa.",
                "ru" => "Неверная сумма.",
                "en" => "Wrong amount.",
            ],

            self::USER_NOT_FOUND => [
                "uz" => "Foydalanuvchi topilmadi",
                "ru" => "Пользователь не найден",
                "en" => "User not found",
            ],

            self::JSON_RPC_ERROR => [
                "uz" => "Notog`ri JSON-RPC obyekt yuborilgan.",
                "ru" => "Передан неправильный JSON-RPC объект.",
                "en" => "Handed the wrong JSON-RPC object."
            ],

            self::TRANS_NOT_FOUND => [
                "uz" => "Transaction not found",
                "ru" => "Трансакция не найдена",
                "en" => "Transaksiya topilmadi"
            ],

            self::METHOD_NOT_FOUND => [
                "uz" => "Metod topilmadi",
                "ru" => "Запрашиваемый метод не найден.",
                "en" => "Method not found"
            ],

            self::JSON_PARSING_ERROR => [
                "uz" => "Json pars qilganda hatolik yuz berdi",
                "ru" => "Ошибка при парсинге JSON",
                "en" => "Error while parsing json"
            ],

            self::CANT_PERFORM_TRANS => [
                "uz" => "Bu operatsiyani bajarish mumkin emas",
                "ru" => "Невозможно выполнить данную операцию.",
                "en" => "Can't perform transaction",
            ],

            self::CANT_CANCEL_TRANSACTION => [
                "uz" => "Transaksiyani qayyarib bolmaydi",
                "ru" => "Невозможно отменить транзакцию",
                "en" => "You can not cancel the transaction"
            ],

            self::PENDING_PAYMENT => [
                "uz" => "To'lov kutilmoqda",
                "ru" => "В ожидании оплаты",
                "en" => "Pending payment"
            ],

            self::AUTH_ERROR => [
                "uz" => "Avtorizatsiyadan otishda xatolik",
                "ru" => "Ошибка аутентификации",
                "en" => "Auth error"
            ]
        ];

        return $messages[$code] ?? [];
    }
}
