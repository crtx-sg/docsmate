-- Update.sql
-- 
-- This file will be required to incorporate any schema or
--   model level changes on top of the base line (docsgo.sql)
-- ------------------------------------------------------
-- Please add any new code below.

USE `docsgo`;

drop procedure if exists schema_change;
delimiter ';;'
create procedure schema_change() begin



-- ----------------------------------------------------------------------------- --
    -- Adding new column product-id in docsgo-test-cases table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-test-cases' and column_name = 'product-id') then
    ALTER TABLE `docsgo-test-cases` ADD COLUMN `product-id` int(11) NOT NULL DEFAULT 1;
    end if;


-- ----------------------------------------------------------------------------
-- Constraints for table `docsgo-test-cases`
-- ----------------------------------------------------------------------------
    ALTER TABLE `docsgo-test-cases`
        ADD CONSTRAINT `fk_testcases_product1` FOREIGN KEY (`product-id`) REFERENCES `docsgo-products` (`product-id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- -----------------------------------------------------------------------------
-- Deleting old record with id=12 in  `docsgo-settings`
-- ------------------------------------------------------------------
delete from `docsgo-settings` where `id`=12;


-- -----------------------------------------------------------------------------
-- Inserting a record in  `docsgo-settings`
-- ------------------------------------------------------------------
INSERT INTO `docsgo-settings` (`id`, `type`, `identifier`, `options`) VALUES (12, 'properties', 'documentProperties', '[{\"key\":\"docTitle\",\"value\":\"\"},{\"key\":\"docIcon\",\"value\":\"\"},{\"key\":\"docConfidential\",\"value\":\"\"},{\"key\":\"docPageNums\",\"value\":\"\"}]');


-- -----------------------------------------------------------------------------
-- Deleting old record with id=12 in  `docsgo-settings`
-- ------------------------------------------------------------------
delete from `docsgo-settings` where `id`=17;


-- -----------------------------------------------------------------------------
-- Inserting a record in  `docsgo-settings`
-- ------------------------------------------------------------------
INSERT INTO `docsgo-settings` (`id`, `type`, `identifier`, `options`) VALUES (17, 'dropdown', 'userCourseStatus', '[{\"key\":0,\"value\":\"Not-Started\"},{\"key\":1,\"value\":\"InProgress\"},{\"key\":2,\"value\":\"Review\"},{\"key\":3,\"value\":\"Completed\"}]');


-- -----------------------------------------------------------------------------
-- Updating a record in  `docsgo-risks` to "Open Anamoly"
-- ------------------------------------------------------------------
    update `docsgo-risks` set `risk_type`="Open Anamoly" where `risk_type`="Open-Issue";



-- -----------------------------------------------------------------------------
-- Updating a record in  `docsgo-risks` to "Software Of Unknown Provenance"
-- ------------------------------------------------------------------
    update `docsgo-risks` set `risk_type`="Software Of Unknown Provenance" where `risk_type`="SOUP";


-- ---------------------------------------------------------------------------- --
    -- Adding new column vulnerability in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'vulnerability') then
        ALTER TABLE `docsgo-risks` ADD `vulnerability` varchar(255);
     end if;


end;;
delimiter ';'
call schema_change();
drop procedure if exists schema_change;