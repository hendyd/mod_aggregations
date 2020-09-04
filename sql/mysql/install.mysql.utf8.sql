CREATE TABLE IF NOT EXISTS `#__aggregation_form` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`userid` int(11) DEFAULT NULL,
	`category` varchar(255) DEFAULT NULL,
	`subcategory` varchar(255) DEFAULT NULL,
	`data` varchar(25000) DEFAULT NULL,
	`date_submitted` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;