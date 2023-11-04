<?php

namespace App\Services\Cashier\DTO;

use Spatie\DataTransferObject\DataTransferObjectCollection;

class CheckoutItemCollection extends DataTransferObjectCollection
{
    public function current(): CheckoutItemData
    {
        return parent::current();
    }
}
