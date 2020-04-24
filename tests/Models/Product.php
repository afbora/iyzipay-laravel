<?php


namespace Models;

use Afbora\IyzipayLaravel\ProductContract;
use Illuminate\Database\Eloquent\Model;
use Iyzipay\Model\BasketItemType;

class Product extends Model implements ProductContract
{

    protected $fillable = [
        'name',
        'price',
        'category'
    ];

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
        return $this->category;
    }

    public function getType()
    {
        return BasketItemType::VIRTUAL;
    }
}
