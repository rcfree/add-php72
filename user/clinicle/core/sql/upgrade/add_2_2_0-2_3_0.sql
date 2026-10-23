ALTER TABLE `encounter` ADD COLUMN `data_hash` VARCHAR(40) NULL  AFTER `date_updated` 
, ADD INDEX `encounter_FI_4` (`data_hash` ASC);

ALTER TABLE `audit` ADD INDEX `record` (`record_name`,`record_type`,`user_id`),
ADD INDEX `timestamp` (`timestamp`);