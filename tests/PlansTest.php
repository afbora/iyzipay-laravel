<?php

namespace Afbora\IyzipayLaravel\Tests;

use Afbora\IyzipayLaravel\IyzipayLaravelFacade as IyzipayLaravel;

class PlansTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        IyzipayLaravel::rollbackPlans();
    }

    /** @test */
    public function developer_can_create_plan_and_access_its_attributes()
    {
        $plan = IyzipayLaravel::plan('aylik', 'Aylık')->trialDays(15)->price(20)
            ->features([
                'first',
                'second',
                'third'
            ])
            ->attributes([
                'reports' => true,
                'posts' => false
            ]);

        $this->assertEquals('Aylık', $plan->name);
        $this->assertEquals('aylik', $plan->id);
        $this->assertEquals(15, $plan->trialDays);
        $this->assertEquals(20, $plan->price);
        $this->assertEquals('TRY', $plan->currency);
        $this->assertEquals(['first', 'second', 'third'], $plan->features);
        $this->assertTrue($plan->attribute('reports'));
        $this->assertFalse($plan->attribute('posts'));
        $this->assertFalse($plan->attribute('moderates', false));
        $this->assertTrue($plan->attribute('moderates', true));
    }

    /** @test */
    public function developer_can_create_multiple_plans_and_filters_them()
    {
        IyzipayLaravel::plan('aylik-standart', 'Aylık Standart')->trialDays(15)->price(20);
        IyzipayLaravel::plan('aylik-platinum', 'Aylık Platinum')->trialDays(15)->price(40);
        IyzipayLaravel::plan('yillik-kucuk', 'Yıllık Küçük')->yearly()->trialDays(15)->price(150);
        IyzipayLaravel::plan('yillik-standart', 'Yıllık Standart')->yearly()->trialDays(15)->price(200);
        IyzipayLaravel::plan('yillik-platinum', 'Yıllık Platinum')->yearly()->trialDays(15)->price(400);

        $this->assertEquals(2, IyzipayLaravel::monthlyPlans()->count());
        $this->assertEquals(3, IyzipayLaravel::yearlyPlans()->count());
    }
}
