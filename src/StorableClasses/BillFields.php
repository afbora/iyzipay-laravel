<?php

namespace Afbora\IyzipayLaravel\StorableClasses;

use Afbora\IyzipayLaravel\Exceptions\Fields\BillFieldsException;

class BillFields extends StorableClass
{

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var Address
     */
    public $shippingAddress;

    /**
     * @var Address
     */
    public $billingAddress;

    /**
     * @var string
     */
    public $identityNumber;

    /**
     * @var string
     */
    public $mobileNumber;

    protected function getFieldExceptionClass(): string
    {
        return BillFieldsException::class;
    }
}
