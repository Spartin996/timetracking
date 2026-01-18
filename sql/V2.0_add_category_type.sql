ALTER TABLE `categories` ADD `entries` VARCHAR(1) NOT NULL DEFAULT 'Y' AFTER `active`, 
ADD `projects` VARCHAR(1) NOT NULL DEFAULT 'Y' AFTER `entries`;