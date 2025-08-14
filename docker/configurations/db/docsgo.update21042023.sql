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
    -- Adding new column product-id in docsgo-requirements table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-requirements' and column_name = 'product_id') then
        ALTER TABLE `docsgo-requirements` ADD COLUMN `product-id` int(11) NOT NULL DEFAULT 1;
    end if;


--
-- Constraints for table `docsgo-requirements`
--
ALTER TABLE `docsgo-requirements`
 ADD CONSTRAINT `fk_requirements_product1` FOREIGN KEY (`product-id`) REFERENCES `docsgo-products` (`product-id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
    

-- ----------------------------------------------------------------------------- --
    -- Adding new column product-id in docsgo-traceability table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-traceability' and column_name = 'product-id') then
        ALTER TABLE `docsgo-traceability` ADD COLUMN `product-id` int(11) NOT NULL DEFAULT 1;
    end if;


--
-- Constraints for table `docsgo-traceability`
--
ALTER TABLE `docsgo-traceability`
 ADD CONSTRAINT `fk_traceability_product1` FOREIGN KEY (`product-id`) REFERENCES `docsgo-products` (`product-id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- ----------------------------------------------------------------------------- --
    -- Update datatype of duration column in docsgo-timesheet table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-timesheet' and column_name = 'duration') then
        ALTER TABLE `docsgo-timesheet` MODIFY `duration` int(11);
    end if;

-- ----------------------------------------------------------------------------- --
    -- Drop constraint in docsgo-requirements table
    -- ----------------------------------------------------------------------------- --
    ALTER TABLE `docsgo-requirements` DROP CONSTRAINT fk_requirements_project1;

-- ----------------------------------------------------------------------------- --
    -- Drop constraint in docsgo-traceability table
    -- ----------------------------------------------------------------------------- --
    ALTER TABLE `docsgo-traceability` DROP CONSTRAINT fk_traceability_project1;

-- ----------------------------------------------------------------------------- --
    -- Drop constraint default of project-id column in docsgo-requirements table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-requirements' and column_name = 'project-id') then
        ALTER TABLE `docsgo-requirements` ALTER COLUMN `project-id` DROP DEFAULT;
    end if;

-- --------------------------------------------------------------------------- --
    -- Update project-id column as Null in docsgo-requirements table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-requirements' and column_name = 'project-id') then
        ALTER TABLE `docsgo-requirements` MODIFY `project-id` int(11) NULL;
    end if;


-- ----------------------------------------------------------------------------- --
    -- Drop constraint default of project-id column in docsgo-traceability table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-traceability' and column_name = 'project-id') then
        ALTER TABLE `docsgo-traceability` ALTER COLUMN `project-id` DROP DEFAULT;
    end if;

-- ----------------------------------------------------------------------------- --
    -- Update project-id column as Null in docsgo-traceability table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-traceability' and column_name = 'project-id') then
        ALTER TABLE `docsgo-traceability` MODIFY `project-id` int(11) NULL;
    end if;


end;;
delimiter ';'
call schema_change();
drop procedure if exists schema_change;
