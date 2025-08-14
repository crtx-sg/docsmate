-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2021 at 07:01 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `docsgo`
--

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-acronyms`
--

CREATE TABLE `docsgo-acronyms` (
  `id` int(11) NOT NULL,
  `acronym` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `update_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-action-list`
--

CREATE TABLE `docsgo-action-list` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `responsible_id` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `sharing` tinyint(1) NOT NULL,
  `update_date` datetime NOT NULL,
  `action` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`action`)),
  `revision_history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`revision_history`))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-diagrams`
--

CREATE TABLE `docsgo-diagrams` (
  `id` int(11) NOT NULL,
  `diagram_name` varchar(100) NOT NULL,
  `markdown` longtext NOT NULL,
  `author_id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-document-master`
--

CREATE TABLE `docsgo-document-master` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `category` varchar(64) NOT NULL,
  `version` varchar(50) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `ref` varchar(100) DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `status` enum('Draft','Approved','Obsolete') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-document-template`
--

CREATE TABLE `docsgo-document-template` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `type` varchar(60) NOT NULL,
  `template-json-object` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-documents`
--

CREATE TABLE `docsgo-documents` (
  `id` int(11) NOT NULL,
  `project-id` int(11) NOT NULL,
  `review-id` int(11) DEFAULT NULL,
  `type` varchar(64) NOT NULL,
  `category` varchar(64) NOT NULL,
  `update-date` datetime NOT NULL,
  `json-object` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `file-name` varchar(64) DEFAULT NULL,
  `author-id` int(11) NOT NULL,
  `reviewer-id` int(11) DEFAULT NULL,
  `status` varchar(64) NOT NULL,
  `revision-history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`revision-history`)),
  `download-path` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-inventory-master`
--

CREATE TABLE `docsgo-inventory-master` (
  `id` int(11) NOT NULL,
  `item` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `serial` varchar(50) NOT NULL,
  `entry_date` date NOT NULL,
  `retired_date` date NOT NULL,
  `cal_date` date NOT NULL,
  `cal_due` date NOT NULL,
  `location` varchar(50) NOT NULL,
  `invoice` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `vendor` varchar(50) NOT NULL,
  `status` enum('active','in-active','not-found','cal-overdue') NOT NULL,
  `used_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `update_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-projects`
--

CREATE TABLE `docsgo-projects` (
  `project-id` int(11) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `start-date` date NOT NULL,
  `end-date` date DEFAULT NULL,
  `status` enum('Active','Completed') NOT NULL,
  `manager-id` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `download-path` varchar(100) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-requirements`
--

CREATE TABLE `docsgo-requirements` (
  `id` int(11) NOT NULL,
  `project-id` int(11) NULL,
  `product-id` int(11) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `requirement` varchar(100) NOT NULL,
  `description` longtext DEFAULT NULL,
  `update_date` datetime NOT NULL DEFAULT current_timestamp(),
  CONSTRAINT `fk_requirements_product1` FOREIGN KEY (`product-id`) REFERENCES `docsgo-products` (`product-id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-reviews`
--

CREATE TABLE `docsgo-reviews` (
  `id` int(11) NOT NULL,
  `project-id` int(11) NOT NULL,
  `review-name` varchar(64) NOT NULL,
  `review-ref` text DEFAULT NULL,
  `review-by` varchar(50) NOT NULL,
  `context` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `code-diff` mediumtext DEFAULT NULL,
  `assigned-to` varchar(50) NOT NULL,
  `status` varchar(64) NOT NULL,
  `category` varchar(60) NOT NULL,
  `updated-at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-risks`
--

CREATE TABLE `docsgo-risks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `risk_type` varchar(100) DEFAULT NULL,
  `risk` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `component` varchar(100) NOT NULL,
  `failure_mode` longtext DEFAULT NULL,
  `harm` longtext DEFAULT NULL,
  `cascade_effect` longtext DEFAULT NULL,
  `hazard-analysis` text NOT NULL,
  `assessment` longtext NOT NULL,
  `baseScore_severity` float NOT NULL,
  `status` enum('Open','Close') NOT NULL,
  `update_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-settings`
--

CREATE TABLE `docsgo-settings` (
  `id` int(11) NOT NULL,
  `type` enum('dropdown','url','properties') NOT NULL,
  `identifier` varchar(50) NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `docsgo-settings`
--

INSERT INTO `docsgo-settings` (`id`, `type`, `identifier`, `options`) VALUES
(1, 'dropdown', 'templateCategory', '[{\"key\":0,\"value\":\"General\"},{\"key\":1,\"value\":\"Planning\"},{\"key\":2,\"value\":\"Design Input\"},{\"key\":3,\"value\":\"Design Output\"},{\"key\":4,\"value\":\"Design Verification\"},{\"key\":5,\"value\":\"Design Transfer\"},{\"key\":6,\"value\":\"Risk Management\"}]'),
(2, 'url', 'third-party', '[{\"key\":\"sonar\",\"url\":\"\",\"apiKey\":\"\"},{\"key\":\"testLink\",\"url\":\"\",\"apiKey\":\"\"},{\"key\":\"jenkins\",\"url\":\"\",\"apiKey\":\"\"}]'),
(3, 'dropdown', 'documentStatus', '[{\"key\":0,\"value\":\"Request Review\"},{\"key\":1,\"value\":\"Request Change\"},{\"key\":2,\"value\":\"Approved\"},{\"key\":3,\"value\":\"Draft\"}]'),
(6, 'dropdown', 'reviewCategory', '[{\"key\":0,\"value\":\"Code\"},{\"key\":1,\"value\":\"Design\"},{\"key\":2,\"value\":\"Plan\"},{\"key\":3,\"value\":\"Risk Management\"},{\"key\":4,\"value\":\"Release\"},{\"key\":5,\"value\":\"Requirements\"},{\"key\":6,\"value\":\"Traceability\"},{\"key\":7,\"value\":\"User Needs\"},{\"key\":8,\"value\":\"Validation\"},{\"key\":9,\"value\":\"Verification\"},{\"key\":10,\"value\":\"Unit Test\"}]'),
(7, 'dropdown', 'userRole', '[{\"key\":0,\"value\":\"Application Tester\"},\r\n{\"key\":1,\"value\":\"Consultant\"},\r\n{\"key\":2,\"value\":\"Director Software Engineering\"},\r\n{\"key\":3,\"value\":\"Principal Architect\"},\r\n{\"key\":4,\"value\":\"Principal Engineer\"},\r\n{\"key\":5,\"value\":\"Project Engineer\"},\r\n{\"key\":6,\"value\":\"Senior Engineering Manager\"},\r\n{\"key\":7,\"value\":\"Senior Project Engineer\"},\r\n{\"key\":8,\"value\":\"Test Lead\"},\r\n{\"key\":9,\"value\":\"Vice President, Engineering\"}]'),
(8, 'dropdown', 'riskCategory', '[{\"key\":0,\"value\":\"Open-Issue\"},{\"key\":1,\"value\":\"Vulnerability\"},{\"key\":2,\"value\":\"SOUP\"},{\"key\":3,\"value\":\"Scope-Items\"},{\"key\":4,\"value\":\"Automation\"}]'),
(9, 'dropdown', 'referenceCategory', '[{\"key\":0,\"value\":\"Design\"},{\"key\":1,\"value\":\"Impact Analysis\"},{\"key\":2,\"value\":\"Requirement\"},{\"key\":3,\"value\":\"Test\"},{\"key\":4,\"value\":\"Standards\"},{\"key\":5,\"value\":\"Other\"}]'),
(10, 'dropdown', 'requirementsCategory', '[{\"key\":0,\"value\":\"Subsystem\",\"isRoot\":false},{\"key\":1,\"value\":\"System\",\"isRoot\":false},{\"key\":2,\"value\":\"User Needs\",\"isRoot\":true},{\"key\":3,\"value\":\"Standards\",\"isRoot\":true},{\"key\":4,\"value\":\"Guidance\",\"isRoot\":true},{\"key\":5,\"value\":\"User Needs 2\",\"isRoot\":true}]'),
(11, 'dropdown', 'assetsCategory', '[{\"key\":0,\"value\":\"Accessory\"},{\"key\":1,\"value\":\"Device Under Test\"},{\"key\":2,\"value\":\"Measuring Instrument\"},{\"key\":3,\"value\":\"Tablet\"},{\"key\":4,\"value\":\"Development System\"}]'),
(12, 'properties', 'documentProperties', '[{\"key\":\"docTitle\",\"value\":\"\"},{\"key\":\"docIcon\",\"value\":\"\"},{\"key\":\"docConfidential\",\"value\":\"\"}]'),
(13, 'dropdown', 'timeTrackerCategory', '[{\"key\":0,\"value\":\"Meeting\"},{\"key\":1,\"value\":\"Development\"},{\"key\":2,\"value\":\"Testing\"},{\"key\":3,\"value\":\"Verification\"},{\"key\":4,\"value\":\"Review\"},{\"key\":5,\"value\":\"Documentation\"},{\"key\":6,\"value\":\"Other\"},{\"key\":7,\"value\":\"Research/Analysis\"}]'),
(14, 'dropdown', 'timesheetStatus', '[{\"key\":0,\"value\":\"Open\"},{\"key\":1,\"value\":\"Close\"}]'),
(15, 'dropdown', 'taskCategory', '[{\"key\":0,\"value\":\"Task\",\"isRoot\":false},{\"key\":1,\"value\":\"New Feature\",\"isRoot\":false},{\"key\":2,\"value\":\"Improvement\",\"isRoot\":true},{\"key\":3,\"value\":\"Bug\",\"isRoot\":true}]'),
(16, 'dropdown', 'taskType', '[{\"key\":0,\"value\":\"Todo\",\"isRoot\":false},{\"key\":1,\"value\":\"InProgress\",\"isRoot\":false},{\"key\":2,\"value\":\"UnderVerification\",\"isRoot\":true},{\"key\":3,\"value\":\"Complete\",\"isRoot\":true}]');
(17, 'dropdown', 'userCourseStatus', '[{\"key\":0,\"value\":\"InProgress\"},{\"key\":1,\"value\":\"Review\"},{\"key\":2,\"value\":\"Completed\"},{\"key\":3,\"value\":\"Not-Started\"}]');
-- --------------------------------------------------------

--
-- Table structure for table `docsgo-taskboard`
--

CREATE TABLE `docsgo-taskboard` (
  `id` int(11) NOT NULL,
  `task_column` varchar(20) NOT NULL,
  `task_category` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `project_id` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `assignee` int(11) DEFAULT NULL,
  `verifier` int(11) DEFAULT NULL,
  `comments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`comments`)),
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-team-master`
--

CREATE TABLE `docsgo-team-master` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `responsibility` varchar(100) DEFAULT NULL,
  `is-manager` tinyint(1) NOT NULL DEFAULT 0,
  `is-admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is-active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `docsgo-team-master`
--

INSERT INTO `docsgo-team-master` (`id`, `name`, `email`, `password`, `role`, `responsibility`, `is-manager`, `is-admin`, `created_at`, `updated_at`, `is-active`) VALUES
(1, 'user', 'user@docsgo.com', '$2y$10$QFG0v/fICPHnhHcVWckVcujd0na3M2im/SW6FNhRgcJ0KA3exPD7K', NULL, 'Admin', 1, 1, '2021-06-17 07:47:14', '2021-06-17 19:17:14', 1);

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-test-cases`
--

CREATE TABLE `docsgo-test-cases` (
  `id` int(11) NOT NULL,
  `testcase` varchar(100) NOT NULL,
  `description` longtext DEFAULT NULL,
  `update_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-time-tracker`
--

CREATE TABLE `docsgo-time-tracker` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `tracker_date` date NOT NULL,
  `action_list` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-traceability`
--

CREATE TABLE `docsgo-traceability` (
  `id` int(11) NOT NULL,
  `project-id` int(11) NULL,
  `product-id` int(11) NOT NULL,
  `root_requirement` varchar(100) DEFAULT NULL,
  `design` text NOT NULL,
  `code` text NOT NULL,
  `description` longtext DEFAULT NULL,
  `update_date` datetime NOT NULL DEFAULT current_timestamp(),
  CONSTRAINT `fk_traceability_product1` FOREIGN KEY (`product-id`) REFERENCES `docsgo-products` (`product-id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `docsgo-traceability-options`
--

CREATE TABLE `docsgo-traceability-options` (
  `id` int(11) NOT NULL,
  `traceability_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `requirement_id` int(11) NOT NULL,
  `update_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` text NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `docsgo-queue`
--

DROP TABLE IF EXISTS `docsgo-queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docsgo-queue` (
  `id` int(11) unsigned NOT NULL,
  `type` varchar(64) NOT NULL,
  `status` enum('SUBMITTED','PROCESSING','SUCCESS','FAILED') NOT NULL DEFAULT 'SUBMITTED',
  `json` longtext NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table structure for table `docsgo-timesheet`
--

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

--
-- Table structure for table `docsgo-products`
--

CREATE TABLE `docsgo-products` (
  `product-id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` longtext DEFAULT NULL,
  `display-name` varchar(100) NOT NULL,
  `status` enum('Active','InActive','Completed') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product-id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `docsgo-products-projects-mapping`
--

CREATE TABLE `docsgo-products-projects-mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product-id` int(11)  NULL,
  `project-id` int(11)  NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   PRIMARY KEY (`id`),
   KEY `idx_products_projects_project1` (`project-id`),
   CONSTRAINT `fk_products_projects_project1` FOREIGN KEY (`project-id`) REFERENCES `docsgo-projects` (`project-id`) ON DELETE  NO ACTION ON UPDATE NO ACTION,
KEY `idx_products_projects_product1` (`product-id`),
   CONSTRAINT `fk_products_projects_product1` FOREIGN KEY (`product-id`) REFERENCES `docsgo-products` (`product-id`) ON DELETE  NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `docsgo-courses`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=0  DEFAULT CHARSET=utf8mb4;


--
-- Table structure for table `docsgo-user-courses`
--

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


-- --------------------------------------------------------
--
-- Indexes for dumped tables
--

--
-- Indexes for table `docsgo-acronyms`
--
ALTER TABLE `docsgo-acronyms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-action-list`
--
ALTER TABLE `docsgo-action-list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-diagrams`
--
ALTER TABLE `docsgo-diagrams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-document-master`
--
ALTER TABLE `docsgo-document-master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-document-template`
--
ALTER TABLE `docsgo-document-template`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-documents`
--
ALTER TABLE `docsgo-documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-inventory-master`
--
ALTER TABLE `docsgo-inventory-master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-projects`
--
ALTER TABLE `docsgo-projects`
  ADD PRIMARY KEY (`project-id`);

--
-- Indexes for table `docsgo-requirements`
--
ALTER TABLE `docsgo-requirements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-reviews`
--
ALTER TABLE `docsgo-reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-risks`
--
ALTER TABLE `docsgo-risks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-settings`
--
ALTER TABLE `docsgo-settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-taskboard`
--
ALTER TABLE `docsgo-taskboard`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-team-master`
--
ALTER TABLE `docsgo-team-master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-test-cases`
--
ALTER TABLE `docsgo-test-cases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-time-tracker`
--
ALTER TABLE `docsgo-time-tracker`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-traceability`
--
ALTER TABLE `docsgo-traceability`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `docsgo-traceability-options`
--
ALTER TABLE `docsgo-traceability-options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `docsgo-queue`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `docsgo-acronyms`
--
ALTER TABLE `docsgo-acronyms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-action-list`
--
ALTER TABLE `docsgo-action-list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-diagrams`
--
ALTER TABLE `docsgo-diagrams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-document-master`
--
ALTER TABLE `docsgo-document-master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-document-template`
--
ALTER TABLE `docsgo-document-template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-documents`
--
ALTER TABLE `docsgo-documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-inventory-master`
--
ALTER TABLE `docsgo-inventory-master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-projects`
--
ALTER TABLE `docsgo-projects`
  MODIFY `project-id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-requirements`
--
ALTER TABLE `docsgo-requirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-reviews`
--
ALTER TABLE `docsgo-reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-risks`
--
ALTER TABLE `docsgo-risks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-settings`
--
ALTER TABLE `docsgo-settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `docsgo-taskboard`
--
ALTER TABLE `docsgo-taskboard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-team-master`
--
ALTER TABLE `docsgo-team-master`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `docsgo-test-cases`
--
ALTER TABLE `docsgo-test-cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-time-tracker`
--
ALTER TABLE `docsgo-time-tracker`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-traceability`
--
ALTER TABLE `docsgo-traceability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `docsgo-traceability-options`
--
ALTER TABLE `docsgo-traceability-options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

  --
-- AUTO_INCREMENT for table `docsgo-queue`
--
ALTER TABLE `docsgo-queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
