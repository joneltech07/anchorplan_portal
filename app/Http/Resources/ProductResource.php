<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'current_stock' => (int) $this->current_stock,
            'min_stock_threshold' => (int) $this->min_stock_threshold,
            'cost_price' => (float) $this->cost_price,
            'is_low_stock' => (bool) $this->is_low_stock,
        ];
    }
}
