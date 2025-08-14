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
    -- Adding new column project_id in docsgo-requirements table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-requirements' and column_name = 'project_id') then
        ALTER TABLE `docsgo-requirements` ADD COLUMN `project-id` int(11) NOT NULL DEFAULT 33;
    end if;


--
-- Constraints for table `docsgo-requirements`
--
ALTER TABLE `docsgo-requirements`
 ADD CONSTRAINT `fk_requirements_project1` FOREIGN KEY (`project-id`) REFERENCES `docsgo-projects` (`project-id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
    

-- ----------------------------------------------------------------------------- --
    -- Adding new column project_id in docsgo-traceability table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-traceability' and column_name = 'project-id') then
        ALTER TABLE `docsgo-traceability` ADD COLUMN `project-id` int(11) NOT NULL DEFAULT 33;
    end if;


--
-- Constraints for table `docsgo-traceability`
--
ALTER TABLE `docsgo-traceability`
 ADD CONSTRAINT `fk_traceability_project1` FOREIGN KEY (`project-id`) REFERENCES `docsgo-projects` (`project-id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- -----------------------------------------------------------------------------
    -- Creating a table `docsgo-timesheet`
    -- ------------------------------------------------------------------
    IF not exists(SELECT * FROM information_schema.tables WHERE table_schema = 'docsgo' AND table_name = 'docsgo-timesheet' LIMIT 1) THEN

        CREATE TABLE `docsgo-timesheet` (
           `timesheet-id` int(11) NOT NULL AUTO_INCREMENT,
	       `project-id` int(11) NOT NULL,
           `user-id` int(11) NOT NULL,
           `type` varchar(64) NOT NULL,
            `log` longtext NOT NULL,
            `dependencies` varchar(100) DEFAULT NULL,
            `duration` time NOT NULL,
            `entry-date` date NOT NULL,
            `status` enum('Open','Close') NOT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`timesheet-id`),
                KEY `idx_timesheet_project1` (`project-id`),
                CONSTRAINT `fk_timesheet_project1` FOREIGN KEY (`project-id`) REFERENCES `docsgo-projects` (`project-id`) ON DELETE  NO ACTION ON UPDATE NO ACTION,
                KEY `fk_timesheet_user1` (`user-id`),
                CONSTRAINT `fk_timesheet_user1` FOREIGN KEY (`user-id`) REFERENCES `docsgo-team-master` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
   END IF;

-- -----------------------------------------------------------------------------
    -- Creating a table `docsgo-products`
    -- ------------------------------------------------------------------
    IF not exists(SELECT * FROM information_schema.tables WHERE table_schema = 'docsgo' AND table_name = 'docsgo-products' LIMIT 1) THEN

        CREATE TABLE `docsgo-products` (
            `product-id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(150) NOT NULL,
            `description` longtext DEFAULT NULL,
            `display-name` varchar(100) NOT NULL,
            `status` enum('Active','InActive','Completed') NOT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
             PRIMARY KEY (`product-id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;
   END IF;

-- -----------------------------------------------------------------------------
    -- Creating a table `docsgo-products-projects-mapping`
    -- ------------------------------------------------------------------
    IF not exists(SELECT * FROM information_schema.tables WHERE table_schema = 'docsgo' AND table_name = 'docsgo-products-projects-mapping' LIMIT 1) THEN

        CREATE TABLE `docsgo-products-projects-mapping` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product-id` int(11) NULL,
            `project-id` int(11)  NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_products_projects_project1` (`project-id`),
            CONSTRAINT `fk_products_projects_project1` FOREIGN KEY (`project-id`) REFERENCES `docsgo-projects` (`project-id`) ON DELETE  NO ACTION ON UPDATE NO ACTION,
            KEY `idx_products_projects_product1` (`product-id`),
            CONSTRAINT `fk_products_projects_product1` FOREIGN KEY (`product-id`) REFERENCES `docsgo-products` (`product-id`) ON DELETE  NO ACTION ON UPDATE NO ACTION
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;
    END IF;

-- -----------------------------------------------------------------------------
    -- Inserting a record in  `docsgo-settings`
    -- ------------------------------------------------------------------
INSERT INTO `docsgo-settings` (`id`, `type`, `identifier`, `options`) VALUES
(14, 'dropdown', 'timesheetStatus', '[{\"key\":0,\"value\":\"Open\"},{\"key\":1,\"value\":\"Close\"}]'),
(15, 'dropdown', 'taskCategory', '[{\"key\":0,\"value\":\"Task\",\"isRoot\":false},{\"key\":1,\"value\":\"New Feature\",\"isRoot\":false},{\"key\":2,\"value\":\"Improvement\",\"isRoot\":true},{\"key\":3,\"value\":\"Bug\",\"isRoot\":true}]'),
(16, 'dropdown', 'taskType', '[{\"key\":0,\"value\":\"Todo\",\"isRoot\":false},{\"key\":1,\"value\":\"In Progress\",\"isRoot\":false},{\"key\":2,\"value\":\"Under Verification\",\"isRoot\":true},{\"key\":3,\"value\":\"Complete\",\"isRoot\":true}]');


end;;
delimiter ';'
call schema_change();
drop procedure if exists schema_change;
