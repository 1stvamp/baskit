-- //
CREATE TABLE `users` (
	id` int(11) unsigned NOT NULL auto_increment,
	`name` varchar(255) NOT NULL,
	`email` varchar(255) NOT NULL,
	`password` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)

);
CREATE TABLE `comments` (
	id` int(11) unsigned NOT NULL auto_increment,
	`body_text` text NOT NULL,
	PRIMARY KEY (`id`)

);
-- //@UNDO
DROP TABLE `users`;
DROP TABLE `comments`;
-- //
