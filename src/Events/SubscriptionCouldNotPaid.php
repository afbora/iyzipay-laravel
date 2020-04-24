<?php

namespace Afbora\IyzipayLaravel\Events;

use Afbora\IyzipayLaravel\PayableContract as Payable;

class SubscriptionCouldNotPaid
{

    /**
     * @var Payable
     */
    public $payable;

    public function __construct(Payable $payable)
    {
        $this->payable = $payable;
    }

}
