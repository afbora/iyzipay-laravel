<?php

namespace Afbora\IyzipayLaravel\Traits;

use Afbora\IyzipayLaravel\Exceptions\Card\CardRemoveException;
use Afbora\IyzipayLaravel\Exceptions\Card\CardSaveException;
use Afbora\IyzipayLaravel\Exceptions\Fields\CreditCardFieldsException;
use Afbora\IyzipayLaravel\Models\CreditCard;
use Afbora\IyzipayLaravel\PayableContract as Payable;
use Illuminate\Support\Facades\Validator;
use Iyzipay\Model\Card;
use Iyzipay\Model\CardInformation;
use Iyzipay\Options;
use Iyzipay\Request\CreateCardRequest;
use Iyzipay\Request\DeleteCardRequest;

trait PreparesCreditCardRequest
{

    /**
     * @param $attributes
     * @throws CreditCardFieldsException
     */
    private function validateCreditCardAttributes($attributes): void
    {
        $v = Validator::make($attributes, [
            'alias' => 'required',
            'holder' => 'required',
            'number' => 'required|digits_between:15,16',
            'month' => 'required|digits:2',
            'year' => 'required|digits:4'
        ]);

        if ($v->fails()) {
            throw new CreditCardFieldsException(implode(',', $v->errors()->all()));
        }
    }

    /**
     * Prepares credit card on iyzipay.
     *
     * @param Payable $payable
     * @param $attributes
     * @return Card
     * @throws CardSaveException
     */
    private function createCardOnIyzipay(Payable $payable, $attributes): Card
    {
        $cardRequest = $this->createCardRequest($payable, $attributes);

        try {
            $card = Card::create($cardRequest, $this->getOptions());
        } catch (\Exception $e) {
            throw new CardSaveException();
        }

        unset($cardRequest);

        if ($card->getStatus() != 'success') {
            throw new CardSaveException($card->getErrorMessage());
        }
        return $card;
    }

    /**
     * Prepare card request class for iyzipay.
     *
     * @param Payable $payable
     * @param $attributes
     * @return CreateCardRequest
     */
    private function createCardRequest(Payable $payable, $attributes): CreateCardRequest
    {
        $cardRequest = new CreateCardRequest();
        $cardRequest->setLocale($this->getLocale());
        $cardRequest->setEmail($payable->bill_fields->email);

        if (!empty($payable->iyzipay_key)) {
            $cardRequest->setCardUserKey($payable->iyzipay_key);
        }

        $cardRequest->setCard($this->createCardInformation($attributes));

        return $cardRequest;
    }

    /**
     * Removes a card on iyzipay
     *
     * @param CreditCard $creditCard
     * @throws CardRemoveException
     */
    private function removeCardOnIyzipay(CreditCard $creditCard): void
    {
        try {
            $result = Card::delete($this->removeCardRequest($creditCard), $this->getOptions());
        } catch (\Exception $e) {
            throw new CardRemoveException();
        }

        if ($result->getStatus() != 'success') {
            throw new CardRemoveException($result->getErrorMessage());
        }
    }

    /**
     * Prepares remove card request class for iyzipay.
     *
     * @param CreditCard $creditCard
     * @return DeleteCardRequest
     */
    private function removeCardRequest(CreditCard $creditCard): DeleteCardRequest
    {
        $removeRequest = new DeleteCardRequest();
        $removeRequest->setCardUserKey($creditCard->owner->iyzipay_key);
        $removeRequest->setCardToken($creditCard->token);
        $removeRequest->setLocale($this->getLocale());

        return $removeRequest;
    }

    /**
     * Prepares card information class for iyzipay
     *
     * @param $attributes
     * @return CardInformation
     */
    private function createCardInformation($attributes): CardInformation
    {
        $cardInformation = new CardInformation();
        $cardInformation->setCardAlias($attributes['alias']);
        $cardInformation->setCardHolderName($attributes['holder']);
        $cardInformation->setCardNumber($attributes['number']);
        $cardInformation->setExpireMonth($attributes['month']);
        $cardInformation->setExpireYear($attributes['year']);

        return $cardInformation;
    }

    abstract protected function getLocale(): string;

    abstract protected function getOptions(): Options;
}
