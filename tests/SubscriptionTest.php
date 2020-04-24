<?php


namespace Afbora\IyzipayLaravel\Tests;

use Afbora\IyzipayLaravel\Exceptions\Transaction\TransactionSaveException;
use Afbora\IyzipayLaravel\IyzipayLaravel;
use Carbon\Carbon;

class SubscriptionTest extends TestCase
{

    /** @test */
    public function users_can_subscribe_plans()
    {
        $this->createPlans();
        $user = $this->createUser();

        $plan        = IyzipayLaravel::monthlyPlans()->first();
        $anotherPlan = IyzipayLaravel::yearlyPlans()->first();
        $user->subscribe($plan);

        $this->assertEquals(1, $user->subscriptions->count());
        $this->assertTrue($user->isSubscribeTo($plan));
        $this->assertFalse($user->isSubscribeTo($anotherPlan));

        $user->subscribe($anotherPlan);
        $user = $user->fresh();
        $this->assertEquals(2, $user->subscriptions->count());
        $this->assertTrue($user->isSubscribeTo($plan));
        $this->assertTrue($user->isSubscribeTo($anotherPlan));
    }

    /** @test */
    public function we_must_pay_user_when_charge_date_has_come()
    {
        $user = $this->prepareBilledUser();
        $user->addCreditCard($this->prepareCreditCardFields());
        $plan = IyzipayLaravel::plan('asap', 'ASAP')->price(10);
        $user->subscribe($plan);

        try {
            $user->paySubscription();
            $this->assertEquals(1, $user->transactions->count());
            $this->assertEquals(1, $user->subscriptions->first()->transactions->count());
            $this->assertEquals(Carbon::now()->startOfDay()->addMonth(), $user->subscriptions->first()->next_charge_at);
        } catch (TransactionSaveException $e) {
            if (str_contains('System error', $e->getMessage())) { // Its weird but we face this error sometimes.
                $this->we_must_pay_user_when_charge_date_has_come();
            }
        }
    }

    /** @test */
    public function we_must_not_pay_user_when_charge_date_has_not_come()
    {
        $user = $this->prepareBilledUser();
        $user->addCreditCard($this->prepareCreditCardFields());
        $plan = IyzipayLaravel::plan('10-days-later', '10 Days Later')->trialDays(10)->price(10);
        $user->subscribe($plan);

        try {
            $user->paySubscription();
            $this->assertEquals(0, $user->transactions->count());
            $this->assertEquals(0, $user->subscriptions->first()->transactions->count());
        } catch (TransactionSaveException $e) {
            if (str_contains('System error', $e->getMessage())) { // Its weird but we face this error sometimes.
                $this->we_must_not_pay_user_when_charge_date_has_not_come();
            }
        }
    }

    /** @test */
    public function payable_can_cancel_its_subscription()
    {
        $user = $this->prepareBilledUser();
        $user->addCreditCard($this->prepareCreditCardFields());
        $plan = IyzipayLaravel::plan('10-days-later', '10 Days Later')->trialDays(10)->price(10);
        $user->subscribe($plan);

        $user->subscriptions->first()->cancel();

        $this->assertTrue($user->isSubscribeTo($plan));

        $plan = IyzipayLaravel::plan('asap', 'ASAP')->price(10);
        $user->subscribe($plan);

        $user = $user->fresh();
        $user->subscriptions[1]->cancel();
        $this->assertFalse($user->isSubscribeTo($plan));

        $plan = IyzipayLaravel::plan('15-days-later', '15 Days Later')->trialDays(15)->price(10);
        $user->subscribe($plan);

        $user = $user->fresh();
        $user->subscriptions[2]->cancel();
        $user->subscriptions[2]->next_charge_at = Carbon::yesterday();
        $user->subscriptions[2]->save();
        $this->assertFalse($user->isSubscribeTo($plan));
    }
}
