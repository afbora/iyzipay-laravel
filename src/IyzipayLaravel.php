<?php

namespace Afbora\IyzipayLaravel;

use Afbora\IyzipayLaravel\Exceptions\Card\PayableMustHaveCreditCardException;
use Afbora\IyzipayLaravel\Exceptions\Fields\BillFieldsException;
use Afbora\IyzipayLaravel\Exceptions\Card\CardRemoveException;
use Afbora\IyzipayLaravel\Exceptions\Fields\CreditCardFieldsException;
use Afbora\IyzipayLaravel\Exceptions\Transaction\TransactionSaveException;
use Afbora\IyzipayLaravel\Exceptions\Transaction\TransactionVoidException;
use Afbora\IyzipayLaravel\Exceptions\Iyzipay\IyzipayAuthenticationException;
use Afbora\IyzipayLaravel\Exceptions\Iyzipay\IyzipayConnectionException;
use Afbora\IyzipayLaravel\Models\CreditCard;
use Afbora\IyzipayLaravel\Models\Transaction;
use Afbora\IyzipayLaravel\Traits\ManagesPlans;
use Afbora\IyzipayLaravel\Traits\PreparesCreditCardRequest;
use Afbora\IyzipayLaravel\Traits\PreparesTransactionRequest;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Iyzipay\Model\ApiTest;
use Iyzipay\Model\Payment;
use Iyzipay\Options;
use Iyzipay\Model\Locale;
use Afbora\IyzipayLaravel\PayableContract as Payable;

class IyzipayLaravel
{

    use PreparesCreditCardRequest, PreparesTransactionRequest, ManagesPlans;

    /**
     * @var Options
     */
    protected $apiOptions;

    public function __construct()
    {
        $this->initializeApiOptions();
        $this->checkApiOptions();
    }

    /**
     * Adds credit card for billable & payable model.
     *
     * @param PayableContract $payable
     * @param array $attributes
     *
     * @return CreditCard
     * @throws BillFieldsException
     * @throws CreditCardFieldsException
     */
    public function addCreditCard(Payable $payable, array $attributes = []): CreditCard
    {
        $this->validateBillable($payable);
        $this->validateCreditCardAttributes($attributes);

        $card = $this->createCardOnIyzipay($payable, $attributes);

        $creditCardModel = new CreditCard([
            'alias' => $card->getCardAlias(),
            'number' => $card->getBinNumber(),
            'token' => $card->getCardToken(),
            'bank' => $card->getCardBankName()
        ]);
        $payable->creditCards()->save($creditCardModel);

        $payable->iyzipay_key = $card->getCardUserKey();
        $payable->save();

        return $creditCardModel;
    }

    /**
     * Remove credit card for billable & payable model.
     *
     * @param CreditCard $creditCard
     *
     * @return bool
     * @throws CardRemoveException
     */
    public function removeCreditCard(CreditCard $creditCard): bool
    {
        $this->removeCardOnIyzipay($creditCard);
        $creditCard->delete();

        return true;
    }

    /**
     * @param PayableContract $payable
     * @param Collection $products
     * @param $currency
     * @param $installment
     * @param bool $subscription
     *
     * @return Transaction $transactionModel
     * @throws TransactionSaveException
     */
    public function singlePayment(Payable $payable, Collection $products, $currency, $installment, $subscription = false): Transaction
    {
        $this->validateBillable($payable);
        $this->validateHasCreditCard($payable);

        $messages = []; // @todo imporove here
        foreach ($payable->creditCards as $creditCard) {
            try {
                $transaction = $this->createTransactionOnIyzipay(
                    $payable,
                    $creditCard,
                    compact('products', 'currency', 'installment'),
                    $subscription
                );

                return $this->storeTransactionModel($transaction, $payable, $products, $creditCard);
            } catch (TransactionSaveException $e) {
                $messages[] = $creditCard->number . ': ' . $e->getMessage();
                continue;
            }
        }

        throw new TransactionSaveException(implode(', ', $messages));
    }

    /**
     * @param Transaction $transactionModel
     *
     * @return Transaction
     * @throws TransactionVoidException
     */
    public function void(Transaction $transactionModel): Transaction
    {
        $cancel = $this->createCancelOnIyzipay($transactionModel);

        $transactionModel->voided_at = Carbon::now();
        $refunds = $transactionModel->refunds;
        $refunds[] = [
            'type' => 'void',
            'amount' => $cancel->getPrice(),
            'iyzipay_key' => $cancel->getPaymentId()
        ];

        $transactionModel->refunds = $refunds;
        $transactionModel->save();

        return $transactionModel;
    }

    /**
     * Initializing API options with the given credentials.
     */
    private function initializeApiOptions()
    {
        $this->apiOptions = new Options();
        $this->apiOptions->setBaseUrl(config('iyzipay.baseUrl'));
        $this->apiOptions->setApiKey(config('iyzipay.apiKey'));
        $this->apiOptions->setSecretKey(config('iyzipay.secretKey'));
    }

    /**
     * Check if api options has been configured successfully.
     *
     * @throws IyzipayAuthenticationException
     * @throws IyzipayConnectionException
     */
    private function checkApiOptions()
    {
        try {
            $check = ApiTest::retrieve($this->apiOptions);
        } catch (\Exception $e) {
            throw new IyzipayConnectionException();
        }

        if ($check->getStatus() != 'success') {
            throw new IyzipayAuthenticationException();
        }
    }

    /**
     * @param PayableContract $payable
     *
     * @throws BillFieldsException
     */
    private function validateBillable(Payable $payable): void
    {
        if (!$payable->isBillable()) {
            throw new BillFieldsException();
        }
    }

    /**
     * @param PayableContract $payable
     *
     * @throws PayableMustHaveCreditCardException
     */
    private function validateHasCreditCard(Payable $payable): void
    {
        if ($payable->creditCards->isEmpty()) {
            throw new PayableMustHaveCreditCardException();
        }
    }

    /**
     * @param Payment $transaction
     * @param PayableContract $payable
     * @param Collection $products
     * @param CreditCard $creditCard
     *
     * @return Transaction
     */
    private function storeTransactionModel(
        Payment $transaction,
        Payable $payable,
        Collection $products,
        CreditCard $creditCard
    ): Transaction
    {
        $iyzipayProducts = [];
        foreach ($transaction->getPaymentItems() as $paymentItem) {
            $iyzipayProducts[] = [
                'iyzipay_key' => $paymentItem->getPaymentTransactionId(),
                'paidPrice' => $paymentItem->getPaidPrice(),
                'product' => $products->where(
                    $products[0]->getKeyName(),
                    $paymentItem->getItemId()
                )->first()->toArray()
            ];
        }

        $transactionModel = new Transaction([
            'amount' => $transaction->getPaidPrice(),
            'products' => $iyzipayProducts,
            'iyzipay_key' => $transaction->getPaymentId(),
            'currency' => $transaction->getCurrency()
        ]);

        $transactionModel->creditCard()->associate($creditCard);
        $payable->transactions()->save($transactionModel);

        return $transactionModel->fresh();
    }

    protected function getLocale(): string
    {
        return (config('app.locale') == 'tr') ? Locale::TR : Locale::EN;
    }

    protected function getOptions(): Options
    {
        return $this->apiOptions;
    }
}
