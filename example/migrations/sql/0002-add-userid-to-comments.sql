-- //
ALTER TABLE `comments` ADD COLUMN `user_id` INT NOT NULL;
-- //@UNDO
ALTER TABLE `comments` DROP COLUMN `user_id`;
-- //
