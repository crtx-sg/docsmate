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
    -- Adding new column software_name in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'software_name') then
        ALTER TABLE `docsgo-risks` ADD `software_name` varchar(255);
    end if;

-- ---------------------------------------------------------------------------- --
    -- Adding new column type in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'type') then
        ALTER TABLE `docsgo-risks` ADD `type` varchar(255);
    end if;

-- ---------------------------------------------------------------------------- --
    -- Adding new column version in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'version') then
        ALTER TABLE `docsgo-risks` ADD `version` varchar(255);
    end if;

-- ---------------------------------------------------------------------------- --
    -- Adding new column latest_version in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'latest_version') then
        ALTER TABLE `docsgo-risks` ADD `latest_version` varchar(255);
    end if;

-- ---------------------------------------------------------------------------- --
    -- Adding new column initial_risk_priority_number in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'initial_risk_priority_number') then
        ALTER TABLE `docsgo-risks` ADD `initial_risk_priority_number` float;
    end if;

-- ---------------------------------------------------------------------------- --
    -- Adding new column residual_risk_priority_number in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'residual_risk_priority_number') then
        ALTER TABLE `docsgo-risks` ADD `residual_risk_priority_number` float;
    end if;

-- ---------------------------------------------------------------------------- --
    -- Adding new column risk_control_measures in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'risk_control_measures') then
        ALTER TABLE `docsgo-risks` ADD `risk_control_measures` longtext;
    end if;


-- ----------------------------------------------------------------------------- --
    -- Adding new column log-date in docsgo-timesheet table
    -- ----------------------------------------------------------------------------- --
    if not exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-timesheet' and column_name = 'log-date') then
        ALTER TABLE `docsgo-timesheet` ADD `log-date` date;
    end if;


-- ----------------------------------------------------------------------------- --
    -- Rename column description to risk_description in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'description') then
        ALTER TABLE `docsgo-risks` RENAME COLUMN `description` TO `risk_description`;
    end if;


    -- ----------------------------------------------------------------------------- --
    -- Rename column harm to initial_risk_evaluation in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'harm') then
        ALTER TABLE `docsgo-risks` RENAME COLUMN `harm` TO `initial_risk_evaluation`;
    end if;


    -- ----------------------------------------------------------------------------- --
    -- Rename column hazard-analysis to risk_analysis in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'hazard-analysis') then
        ALTER TABLE `docsgo-risks` RENAME COLUMN `hazard-analysis` TO `risk_analysis`;
    end if;


    -- ----------------------------------------------------------------------------- --
    -- Rename column cascade_effect to residual_risk_evaluation in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'cascade_effect') then
        ALTER TABLE `docsgo-risks` RENAME COLUMN `cascade_effect` TO `residual_risk_evaluation`;
    end if;


    -- ----------------------------------------------------------------------------- --
    -- Rename column failure_mode to benefit_risk_analysis in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'failure_mode') then
        ALTER TABLE `docsgo-risks` RENAME COLUMN `failure_mode` TO `benefit_risk_analysis`;
    end if;


    -- ----------------------------------------------------------------------------- --
    -- Rename column baseScore_severity to CVSS_3_1_base_risk_assessment in docsgo-risks table
    -- ----------------------------------------------------------------------------- --
    if exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-risks' and column_name = 'baseScore_severity') then
        ALTER TABLE `docsgo-risks` RENAME COLUMN `baseScore_severity` TO `CVSS_3_1_base_risk_assessment`;
    end if;
    
    
    -- -----------------------------------------------------------------------------
    -- Creating a table `docsgo-risk-categories`
    -- ------------------------------------------------------------------
    IF not exists(SELECT * FROM information_schema.tables WHERE table_schema = 'docsgo' AND table_name = 'docsgo-risk-categories' LIMIT 1) THEN
        CREATE TABLE `docsgo-risk-categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(150) NOT NULL,
            `risk-methodology` varchar(120) NOT NULL,
            `status` enum('Active','InActive') NOT NULL,
            `created_at` datetime DEFAULT current_timestamp(),
            `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   
    END IF;

-- ----------------------------------------------------------------------------- --
    -- Change datatype of column reviewer-id int to varchar in docsgo-documents table
    -- ----------------------------------------------------------------------------- --
    if exists(select * from information_schema.columns where table_schema = 'docsgo' and table_name = 'docsgo-documents' and column_name = 'reviewer-id') then
        ALTER TABLE `docsgo-documents` CHANGE `reviewer-id` `reviewer-id` varchar(100);
    end if;


-- -----------------------------------------------------------------------------
    -- Inserting a record in  `docsgo-settings`
    -- ------------------------------------------------------------------
INSERT INTO `docsgo-settings` (`id`, `type`, `identifier`, `options`) VALUES (18, 'dropdown', 'riskMethodologyCategory', '[{\"key\":0,\"value\":\"Risk Acceptability Matrix\",\"isRoot\":true},{\"key\":1,\"value\":\"CVSS 3.1\",\"isRoot\":true}]');


-- -----------------------------------------------------------------------------
    -- Deleting old record with id=13 in  `docsgo-settings`
    -- ------------------------------------------------------------------
delete from `docsgo-settings` where `id`=13;


-- -----------------------------------------------------------------------------
    -- Inserting a record in  `docsgo-settings`
    -- ------------------------------------------------------------------
INSERT INTO `docsgo-settings` (`id`, `type`, `identifier`, `options`) VALUES (13, 'dropdown', 'timeTrackerCategory', '[{\"key\":0,\"value\":\"Meeting\",\"isRoot\":true},{\"key\":1,\"value\":\"Development\",\"isRoot\":true},{\"key\":2,\"value\":\"Testing\",\"isRoot\":true},{\"key\":3,\"value\":\"Verification\",\"isRoot\":true},{\"key\":4,\"value\":\"Review\",\"isRoot\":true},{\"key\":5,\"value\":\"Documentation\",\"isRoot\":true},{\"key\":6,\"value\":\"Other\",\"isRoot\":true},{\"key\":7,\"value\":\"Research/Analysis\",\"isRoot\":false}]');


end;;
delimiter ';'
call schema_change();
drop procedure if exists schema_change;