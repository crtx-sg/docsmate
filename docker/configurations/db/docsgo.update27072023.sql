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
    -- Adding new column approved-by in docsgo-reviews table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-reviews' and column_name = 'approved-by') then
        ALTER TABLE `docsgo-reviews` ADD COLUMN `approved-by` varchar(50);
    end if;


-- ----------------------------------------------------------------------------- --
    -- Adding new column approved-by in docsgo-reviews table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-reviews' and column_name = 'approved-at') then
        ALTER TABLE `docsgo-reviews` ADD COLUMN `approved-at` datetime;
    end if;


-- ----------------------------------------------------------------------------- --
    -- Adding new column approved-by in docsgo-documents table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-documents' and column_name = 'approved-by') then
        ALTER TABLE `docsgo-documents` ADD COLUMN `approved-by` varchar(50);
    end if;


-- ----------------------------------------------------------------------------- --
    -- Adding new column approved-at in docsgo-documents table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-documents' and column_name = 'approved-at') then
        ALTER TABLE `docsgo-documents` ADD COLUMN `approved-at` datetime;
    end if;


end;;
delimiter ';'
call schema_change();
drop procedure if exists schema_change;