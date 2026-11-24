-- The range-list rule written once: every active, national, effective non-bag catalogue price with its band
-- per step of price_choice_steps, the unit conversion of price_choice_units and the rounding of
-- price_choice_rounding. The crop lists below read it.
--
-- This file is the definition of the view. Change it here and run `php artisan views:rebuild`
-- (it also runs by itself after every migration), never in a migration.
CREATE OR REPLACE VIEW `vw_price_bands` AS
select `x`.`id` * 10 + `x`.`step_order` AS `id`,`x`.`item` AS `item`,`x`.`country` AS `country`,`x`.`step_order` AS `step_order`,`x`.`lo` AS `min_local_price`,`x`.`hi` AS `max_local_price`,`x`.`step_order` = (select min(`price_choice_steps`.`step_order`)
    from `price_choice_steps`) AS `is_first`,`x`.`step_order` = (select max(`price_choice_steps`.`step_order`)
    from `price_choice_steps`) AS `is_last`,`x`.`updated_at` AS `updated_at`
from (select `d`.`id` AS `id`,`d`.`item` AS `item`,`d`.`country` AS `country`,`d`.`updated_at` AS `updated_at`,`s`.`step_order` AS `step_order`,cast(round(case when `c`.`default_price_basis` = 'subsidised' and `d`.`subsidised_price` is not null then `d`.`subsidised_price` else `d`.`price` end * coalesce(`u`.`quantity`,1) * (1 + (`s`.`change_pct` - `s`.`band_half_pct`) / 100) / coalesce(`u`.`rounding`,`r`.`step`,1),0) * coalesce(`u`.`rounding`,`r`.`step`,1) as decimal(10,2)) AS `lo`,cast(round(case when `c`.`default_price_basis` = 'subsidised' and `d`.`subsidised_price` is not null then `d`.`subsidised_price` else `d`.`price` end * coalesce(`u`.`quantity`,1) * (1 + (`s`.`change_pct` + `s`.`band_half_pct`) / 100) / coalesce(`u`.`rounding`,`r`.`step`,1),0) * coalesce(`u`.`rounding`,`r`.`step`,1) as decimal(10,2)) AS `hi`
    from ((((`price_catalogue` `d`
                    join `countries` `c` on(`c`.`code` = `d`.`country`))
                join `price_choice_steps` `s`)
            left join `price_choice_rounding` `r` on(`r`.`currency` = `d`.`currency`))
        left join `price_choice_units` `u` on(`u`.`item` = `d`.`item`))
    where `d`.`unit` <> 'per_bag' and `d`.`status` = 'active' and `d`.`region` is null and (`d`.`effective_from` is null or `d`.`effective_from` <= curdate())) `x`
where `x`.`lo` > 0 and `x`.`hi` > `x`.`lo`
union all select 1000000 + `d`.`id` * 10 AS `1000000 + d.id * 10`,`d`.`item` AS `item`,`d`.`country` AS `country`,0 AS `0`,-1 AS `-1`,-1 AS `-1`,0 AS `0`,0 AS `0`,`d`.`updated_at` AS `updated_at`
from `price_catalogue` `d`
where `d`.`unit` <> 'per_bag' and `d`.`status` = 'active' and `d`.`region` is null and (`d`.`effective_from` is null or `d`.`effective_from` <= curdate());
