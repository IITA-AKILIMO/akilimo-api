# Database views

The lists the app reads (the fertilizers, the price options, the investment amounts, the operation costs) are
database views over the price catalogue and the rule tables, so they can never drift from them. A view is read-only
and computed, so its name says so: **every view starts with `vw_`**.

| View | Model | What it is |
|---|---|---|
| `vw_fertilizers` | `VwFertilizer` | A fertilizer sold in a country (`fertilizer_types` joined to `country_fertilizers`) |
| `vw_fertilizer_prices` | `VwFertilizerPrice` | The price options for each fertilizer that has an active price, plus its two marker rows |
| `vw_price_bands` | `VwPriceBand` | The range-list rule, written once, for every non-bag price |
| `vw_cassava_prices`, `vw_potato_prices`, `vw_maize_prices` | `VwCassavaPrice`, `VwPotatoPrice`, `VwMaizePrice` | The crop price options, from `vw_price_bands` |
| `vw_investment_amount` | `VwInvestmentAmount` | The investment amounts per country |
| `vw_operation_costs` | `VwOperationCost` | The labour and machinery cost bands |

Each has a model named for it (`App\Models\VwFertilizerPrice`, with its generated base in `App\Models\Base`). They used to be called `fertilizers`, `fertilizer_prices`, `cassava_prices`, `potato_prices`, `maize_prices`,
`investment_amount`, `operation_costs` and `price_bands` (renamed by migration `000021`). The columns are the same,
so the API responses did not change.

## The files are the definition

Each view is defined by one file in `database/views/`, named for the view (`vw_fertilizers.sql`), holding a
`CREATE OR REPLACE VIEW`. **Change a view by editing its file, never in a migration.**

```bash
php artisan views:rebuild            # create or replace every view from its file
php artisan views:rebuild --check    # only say which views are missing, broken, without a file, or not prefixed
```

`views:rebuild` also runs by itself at the end of every migration run (`migrate`, `migrate:rollback`,
`migrate:fresh`), so a view follows a table that gained or lost a column without anyone remembering to.

## When a table changes

1. **A column a view does not read is added or dropped:** nothing to do; the rebuild after the migration makes the
   views again.
2. **A column a view reads is renamed or dropped:** the rebuild after the migration cannot make that view, so it
   logs a warning and the view that exists is left as it was (nothing is dropped first, because each view is made with
   `CREATE OR REPLACE`). `php artisan views:rebuild --check` says which views stopped working. Edit the file of each
   one to use the new column, then run `php artisan views:rebuild`.
3. **A column should show in a list:** add it to the view's file and rebuild; then add it to the model and the
   resource.

A view that reads another view (`vw_cassava_prices` reads `vw_price_bands`) is handled: the rebuild tries them until all
exist, in whatever order the files sort.

## A new view

Create `database/views/vw_<name>.sql` with `CREATE OR REPLACE VIEW \`vw_<name>\` AS ...`, then run
`php artisan views:rebuild`. `tests/Feature/ViewNamingTest.php` fails for a view without the prefix, and
`ViewRebuildTest` checks that every view has a file and every file has a view.

*IITA Internal Use*
