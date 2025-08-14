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
    -- Adding new column assessment in docsgo-courses table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-courses' and column_name = 'assessment') then
        ALTER TABLE `docsgo-courses` ADD COLUMN `assessment` longtext NOT NULL;
    end if;

end;;
delimiter ';'
call schema_change();
drop procedure if exists schema_change;