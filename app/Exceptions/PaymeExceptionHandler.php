<?php

namespace App\Exceptions;

use App\Traits\JsonRPC;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class PaymeExceptionHandler extends ExceptionHandler
{
    use JsonRPC;

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
           // 
        });

        $this->renderable(function (PaymeException $e) {
           return $this->error($e->error);
        });
    }
}