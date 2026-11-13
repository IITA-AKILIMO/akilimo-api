-- The labour and machinery cost bands per country and operation, from the catalogue and
-- price_choice_operations. Read-only.
--
-- This file is the definition of the view. Change it here and run `php artisan views:rebuild`
-- (it also runs by itself after every migration), never in a migration.
CREATE OR REPLACE VIEW `vw_operation_costs` AS
select `b`.`id` AS `id`,`o`.`operation_name` AS `operation_name`,`o`.`operation_type` AS `operation_type`,`b`.`country` AS `country_code`,`b`.`min_local_price` AS `min_cost`,`b`.`max_local_price` AS `max_cost`,1 AS `is_active`,`b`.`updated_at` AS `created_at`,`b`.`updated_at` AS `updated_at`
from (`vw_price_bands` `b`
    join `price_choice_operations` `o` on(`o`.`item` = `b`.`item`))
where `b`.`step_order` > 0;
