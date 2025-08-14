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


-- -----------------------------------------------------------------------------
    -- Creating a table `docsgo-meeting-notes`
    -- ------------------------------------------------------------------
    IF not exists(SELECT * FROM information_schema.tables WHERE table_schema = 'docsgo' AND table_name = 'docsgo-meeting-notes' LIMIT 1) THEN

        CREATE TABLE `docsgo-meeting-notes` (
            `meeting-id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(64) NOT NULL,
            `notes` text NOT NULL,
            `entry-date` date NOT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            PRIMARY KEY (`meeting-id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;    
    END IF;

end;;
delimiter ';'
call schema_change();
drop procedure if exists schema_change;