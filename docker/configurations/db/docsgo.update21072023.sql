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
    -- Drop  column is_certified in docsgo-user-courses table
    -- ----------------------------------------------------------------------------- --
    if exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-user-courses' and column_name = 'is_certified') then
        ALTER TABLE `docsgo-user-courses` DROP COLUMN `is_certified`;
    end if;



-- ----------------------------------------------------------------------------- --
    -- Adding new column is_certified in docsgo-courses table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-courses' and column_name = 'is_certified') then
        ALTER TABLE `docsgo-courses` ADD COLUMN `is_certified` tinyint(1) NOT NULL;
    end if;


-- ----------------------------------------------------------------------------- --
    -- Modifying column completed_date in docsgo-user-courses table
    -- ----------------------------------------------------------------------------- --
    if exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-user-courses' and column_name = 'completed_date') then
        ALTER TABLE `docsgo-user-courses` MODIFY COLUMN `completed_date` date NULL;
    end if;


end;;
delimiter ';'
call schema_change();
drop procedure if exists schema_change;