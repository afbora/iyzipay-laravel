<?php

namespace Afbora\IyzipayLaravel;

interface ProductContract
{
    public function getKey();

    public function getKeyName();

    public function getName();

    public function getPrice();

    public function getCategory();

    public function getType();

    public function toArray();
}
