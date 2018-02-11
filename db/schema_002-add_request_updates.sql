CREATE TABLE `request_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_desc` text,
  `old_status_id` int(11) DEFAULT NULL,
  `changed_by` int(11),
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`,`status_id`,`updated_at`),
  KEY `changed_by` (`changed_by`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;