CREATE TABLE `album_files` (
  `album_files_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(10) unsigned NOT NULL,
  `file_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`album_files_id`),
  KEY `gallery_file` (`album_id`,`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8

CREATE TABLE `albums` (
  `album_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`album_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8

CREATE TABLE `file_info` (
  `file_id` int(11) unsigned NOT NULL,
  `key` enum('exif_create_timestamp','exif_description','exif_title','exif_keywords') NOT NULL DEFAULT 'exif_create_timestamp',
  `value` text NOT NULL,
  PRIMARY KEY (`file_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE `files` (
  `file_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `folder_id` int(10) unsigned NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `md_path` char(32) NOT NULL,
  `md_content` char(32) NOT NULL,
  `processed` int(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`file_id`),
  UNIQUE KEY `md_content` (`md_content`),
  UNIQUE KEY `md_path` (`md_path`),
  KEY `folder_id` (`folder_id`),
  KEY `processed` (`processed`)
) ENGINE=InnoDB AUTO_INCREMENT=233 DEFAULT CHARSET=utf8

CREATE TABLE `folders` (
  `folder_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `level` smallint(5) unsigned NOT NULL,
  `left` int(10) unsigned NOT NULL,
  `right` int(10) unsigned NOT NULL,
  PRIMARY KEY (`folder_id`),
  KEY `level` (`level`),
  KEY `left` (`left`),
  KEY `right` (`right`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE `folder_property` (
  `folder_id` int(10) unsigned NOT NULL,
  `access_code` char(32) DEFAULT NULL,
  UNIQUE KEY `folder_id` (`folder_id`),
  UNIQUE KEY `access_code` (`access_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8