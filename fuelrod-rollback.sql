--  *********************************************************************
--  SQL to roll back currently unexecuted changes
--  *********************************************************************
--  Change Log: classpath:liquibase/master.xml
--  Ran at: 2/11/21, 1:05 PM
--  Against: root@jdbc:mariadb://127.0.0.1:3306/akilimo
--  Liquibase version: 3.8.8
--  *********************************************************************

--  Lock Database
UPDATE akilimo.DATABASECHANGELOGLOCK SET `LOCKED` = 1, LOCKEDBY = 'CODE-EATER (172.27.80.1)', LOCKGRANTED = '2021-02-11 13:05:58.38' WHERE ID = 1 AND `LOCKED` = 0;

--  Rolling Back ChangeSet: /liquibase/changelog/20210211124030_add_min_and_max_price_columns_to_maize_prices.xml::20210211124030::smbar
ALTER TABLE akilimo.maize_prices ALTER max_price DROP DEFAULT;

ALTER TABLE akilimo.maize_prices ALTER min_price DROP DEFAULT;

ALTER TABLE akilimo.maize_prices DROP max_price, DROP min_price;

DELETE FROM akilimo.DATABASECHANGELOG WHERE ID = '20210211124030' AND AUTHOR = 'smbar' AND FILENAME = '/liquibase/changelog/20210211124030_add_min_and_max_price_columns_to_maize_prices.xml';

--  Release Database Lock
UPDATE akilimo.DATABASECHANGELOGLOCK SET `LOCKED` = 0, LOCKEDBY = NULL, LOCKGRANTED = NULL WHERE ID = 1;

