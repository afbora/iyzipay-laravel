<?php

namespace Afbora\IyzipayLaravel\Tests;

use Afbora\IyzipayLaravel\Exceptions\Fields\BillFieldsException;
use Afbora\IyzipayLaravel\StorableClasses\BillFields;

class BillTest extends TestCase
{

    /** @test */
    public function must_set_all_required_fields_for_bill()
    {
        $user = $this->createUser();

        $this->expectException(BillFieldsException::class);
        $user->bill_fields = new BillFields([
            'first_name' => $this->faker->firstName
        ]);
        $user->save();
    }

    /** @test */
    public function check_bill_fields_has_been_set_correct_and_can_be_updated_after_creation()
    {
        $user              = $this->createUser();
        $billFields        = $this->prepareBillFields();
        $user->bill_fields = $billFields;

        $this->assertEquals($billFields->firstName, $user->bill_fields->firstName);
        $this->assertEquals($billFields->lastName, $user->bill_fields->lastName);
        $this->assertEquals($billFields->email, $user->bill_fields->email);
        $this->assertEquals($billFields->shippingAddress->city, $user->bill_fields->shippingAddress->city);
        $this->assertEquals($billFields->shippingAddress->country, $user->bill_fields->shippingAddress->country);
        $this->assertEquals($billFields->shippingAddress->address, $user->bill_fields->shippingAddress->address);
        $this->assertEquals($billFields->billingAddress->city, $user->bill_fields->billingAddress->city);
        $this->assertEquals($billFields->billingAddress->country, $user->bill_fields->billingAddress->country);
        $this->assertEquals($billFields->billingAddress->address, $user->bill_fields->billingAddress->address);
    }
}
