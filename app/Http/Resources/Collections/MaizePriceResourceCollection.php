<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Http\Resources\Collections;

use App\Data\MinMaxPriceDto;
use App\Http\Resources\MaizePriceResource;
use App\Repositories\MaizePriceRepo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MaizePriceResourceCollection extends ResourceCollection
{
    public function __construct($resource, private readonly MaizePriceRepo $priceRepo)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * Price bands are loaded once for the (country, produce_type) combinations present
     * on this page, eliminating the N+1 query that the old per-row repo instantiation caused.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $countries = $this->collection->pluck('country')->unique()->values()->all();
        /** @var array<string, MinMaxPriceDto> $priceBands */
        $priceBands = $this->priceRepo->findPriceBandsBulkForCountries($countries);

        return $this->collection->map(function ($item) use ($priceBands, $request) {
            $key = "{$item->country}:{$item->produce_type}";
            $band = $priceBands[$key] ?? new MinMaxPriceDto;

            return (new MaizePriceResource($item, $band))->toArray($request);
        })->toArray();
    }
}
