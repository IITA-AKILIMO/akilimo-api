<?php

/**
 * (c) 2026 AKILIMO — https://akilimo.org
 *
 * For licence information, see the LICENCE file.
 */

namespace App\Support;

/**
 * The small reference and settings tables the admin manages through one set of
 * screens (ReferenceDataController): what the price forms choose from, and the
 * rules the app's price lists are computed with.
 *
 * Each entry says what the table is, which columns identify a row (key) and which
 * can be edited (fields). A field is text, number or select; a select takes its
 * options from another table. The key of an existing row is read-only (renaming
 * a row that others point to is done by adding the new one and removing the old).
 *
 * @phpstan-type Field array{name: string, label: string, type: string, key?: bool, rules: list<mixed>, from?: array{table: string, value: string, label?: string}, step?: string, help?: string}
 */
final class ReferenceTables
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'price-units' => [
                'title' => 'Price units',
                'description' => 'The units a catalogue price can be in. Offered as a list in the price forms.',
                'table' => 'price_units',
                'order' => ['sort_order', 'value'],
                'fields' => [
                    self::text('value', 'Value', ['required', 'string', 'max:15', 'regex:/^[a-z0-9_]+$/'], key: true, help: 'Lower case letters, digits and underscores, e.g. per_bag'),
                    self::text('label', 'Label', ['required', 'string', 'max:50']),
                    self::number('sort_order', 'Order', ['required', 'integer', 'min:0'], default: 0),
                ],
            ],
            'price-items' => [
                'title' => 'Price items',
                'description' => 'What a catalogue price can be for, and the unit each item is priced in. Choosing an item in the price form fills in its unit.',
                'table' => 'price_items',
                'order' => ['item'],
                'fields' => [
                    self::text('item', 'Item', ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_+]+$/'], key: true, help: 'Letters, digits, underscores, e.g. cassUP'),
                    self::select('unit', 'Unit', ['required', 'string'], from: ['table' => 'price_units', 'value' => 'value', 'label' => 'label']),
                ],
            ],
            'fertilizer-use-cases' => [
                'area' => 'fertilizers',
                'title' => 'Fertilizer use cases',
                'description' => 'What a fertilizer is offered for in a country (FR, CIM, CIS, ALL). Picked in the fertilizer form.',
                'table' => 'fertilizer_use_cases',
                'order' => ['sort_order', 'value'],
                'fields' => [
                    self::text('value', 'Value', ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_]+$/'], key: true),
                    self::text('label', 'Label', ['required', 'string', 'max:100']),
                    self::number('sort_order', 'Order', ['required', 'integer', 'min:0'], default: 0),
                ],
            ],
            'fertilizer-categories' => [
                'area' => 'fertilizers',
                'title' => 'Fertilizer categories',
                'description' => 'The kinds of fertilizer (straight, compound, other). Picked when a fertilizer is added and shown in the API.',
                'table' => 'fertilizer_categories',
                'order' => ['sort_order', 'value'],
                'fields' => [
                    self::text('value', 'Value', ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_]+$/'], key: true),
                    self::text('label', 'Label', ['required', 'string', 'max:100']),
                    self::number('sort_order', 'Order', ['required', 'integer', 'min:0'], default: 0),
                ],
            ],
            'fertilizer-types' => [
                'area' => 'fertilizers',
                'title' => 'Fertilizer types',
                'description' => 'Each fertilizer once: its code, catalogue label, name and usual bag weight. The code is worked out from the name when a fertilizer is added and cannot be changed.',
                'table' => 'fertilizer_types',
                'order' => ['name'],
                'timestamps' => true,
                'create_url' => '/admin/fertilizer-types/create',
                'fields' => [
                    self::text('type', 'Code', ['required', 'string'], key: true),
                    self::text('name', 'Name', ['required', 'string', 'max:255']),
                    self::text('label', 'Catalogue label', ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9_+]+$/'], help: 'How the catalogue and the engine name it, e.g. NPK201010'),
                    self::number('weight', 'Bag weight (kg)', ['required', 'integer', 'min:1', 'max:1000']),
                    self::select('category', 'Category', ['required', 'string'], from: ['table' => 'fertilizer_categories', 'value' => 'value', 'label' => 'label']),
                ],
            ],
            'maize-units' => [
                'area' => 'units',
                'title' => 'Maize units',
                'description' => 'The units maize grain is sold in, offered to the farmer as the unit of sale (the standard bag in Nigeria is 100 kg). The maize price list is per tonne; a client shows the price for the chosen unit as average x weight / 1000. Local measures (mudu, derica) have no fixed weight and are not listed.',
                'table' => 'maize_units',
                'order' => ['sort_order', 'unit_weight'],
                'timestamps' => true,
                'fields' => [
                    self::text('label', 'Label', ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/'], key: true, help: 'The name of the unit of sale in the app, e.g. HUNDRED_KG'),
                    self::number('unit_weight', 'Weight (kg)', ['required', 'numeric', 'gt:0', 'max:100000'], step: '0.01'),
                    self::text('description', 'Description', ['nullable', 'string', 'max:500']),
                    self::number('sort_order', 'Order', ['required', 'integer', 'min:0'], default: 0),
                    self::number('is_default', 'Preselected (1 yes, 0 no)', ['required', 'integer', 'in:0,1'], default: 0, help: 'One unit should be preselected'),
                    self::number('is_active', 'Offered (1 yes, 0 no)', ['required', 'integer', 'in:0,1'], default: 1),
                ],
            ],
            'price-choice-steps' => [
                'title' => 'Price choice steps',
                'description' => 'The steps around the reference price that the app offers, e.g. -30, -10, +10, +30 %. For range lists (cassava, maize, sweet potato) each band is the step plus or minus half the band width.',
                'warning' => 'Changing a step changes the price choices the app shows in every country at once.',
                'table' => 'price_choice_steps',
                'previews' => ['fertilizer', 'cassava', 'maize', 'potato', 'operations'],
                'order' => ['step_order'],
                'fields' => [
                    self::number('step_order', 'Order', ['required', 'integer', 'min:1', 'max:20'], key: true, help: '1 is the lowest choice'),
                    self::number('change_pct', 'Change from the reference (%)', ['required', 'numeric', 'between:-99,500'], step: '0.01', help: '-30 is 30 % below'),
                    self::number('band_half_pct', 'Half band width (%)', ['required', 'numeric', 'between:0,100'], step: '0.01', help: 'Used by range lists; 10 makes bands 20 % wide'),
                ],
            ],
            'price-choice-rounding' => [
                'title' => 'Price choice rounding',
                'description' => 'What the offered prices are rounded to, per currency (100 for NGN, TZS, RWF, BIF; 5 for GHS). A currency without a row is not rounded.',
                'warning' => 'Changing a step changes the price choices the app shows for that currency at once.',
                'table' => 'price_choice_rounding',
                'previews' => ['fertilizer', 'cassava', 'maize', 'potato', 'operations'],
                'order' => ['currency'],
                'fields' => [
                    self::select('currency', 'Currency', ['required', 'string', 'size:3'], key: true, from: ['table' => 'currencies', 'value' => 'currency_code', 'label' => 'currency_name']),
                    self::number('step', 'Round to the nearest', ['required', 'integer', 'min:1']),
                ],
            ],
            'price-choice-units' => [
                'title' => 'Price choice units',
                'description' => 'Items whose offered prices are not shown as the catalogue holds them: a quantity to multiply by (maize grain is per kg, shown per 50 kg) and a rounding of their own (a price per cob is too small for the currency step).',
                'warning' => 'Changing a row changes the price choices for that item in every country at once.',
                'table' => 'price_choice_units',
                'previews' => ['fertilizer', 'cassava', 'maize', 'potato', 'operations'],
                'order' => ['item'],
                'fields' => [
                    self::select('item', 'Item', ['required', 'string'], key: true, from: ['table' => 'price_items', 'value' => 'item']),
                    self::number('quantity', 'Quantity', ['required', 'integer', 'min:1'], help: 'The catalogue price is multiplied by this'),
                    self::number('rounding', 'Rounding', ['nullable', 'integer', 'min:1'], help: 'Leave empty to use the currency rounding'),
                ],
            ],
            'price-choice-multiples' => [
                'title' => 'Investment multiples',
                'description' => 'The investment amounts the app offers are these multiples of the country\'s reference amount per acre (1, 2, 3, 4, 6).',
                'warning' => 'Changing a multiple changes the investment amounts in every country at once.',
                'table' => 'price_choice_multiples',
                'previews' => ['investment'],
                'order' => ['step_order'],
                'fields' => [
                    self::number('step_order', 'Order', ['required', 'integer', 'min:1', 'max:20'], key: true),
                    self::number('multiple', 'Multiple of the reference amount', ['required', 'numeric', 'gt:0', 'max:1000'], step: '0.01'),
                ],
            ],
            'price-choice-operations' => [
                'title' => 'Operation cost items',
                'description' => 'The catalogue labour price each operation cost list is built around, e.g. tillage by tractor follows tractor_ploughing. An operation without a row has no cost list.',
                'warning' => 'Changing a row changes the operation costs for that operation in every country at once.',
                'table' => 'price_choice_operations',
                'previews' => ['operations'],
                'order' => ['operation_name', 'operation_type'],
                'fields' => [
                    self::text('operation_name', 'Operation', ['required', 'string', 'max:100'], key: true, help: 'e.g. tillage, ridging, weeding'),
                    self::text('operation_type', 'Type', ['required', 'string', 'max:100'], key: true, help: 'e.g. manual, tractor, herbicide'),
                    self::select('item', 'Catalogue item', ['required', 'string'], from: ['table' => 'price_items', 'value' => 'item']),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $slug): ?array
    {
        $all = self::all();

        return isset($all[$slug]) ? ['slug' => $slug] + $all[$slug] : null;
    }

    /**
     * @param list<mixed> $rules
     * @return Field
     */
    private static function text(string $name, string $label, array $rules, bool $key = false, ?string $help = null): array
    {
        return ['name' => $name, 'label' => $label, 'type' => 'text', 'key' => $key, 'rules' => $rules, 'help' => $help];
    }

    /**
     * @param list<mixed> $rules
     * @return Field
     */
    private static function number(string $name, string $label, array $rules, bool $key = false, ?string $help = null, ?string $step = null, ?int $default = null): array
    {
        return ['name' => $name, 'label' => $label, 'type' => 'number', 'key' => $key, 'rules' => $rules, 'help' => $help, 'step' => $step, 'default' => $default];
    }

    /**
     * @param list<mixed> $rules
     * @param array{table: string, value: string, label?: string} $from
     * @return Field
     */
    private static function select(string $name, string $label, array $rules, array $from, bool $key = false, ?string $help = null): array
    {
        return ['name' => $name, 'label' => $label, 'type' => 'select', 'key' => $key, 'rules' => $rules, 'from' => $from, 'help' => $help];
    }
}
