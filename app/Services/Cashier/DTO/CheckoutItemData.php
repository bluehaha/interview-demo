<?php

namespace App\Services\Cashier\DTO;

use App\Models\Product;
use Spatie\DataTransferObject\DataTransferObject;

class CheckoutItemData extends DataTransferObject
{
    // 要購買的商品 ID
    public Product $product;

    // 購買數量
    public int $quantity;
}
