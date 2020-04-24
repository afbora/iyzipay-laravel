<?php

namespace Afbora\IyzipayLaravel\Tests\Models;

use Afbora\IyzipayLaravel\Payable;
use Afbora\IyzipayLaravel\PayableContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements PayableContract
{
    use Payable;

    protected $fillable = [
        'name'
    ];

    public $timestamps = false;
}
