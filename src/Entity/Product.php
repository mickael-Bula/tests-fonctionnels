<?php

namespace App\Entity;

use JMS\Serializer\Exception\LogicException;

class Product
{
    const FOOD_PRODUCT = 'food';

    public function __construct($name, $type, $price)
    {
        $this->name = $name;
        $this->type = $type;
        $this->price = $price;
    }

    public function computeTVA(): float | LogicException
    {
        if ($this->price < 0) {
            throw new LogicException('The TVA cannot be negative.');
        }

        if (self::FOOD_PRODUCT == $this->type) {
            return $this->price * 0.055;
        }

        return $this->price * 0.196;
    }
}