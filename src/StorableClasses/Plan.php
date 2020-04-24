<?php

namespace Afbora\IyzipayLaravel\StorableClasses;

use Afbora\IyzipayLaravel\Exceptions\Fields\PlanFieldsException;
use Afbora\IyzipayLaravel\ProductContract;
use Iyzipay\Model\BasketItemType;

class Plan extends StorableClass implements ProductContract
{
    /**
     * The plan's id
     *
     * @var string
     */
    public $id;

    /**
     * The plan's displayable name
     *
     * @var string
     */
    public $name;

    /**
     * The plan's price.
     *
     * @var integer
     */
    public $price = 0;

    /**
     * The plan's interval.
     *
     * @var string
     */
    public $interval = 'monthly';

    /**
     * The number of trial days that come with the plan.
     *
     * @var int
     */
    public $trialDays = 0;

    /**
     * The plan's features.
     *
     * @var array
     */
    public $features = [];

    /**
     * The plan's attributes.
     *
     * @var array
     */
    public $attributes = [];

    /**
     * The plan's currency
     *
     * @var string
     */
    public $currency = 'TRY';

    /**
     * Set the name of the plan.
     *
     * @param string $name
     *
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the id of the plan.
     *
     * @param string $id
     *
     * @return $this
     */
    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the price of the plan.
     *
     * @param string|integer $price
     *
     * @return $this
     */
    public function price($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Specify that the plan is on a yearly interval.
     *
     * @return $this
     */
    public function yearly()
    {
        $this->interval = 'yearly';

        return $this;
    }

    /**
     * Specify the number of trial days that come with the plan.
     *
     * @param int $trialDays
     *
     * @return $this
     */
    public function trialDays($trialDays)
    {
        $this->trialDays = $trialDays;

        return $this;
    }

    /**
     * Specify the currency of plan.
     *
     * @param $currency
     *
     * @return $this
     */
    public function currency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Specify the plan's features.
     *
     * @param array $features
     *
     * @return $this
     */
    public function features(array $features)
    {
        $this->features = $features;

        return $this;
    }

    /**
     * Get a given attribute from the plan.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function attribute($key, $default = null)
    {
        return array_get($this->attributes, $key, $default);
    }

    /**
     * Specify the plan's attributes.
     *
     * @param array $attributes
     *
     * @return $this
     */
    public function attributes(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }


    protected function getFieldExceptionClass(): string
    {
        return PlanFieldsException::class;
    }

    public function getKey()
    {
        return $this->name;
    }

    public function getKeyName()
    {
        return 'name';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getCategory()
    {
        return 'Plan';
    }

    public function getType()
    {
        return BasketItemType::VIRTUAL;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'currency' => $this->currency
        ];
    }
}
