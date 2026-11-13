-- The investment amounts per country: the catalogue's investment_amount price multiplied by
-- price_choice_multiples, plus the 'Exact amount' row (0).
--
-- This file is the definition of the view. Change it here and run `php artisan views:rebuild`
-- (it also runs by itself after every migration), never in a migration.
CREATE OR REPLACE VIEW `vw_investment_amount` AS
select `d`.`id` * 10 + `m`.`step_order` AS `id`,`d`.`country` AS `country`,cast(round(`d`.`price` * `m`.`multiple`,2) as decimal(10,2)) AS `investment_amount`,'acre' AS `area_unit`,1 AS `price_active`,`m`.`step_order` AS `sort_order`,`d`.`updated_at` AS `created_at`,`d`.`updated_at` AS `updated_at`
from (`price_catalogue` `d`
    join `price_choice_multiples` `m`)
where `d`.`item` = 'investment_amount' and `d`.`status` = 'active' and `d`.`region` is null and (`d`.`effective_from` is null or `d`.`effective_from` <= curdate())
union all select 1000000 + `d`.`id` * 10 AS `1000000 + d.id * 10`,`d`.`country` AS `country`,0 AS `0`,'acre' AS `acre`,1 AS `1`,0 AS `0`,`d`.`updated_at` AS `updated_at`,`d`.`updated_at` AS `updated_at`
from `price_catalogue` `d`
where `d`.`item` = 'investment_amount' and `d`.`status` = 'active' and `d`.`region` is null and (`d`.`effective_from` is null or `d`.`effective_from` <= curdate());
