<?php

namespace Afbora\IyzipayLaravel\Tests;

use Afbora\IyzipayLaravel\Exceptions\Fields\CreditCardFieldsException;
use Afbora\IyzipayLaravel\Exceptions\Fields\BillFieldsException;
use Afbora\IyzipayLaravel\Models\CreditCard;

class CreditCardsTest extends TestCase
{

    /** @test */
    public function must_set_bill_information_before_adding_credit_cards()
    {
        $user = $this->createUser();

        $this->expectException(BillFieldsException::class);
        $user->addCreditCard($this->prepareCreditCardFields());
    }

    /** @test */
    public function must_set_all_credit_card_fields()
    {
        $user = $this->prepareBilledUser();

        $this->expectException(CreditCardFieldsException::class);
        $user->addCreditCard([
            'alias' => $this->faker->word
        ]);
    }

    /** @test */
    public function add_credit_card_operations_returns_card_model()
    {
        $user = $this->prepareBilledUser();

        $this->assertInstanceOf(
            CreditCard::class,
            $user->addCreditCard($this->prepareCreditCardFields())
        );

        $this->assertEquals(1, $user->creditCards->count());
        $this->assertNotEmpty($user->iyzipay_key);
    }

    /** @test */
    public function remove_credit_card_operations_return_true_if_succeed()
    {
        $user = $this->prepareBilledUser();
        $creditCard = $user->addCreditCard($this->prepareCreditCardFields());

        $this->assertTrue($user->removeCreditCard($creditCard));
        $this->assertEquals(0, $user->fresh()->creditCards->count());
    }
}
