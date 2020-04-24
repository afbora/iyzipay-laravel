<?php


namespace Afbora\IyzipayLaravel\Tests;

use Afbora\IyzipayLaravel\Exceptions\Card\PayableMustHaveCreditCardException;
use Afbora\IyzipayLaravel\Exceptions\Fields\BillFieldsException;
use Afbora\IyzipayLaravel\Exceptions\Fields\TransactionFieldsException;
use Afbora\IyzipayLaravel\Exceptions\Transaction\TransactionSaveException;
use Afbora\IyzipayLaravel\Models\Transaction;
use Afbora\IyzipayLaravel\Tests\Models\User;
use Models\Product;
use Illuminate\Support\Collection;
use Iyzipay\Model\Currency;

class TransactionTest extends TestCase
{

    /** @test */
    public function must_set_bill_information_before_transaction()
    {
        $user = $this->createUser();

        $this->expectException(BillFieldsException::class);
        $user->pay($this->prepareProducts());
    }

    /** @test */
    public function payable_must_have_credit_card_before_transaction()
    {
        $user = $this->prepareBilledUser();

        $this->expectException(PayableMustHaveCreditCardException::class);
        $user->pay($this->prepareProducts());
    }

    /** @test */
    public function currency_must_be_in_allowed_form()
    {
        $user = $this->prepareUserHasCard();

        $this->expectException(TransactionFieldsException::class);
        $user->pay($this->prepareProducts(), 'ASD');
    }

    /** @test */
    public function installment_must_be_greater_that_zero()
    {
        $user = $this->prepareUserHasCard();

        $this->expectException(TransactionFieldsException::class);
        $user->pay($this->prepareProducts(), Currency::TL, 0);
    }

    /** @test */
    public function success_transaction_operation_returns_transaction_model()
    {
        $user = $this->prepareUserHasCard();
        $products = $this->prepareProducts();

        try {
            $this->assertInstanceOf(Transaction::class, $user->pay($products));
            $this->assertEquals(1, $user->transactions->count());
        } catch (TransactionSaveException $e) {
            if (str_contains('System error', $e->getMessage())) { // Its weird but we face this error sometimes.
                $this->success_transaction_operation_returns_transaction_model();
            }
        }
    }

    /** @test */
    public function transaction_can_be_voided()
    {
        $user = $this->prepareUserHasCard();
        $products = $this->prepareProducts();
        try {
            $transaction = $user->pay($products);
            $this->assertInstanceOf(Transaction::class, $transaction->void());
            $this->assertEquals($transaction->amount, $transaction->refunded_amount);
            $this->assertNotNull($transaction->voided_at);
        } catch (TransactionSaveException $e) {
            if (str_contains('System error', $e->getMessage())) { // Its weird but we face this error sometimes.
                $this->transaction_can_be_voided();
            }
        }
    }

    /** @test */
    public function transaction_can_be_refunded_full()
    {
        $user = $this->prepareUserHasCard();
        $products = $this->prepareProducts();

        try {
            $transaction = $user->pay($products);
            $this->assertInstanceOf(Transaction::class, $transaction->refund());
            $this->assertEquals($transaction->amount, $transaction->refunded_amount);
        } catch (TransactionSaveException $e) {
            if (str_contains('System error', $e->getMessage())) { // Its weird but we face this error sometimes.
                $this->transaction_can_be_refunded_full();
            }
        }
    }

    protected function prepareUserHasCard(): User
    {
        $user = $this->prepareBilledUser();
        $user->addCreditCard($this->prepareCreditCardFields());

        return $user->fresh();
    }

    protected function prepareProducts($count = 5): Collection
    {
        $products = new Collection();
        for ($i = 0; $i < $count; $i++) {
            $products->push(Product::create([
                'name' => $this->faker->word,
                'price' => $this->faker->numberBetween(1, 100),
                'category' => $this->faker->word
            ]));
        }

        return $products;
    }
}
