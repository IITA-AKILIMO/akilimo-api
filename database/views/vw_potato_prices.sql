-- The sweet potato price options per country, from vw_price_bands (items sweetPotato_tuber and
-- sweetPotato_flour), plus the exact-price marker row.
--
-- This file is the definition of the view. Change it here and run `php artisan views:rebuild`
-- (it also runs by itself after every migration), never in a migration.
CREATE OR REPLACE VIEW `vw_potato_prices` AS
select `b`.`id` AS `id`,`b`.`country` AS `country`,`b`.`min_local_price` AS `min_local_price`,`b`.`max_local_price` AS `max_local_price`,0 AS `min_usd`,0 AS `max_usd`,`b`.`is_first` AS `min_price`,`b`.`is_last` AS `max_price`,1 AS `price_active`,1 AS `sort_order`,`b`.`updated_at` AS `created_at`,`b`.`updated_at` AS `updated_at`
from `vw_price_bands` `b`
where `b`.`item` = 'sweetPotato_tuber';
