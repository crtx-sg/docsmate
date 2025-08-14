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
    -- Dropping a table `docsgo-timesheet`
    -- ------------------------------------------------------------------
    IF exists(SELECT * FROM information_schema.tables WHERE table_schema = 'docsgo' AND table_name = 'docsgo-timesheet' LIMIT 1) THEN

        DROP TABLE `docsgo-timesheet`;

    END IF;

-- -----------------------------------------------------------------------------
    -- Creating a table `docsgo-timesheet`
    -- ------------------------------------------------------------------
    IF not exists(SELECT * FROM information_schema.tables WHERE table_schema = 'docsgo' AND table_name = 'docsgo-timesheet' LIMIT 1) THEN

        CREATE TABLE `docsgo-timesheet` (
            `timesheet-id` int(11) NOT NULL AUTO_INCREMENT,
            `project-id` int(11) NOT NULL,
            `user-id` int(11) NOT NULL,
            `type` varchar(64) NOT NULL,
            `log` json NOT NULL,
            `dependencies` varchar(100) DEFAULT NULL,
            `day-log-hours` int(11) NOT NULL,
            `total-logged-hours` int(11) DEFAULT 0,
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
    -- Creating a table `docsgo-courses`
    -- ------------------------------------------------------------------
    IF not exists(SELECT * FROM information_schema.tables WHERE table_schema = 'docsgo' AND table_name = 'docsgo-courses' LIMIT 1) THEN

    CREATE TABLE `docsgo-courses` (
        `course_id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(200) NOT NULL,
        `description` text DEFAULT NULL,
        `url` text NOT NULL,
        `k-points` int(11) NOT NULL,
        `status` enum('Active','InActive') NOT NULL,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        `updated-at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`course_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;
     END IF;

-- -----------------------------------------------------------------------------
    -- Creating a table `docsgo-user-courses`
    -- ------------------------------------------------------------------
    IF not exists(SELECT * FROM information_schema.tables WHERE table_schema = 'docsgo' AND table_name = 'docsgo-user-courses' LIMIT 1) THEN

    CREATE TABLE `docsgo-user-courses` (
    `user_course_id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `course_id` int(11) NOT NULL,
    `planned_date` date NOT NULL,
    `completed_date` date NOT NULL,
    `is_certified` tinyint(1) NOT NULL,
    `status` enum('InProgress','Review','Completed','Not-Started') NOT NULL,
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`user_course_id`),
    KEY `idx_user_courses_user1` (`user_id`),
    CONSTRAINT `fk_user_courses_user1` FOREIGN KEY (`user_id`) REFERENCES `docsgo-team-master` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    KEY `idx_user_courses_course1` (`course_id`),
    CONSTRAINT `fk_user_courses_course1` FOREIGN KEY (`course_id`) REFERENCES `docsgo-courses` (`course_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

   END IF;

-- -----------------------------------------------------------------------------
    -- Inserting a record in  `docsgo-settings`
    -- ------------------------------------------------------------------
INSERT INTO `docsgo-settings` (`id`, `type`, `identifier`, `options`) VALUES
(17, 'dropdown', 'userCourseStatus', '[{\"key\":0,\"value\":\"InProgress\"},{\"key\":1,\"value\":\"Review\"},{\"key\":2,\"value\":\"Completed\"},{\"key\":3,\"value\":\"Not-Started\"}]');

end;;
delimiter ';'
call schema_change();
drop procedure if exists schema_change;