<?php

namespace App\Traits;

trait JsonRPC
{
    public function success($result)
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'result' => $result
        ]);
    }

    public function error($error)
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'error' => $error
        ]);
    }
}

?>