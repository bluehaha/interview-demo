<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    protected Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function findManyWithPrice(array $productIds): Collection
    {
        return $this->product->with('price')->findMany($productIds);
    }

    public function __call($method, $args)
    {
        return $this->product->$method(...$args);
    }
}
