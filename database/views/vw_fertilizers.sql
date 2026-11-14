-- One row per fertilizer sold in a country: the fertilizer (fertilizer_types) joined to where it is sold and
-- how it is used (country_fertilizers). The key is the type code, an underscore and the country. Read-only.
--
-- This file is the definition of the view. Change it here and run `php artisan views:rebuild`
-- (it also runs by itself after every migration), never in a migration.
CREATE OR REPLACE VIEW `vw_fertilizers` AS
select `cf`.`id` AS `id`,`t`.`label` AS `fertilizer_label`,`t`.`name` AS `name`,`t`.`type` AS `type`,`t`.`category` AS `category`,concat(`t`.`type`,'_',`cf`.`country`) AS `fertilizer_key`,`t`.`weight` AS `weight`,`cf`.`country` AS `country`,`cf`.`sort_order` AS `sort_order`,`cf`.`use_case` AS `use_case`,`cf`.`cis` AS `cis`,`cf`.`cim` AS `cim`,`cf`.`available` AS `available`,`cf`.`created_at` AS `created_at`,`cf`.`updated_at` AS `updated_at`
from (`country_fertilizers` `cf`
    join `fertilizer_types` `t` on(`t`.`type` = `cf`.`fertilizer_type`));
