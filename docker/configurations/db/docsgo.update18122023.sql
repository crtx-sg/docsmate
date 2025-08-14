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
    -- Delete a record and insert a record in the table `docsgo-settings`
    -- ------------------------------------------------------------------
IF exists(SELECT * FROM information_schema.tables WHERE table_schema = 'docsgo' AND table_name = 'docsgo-settings' LIMIT 1) THEN

    delete from `docsgo-settings` where `id`=2;

    INSERT INTO `docsgo-settings` (`id`, `type`, `identifier`, `options`) VALUES (2, 'url', 'third-party', '[{\"key\":\"sonar\",\"url\":\"http://devops.company.in:9000\",\"apiKey\":\"sqa_7ee3af5957a4cb830c11d606ddf630fdfadd565e\"},{\"key\":\"testLink\",\"url\":\"http://13.127.247.145\",\"apiKey\":\"06b767112dc6fe41b6b96abf87b032c4\"},{\"key\":\"jenkins\",\"url\":\"http://devops.company.in:8081\",\"apiKey\":\"1168b64c57e96fc3bb0857e5ccc8e8a99e\", \"fileManagerUrl\":\"https://devops.company.in\", \"job\":\"build_repos\", \"user\":\"company\"},{\"key\":\"generic\",\"dailyBuildLiveUrl\":\"https://automation.company.in\"}]');

END IF;


end;;
delimiter ';'
call schema_change();
drop procedure if exists schema_change;
