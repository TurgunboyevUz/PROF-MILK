<?php

namespace App\Http\Controllers;

use App\Enums\PaymeState;
use App\Exceptions\PaymeException;
use App\Models\Order;
use App\Models\PaymeTransaction;
use App\Traits\JsonRPC;
use App\Traits\Payme;
use Illuminate\Http\Request;
use SergiX44\Nutgram\Nutgram;

class PaymeController extends Controller
{
    use JsonRPC, Payme;

    public $method_list = [
        'CheckPerformTransaction',
        'CreateTransaction',
        'PerformTransaction',
        'CancelTransaction',
        'CheckTransaction',
        'GetStatement',
        'SetFiscalData',
    ];

    public $account;
    public $minAmount = 100000;
    public $maxAmount = 1000000000000000;

    public $params = [];

    public $timeout = 6000 * 1000;

    public function __construct()
    {
        $this->account = config('payme.identity');
        $this->minAmount = config('payme.min_amount');
        $this->maxAmount = config('payme.max_amount');
    }

    public function handle(Request $request)
    {
        $method = $this->method($request);

        if(!in_array($method, $this->method_list)) {
            throw new PaymeException(PaymeException::INVALID_HTTP_METHOD);
        }

        return $this->{$method}();
    }

    public function method($request)
    {
        if ($request->method() !== 'POST') {
            throw new PaymeException(PaymeException::INVALID_HTTP_METHOD);
        }

        $data = $request->all();

        if (!isset($data['method']) || !isset($data['params'])) {
            throw new PaymeException(PaymeException::JSON_PARSING_ERROR);
        }

        $this->params = $data['params'];

        return $data['method'];
    }

    public function CheckPerformTransaction()
    {
        $amount = $this->params['amount'] ?? null;
        $account = $this->params['account'] ?? null;

        $this->hasParam(['amount', 'account']);
        $this->isValidAmount($amount);
        $this->hasAccount($account);

        $account = $account[$this->account] ?? null;
        $order = Order::find($account);

        if(!$order){
            throw new PaymeException(PaymeException::USER_NOT_FOUND);
        }

        if($order->payment_status){
            throw new PaymeException(PaymeException::CANT_PERFORM_TRANS);
        }

        return $this->successCheckPerformTransaction($order);
    }

    public function CreateTransaction()
    {
        $id = $this->params['id'] ?? '';
        $time = $this->params['time'] ?? '';
        $amount = $this->params['amount'] ?? '';
        $account = $this->params['account'] ?? '';

        $this->hasParam(['id', 'time', 'amount', 'account']);
        $this->isValidAmount($amount);
        $this->hasAccount($account);

        $account = $account[$this->account] ?? null;
        $order = Order::find($account);

        if(!$order){
            throw new PaymeException(PaymeException::USER_NOT_FOUND);
        }

        $transaction = PaymeTransaction::transaction($id);

        if($transaction){
            if ($transaction->state != PaymeState::Pending) {
                throw new PaymeException(PaymeException::CANT_PERFORM_TRANS);
            }

            if(!$this->checkTimeout($transaction->create_time))
            {
                $transaction->update([
                    'state' => PaymeState::Cancelled,
                    'reason' => 4
                ]);

                throw new PaymeException(error: PaymeException::CANT_PERFORM_TRANS, customMessage: [
                    "uz" => "Vaqt tugashi o'tdi",
                    "ru" => "Тайм-аут прошел",
                    "en" => "Timeout passed"
                ]);
            }

            return $this->successCreateTransaction(
                $transaction->create_time,
                $transaction->transaction,
                $transaction->state
            );
        }

        if(PaymeTransaction::where('owner_id', $account)->exists())
        {
            throw new PaymeException(PaymeException::PENDING_PAYMENT);
        }

        $transaction = PaymeTransaction::create([
            'transaction' => $id,
            'payme_time' => $time,
            'amount' => $amount,
            'state' => PaymeState::Pending,
            'create_time' => $this->microtime(),
            'owner_id' => $account,
        ]);

        return $this->successCreateTransaction(
            $transaction->create_time,
            $transaction->transaction,
            $transaction->state
        );
    }

    public function PerformTransaction()
    {
        $this->hasParam('id');

        $id = $this->params['id'] ?? null;
        $transaction = PaymeTransaction::transaction($id);

        if(!$transaction){
            throw new PaymeException(PaymeException::TRANS_NOT_FOUND);
        }

        if($transaction->state != PaymeState::Pending){
            if($transaction->state == PaymeState::Done){
                return $this->successPerformTransaction($transaction->state, $transaction->perform_time, $transaction->transaction);
            }else{
                throw new PaymeException(PaymeException::CANT_PERFORM_TRANS);
            }
        }

        if(!$this->checkTimeout($transaction->create_time)){
            $transaction->update([
                'state' => PaymeState::Cancelled,
                'reason' => 4
            ]);

            throw new PaymeException(error: PaymeException::CANT_PERFORM_TRANS, customMessage: [
                "uz" => "Vaqt tugashi o'tdi",
                "ru" => "Тайм-аут прошел",
                "en" => "Timeout passed"
            ]);
        }

        $transaction->state = PaymeState::Done;
        $transaction->perform_time = $this->microtime();
        $transaction->save();

        $this->performOrder($transaction);
        return $this->successPerformTransaction($transaction->state, $transaction->perform_time, $transaction->transaction);
    }

    public function CancelTransaction()
    {
        $this->hasParam(['id', 'reason']);

        $id = $this->params['id'] ?? null;
        $reason = $this->params['reason'] ?? null;

        $transaction = PaymeTransaction::transaction($id);
        if(!$transaction){
            throw new PaymeException(PaymeException::TRANS_NOT_FOUND);
        }

        if ($transaction->state == PaymeState::Pending) {
            $cancelTime = $this->microtime();
            $transaction->update([
                "state" => PaymeState::Cancelled,
                "cancel_time" => $cancelTime,
                "reason" => $reason
            ]);

            return $this->successCancelTransaction($transaction->state, $cancelTime, $transaction->transaction);
        }

        if ($transaction->state != PaymeState::Done) {
            return $this->successCancelTransaction($transaction->state, $transaction->cancel_time, $transaction->transaction);
        }

        $cancelTime = $this->microtime();

        $transaction->update([
            "state" => PaymeState::Cancelled_After_Success,
            "cancel_time" => $cancelTime,
            "reason" => $reason
        ]);

        return $this->successCancelTransaction($transaction->state, $cancelTime, $transaction->transaction);
    }

    public function CheckTransaction()
    {
        $this->hasParam('id');

        $id = $this->params['id'] ?? null;
        $transaction = PaymeTransaction::transaction($id);

        if(!$transaction){
            throw new PaymeException(PaymeException::TRANS_NOT_FOUND);
        }

        return $this->successCheckTransaction(
            $transaction->create_time,
            $transaction->perform_time,
            $transaction->cancel_time,
            $transaction->transaction,
            $transaction->state,
            $transaction->reason
        );
    }

    public function GetStatement()
    {
        $this->hasParam(['from', 'to']);

        $transactions = PaymeTransaction::whereBetween('create_time', [
            $this->params['from'] ?? null,
            $this->params['to'] ?? null
        ]);

        $statement = [];

        foreach ($transactions as $transaction) {
            $statement[] = [
                'id' => $transaction->transaction,
                'time' => $this->microtime(),
                'amount' => (int) $transaction->amount,
                'account' => [$this->account => $transaction->owner_id],
                'create_time' => (int)$transaction->create_time,
                'perform_time' => (int)$transaction->perform_time,
                'cancel_time' => (int)$transaction->cancel_time,
                'transaction' => (int)$transaction->id,
                'state' => (int)$transaction->state,
                'reason' => isset($transaction->reason) ? (int) $transaction->reason : null,
                'receivers' => []
            ];
        }

        return $this->successGetStatement($statement);
    }
}
