-- The price options the app shows for each fertilizer that has an active per-bag price: one row per step of
-- price_choice_steps (rounded per price_choice_rounding), plus the two marker rows (I don't know = 0, Exact
-- price = -1). A fertilizer with no active price has no rows. Row ids are the catalogue id x 10 + the step;
-- markers use ids from 1,000,000.
--
-- This file is the definition of the view. Change it here and run `php artisan views:rebuild`
-- (it also runs by itself after every migration), never in a migration.
CREATE OR REPLACE VIEW `vw_fertilizer_prices` AS
select `x`.`id` * 10 + `x`.`step_order` AS `id`,`x`.`country` AS `country`,`x`.`fertilizer_key` AS `fertilizer_key`,`x`.`price` AS `min_price`,`x`.`price` AS `max_price`,`x`.`price` AS `price_per_bag`,1 AS `price_active`,`x`.`step_order` AS `sort_order`,'' AS `desc`,`x`.`updated_at` AS `created_at`,`x`.`updated_at` AS `updated_at`
from (select `d`.`id` AS `id`,`d`.`country` AS `country`,concat(`d`.`fertilizer_type`,'_',`d`.`country`) AS `fertilizer_key`,`d`.`updated_at` AS `updated_at`,`s`.`step_order` AS `step_order`,`s`.`change_pct` AS `change_pct`,cast(round(case when `c`.`default_price_basis` = 'subsidised' and `d`.`subsidised_price` is not null then `d`.`subsidised_price` else `d`.`price` end * (1 + `s`.`change_pct` / 100) / coalesce(`r`.`step`,1),0) * coalesce(`r`.`step`,1) as decimal(10,2)) AS `price`
    from (((`price_catalogue` `d`
                join `countries` `c` on(`c`.`code` = `d`.`country`))
            join `price_choice_steps` `s`)
        left join `price_choice_rounding` `r` on(`r`.`currency` = `d`.`currency`))
    where `d`.`unit` = 'per_bag' and `d`.`fertilizer_type` is not null and `d`.`status` = 'active' and `d`.`region` is null and (`d`.`effective_from` is null or `d`.`effective_from` <= curdate())) `x`
where `x`.`price` > 0
union all select 1000000 + `f`.`id` * 10 AS `1000000 + f.id * 10`,`f`.`country` AS `country`,`f`.`fertilizer_key` AS `fertilizer_key`,0 AS `0`,0 AS `0`,0 AS `0`,1 AS `1`,0 AS `0`,'I don''t know' AS `I don't know`,`f`.`updated_at` AS `updated_at`,`f`.`updated_at` AS `updated_at`
from `vw_fertilizers` `f`
where exists(select 1
    from `price_catalogue` `p`
    where concat(`p`.`fertilizer_type`,'_',`p`.`country`) = `f`.`fertilizer_key` and `p`.`unit` = 'per_bag' and `p`.`status` = 'active' and `p`.`region` is null and `p`.`price` > 0 and (`p`.`effective_from` is null or `p`.`effective_from` <= curdate()) limit 1)
union all select 1000000 + `f`.`id` * 10 + 5 AS `1000000 + f.id * 10 + 5`,`f`.`country` AS `country`,`f`.`fertilizer_key` AS `fertilizer_key`,-1 AS `-1`,-1 AS `-1`,-1 AS `-1`,1 AS `1`,5 AS `5`,'Exact price' AS `Exact price`,`f`.`updated_at` AS `updated_at`,`f`.`updated_at` AS `updated_at`
from `vw_fertilizers` `f`
where exists(select 1
    from `price_catalogue` `p`
    where concat(`p`.`fertilizer_type`,'_',`p`.`country`) = `f`.`fertilizer_key` and `p`.`unit` = 'per_bag' and `p`.`status` = 'active' and `p`.`region` is null and `p`.`price` > 0 and (`p`.`effective_from` is null or `p`.`effective_from` <= curdate()) limit 1);
