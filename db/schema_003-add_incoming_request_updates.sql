ALTER TABLE `request_updates` ADD COLUMN `is_outbound` tinyint(1) UNSIGNED NOT NULL;
ALTER TABLE `request_updates` ADD COLUMN `changed_by_name` varchar(255);
ALTER TABLE `request_updates` ADD COLUMN `media_url` varchar(255);
ALTER TABLE `request_updates` ADD COLUMN `remote_update_id` INTEGER;
ALTER TABLE `request_updates` ADD COLUMN `source_client` INTEGER DEFAULT NULL;
