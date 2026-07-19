ALTER TABLE `entries` ADD `follow_up` VARCHAR(1) NOT NULL DEFAULT 'N' AFTER `interrupted`;
UPDATE `entries` SET `follow_up` = 'Y' WHERE `interrupted` = 'Y';
