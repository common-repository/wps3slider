CREATE TABLE IF NOT EXISTS `{PREFIX}wps3_galleries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `tstamp` int(10) unsigned NOT NULL,
  `overlay_colour` varchar(7) NOT NULL DEFAULT '#000000',
  `opacity` float NOT NULL DEFAULT '0.7',
  `text_colour` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `width` int(10) unsigned NOT NULL DEFAULT '400',
  `height` int(10) unsigned NOT NULL DEFAULT '200',
  `timeout` int(10) unsigned NOT NULL DEFAULT '4000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-NEXT-
CREATE TABLE IF NOT EXISTS  `{PREFIX}wps3_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gallery_id` int(10) unsigned NOT NULL,
  `image_path` text,
  `href` text,
  `overlay_text` text,
  `order` tinyint(3) unsigned NOT NULL,
  `span_location` varchar(12) NOT NULL DEFAULT 'top',
  PRIMARY KEY (`id`),
  KEY `FK_{PREFIX}wps3_items_1` (`gallery_id`),
  CONSTRAINT `FK_{PREIFX}wps3_items_1` FOREIGN KEY (`gallery_id`) REFERENCES `{PREFIX}wps3_galleries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;