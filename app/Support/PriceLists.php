<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Support;

/**
 * The price lists the mobile app reads (database views over the catalogue): how
 * the admin lists them and how a row is described in a change preview.
 */
final class PriceLists
{
    /**
     * @var array<string, array{title: string, table: string, country: string, order: list<string>, columns: array<string, string>, raw?: array<string, string>}>
     */
    public const LISTS = [
        'fertilizer' => [
            'title' => 'Fertilizer prices', 'table' => 'vw_fertilizer_prices', 'country' => 'country',
            'order' => ['fertilizer_key', 'sort_order'],
            'columns' => ['country' => 'Country', 'fertilizer_key' => 'Fertilizer key', 'price_per_bag' => 'Price per bag', 'sort_order' => 'Order'],
        ],
        'cassava' => [
            'title' => 'Cassava prices', 'table' => 'vw_cassava_prices', 'country' => 'country',
            'order' => ['min_local_price'],
            'columns' => ['country' => 'Country', 'min_local_price' => 'From', 'max_local_price' => 'To', 'average' => 'Average sent'],
            'raw' => ['average' => '(min_local_price + max_local_price) / 2'],
        ],
        'maize' => [
            'title' => 'Maize prices', 'table' => 'vw_maize_prices', 'country' => 'country',
            'order' => ['produce_type', 'min_local_price'],
            'columns' => ['country' => 'Country', 'produce_type' => 'Produce', 'min_local_price' => 'From', 'max_local_price' => 'To', 'average' => 'Average sent'],
            'raw' => ['average' => '(min_local_price + max_local_price) / 2'],
        ],
        'potato' => [
            'title' => 'Sweet potato prices', 'table' => 'vw_potato_prices', 'country' => 'country',
            'order' => ['min_local_price'],
            'columns' => ['country' => 'Country', 'min_local_price' => 'From', 'max_local_price' => 'To', 'average' => 'Average sent'],
            'raw' => ['average' => '(min_local_price + max_local_price) / 2'],
        ],
        'investment' => [
            'title' => 'Investment amounts', 'table' => 'vw_investment_amount', 'country' => 'country',
            'order' => ['sort_order'],
            'columns' => ['country' => 'Country', 'investment_amount' => 'Amount', 'area_unit' => 'Per', 'sort_order' => 'Order'],
        ],
        'operations' => [
            'title' => 'Operation costs', 'table' => 'vw_operation_costs', 'country' => 'country_code',
            'order' => ['operation_name', 'operation_type', 'min_cost'],
            'columns' => ['country_code' => 'Country', 'operation_name' => 'Operation', 'operation_type' => 'Type', 'min_cost' => 'From', 'max_cost' => 'To'],
        ],
    ];

    /**
     * Text columns a list can be searched by.
     *
     * @var array<string, list<string>>
     */
    public const SEARCH = [
        'fertilizer' => ['fertilizer_key'],
        'maize' => ['produce_type'],
        'operations' => ['operation_name', 'operation_type'],
    ];

    /**
     * What makes a row one of the two the client shows in words rather than as a
     * price ("I don't know", "Exact price"), as a SQL condition. Lists without such
     * a row are not here.
     *
     * @var array<string, string>
     */
    public const MARKER = [
        'fertilizer' => 'price_per_bag <= 0',
        'cassava' => 'min_local_price < 0',
        'maize' => 'min_local_price < 0',
        'potato' => 'min_local_price < 0',
        'investment' => 'investment_amount = 0',
    ];

    /** Lists whose API responses carry the currency symbol in their labels. */
    private const WITH_CURRENCY = ['fertilizer', 'cassava', 'investment'];

    /**
     * The text the API gives a client to show for a row (its `label`), so an admin
     * sees exactly what the farmer is offered.
     */
    public static function label(string $list, object $row, ?string $symbol): string
    {
        $symbol = in_array($list, self::WITH_CURRENCY, true) ? $symbol : null;

        return match ($list) {
            'fertilizer' => PriceMarker::labelForPrice($row->price_per_bag, $symbol),
            'cassava', 'potato', 'maize' => PriceMarker::labelForRange($row->min_local_price, $row->max_local_price, $symbol),
            'investment' => (float) $row->investment_amount === 0.0 ? PriceMarker::EXACT_AMOUNT : PriceMarker::labelForPrice($row->investment_amount, $symbol),
            'operations' => PriceMarker::labelForRange($row->min_cost, $row->max_cost),
        };
    }

    /**
     * A row in words and the value it holds, for a before-and-after preview.
     * The id of a row is stable across a change of rule, so the two sides match.
     *
     * @return array{label: string, value: string}
     */
    public static function describe(string $list, object $row): array
    {
        return match ($list) {
            'fertilizer' => [
                'label' => "{$row->country} {$row->fertilizer_key} (choice {$row->sort_order})",
                'value' => self::number($row->price_per_bag),
            ],
            'cassava', 'potato' => [
                'label' => "{$row->country} " . ((float) $row->min_local_price < 0 ? 'exact price' : 'band ' . ($row->id % 10)),
                'value' => (float) $row->min_local_price < 0 ? 'exact' : self::number($row->min_local_price) . ' – ' . self::number($row->max_local_price),
            ],
            'maize' => [
                'label' => "{$row->country} {$row->produce_type} " . ((float) $row->min_local_price < 0 ? 'exact price' : 'band ' . ($row->id % 10)),
                'value' => (float) $row->min_local_price < 0 ? 'exact' : self::number($row->min_local_price) . ' – ' . self::number($row->max_local_price),
            ],
            'investment' => [
                'label' => "{$row->country} amount " . $row->sort_order,
                'value' => self::number($row->investment_amount),
            ],
            'operations' => [
                'label' => "{$row->country_code} {$row->operation_name} ({$row->operation_type}) " . ($row->id % 10),
                'value' => self::number($row->min_cost) . ' – ' . self::number($row->max_cost),
            ],
        };
    }

    /**
     * 12000.00 as 12,000 and 8.5 as 8.5.
     */
    private static function number(mixed $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ','), '0'), '.');
    }
}
