/*!40101 SET NAMES utf8 */;



/*!40101 SET SQL_MODE=''*/;



/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*Table structure for table `default_ps_activity` */



DROP TABLE IF EXISTS `default_ps_activity`;



CREATE TABLE `default_ps_activity` (
  `activity_id` int(32) NOT NULL AUTO_INCREMENT,
  `site_id` int(6) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `verb` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `module` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `data` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `status` char(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`activity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



/*Table structure for table `default_ps_friends` */



DROP TABLE IF EXISTS `default_ps_friends`;



CREATE TABLE `default_ps_friends` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `friend_id` bigint(20) NOT NULL,
  `is_confirmed` tinyint(1) DEFAULT '0',
  `is_limited` tinyint(1) DEFAULT '0',
  `date_created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `friend_id` (`friend_id`)
) ENGINE=MyISAM;



/*Table structure for table `default_ps_friends_meta` */



DROP TABLE IF EXISTS `default_ps_friends_meta`;



CREATE TABLE `default_ps_friends_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `meta_key` varchar(55) DEFAULT NULL,
  `meta_value` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`)
) ENGINE=MyISAM;



/*Table structure for table `default_ps_likes` */



DROP TABLE IF EXISTS `default_ps_likes`;



CREATE TABLE `default_ps_likes` (
  `id` bigint(22) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `comment_id` int(11) DEFAULT '0',
  `like_type` char(1) DEFAULT '1',
  `liked` int(6) DEFAULT '0',
  `disliked` int(6) DEFAULT '0',
  `is_stats` char(1) DEFAULT '0',
  `like_date` int(11) DEFAULT NULL,
  `ip_address` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `streamid` (`stream_id`),
  KEY `is_stats` (`is_stats`),
  KEY `comment_id` (`comment_id`),
  KEY `authorid` (`author_id`)
) ENGINE=MyISAM;



/*Table structure for table `default_ps_media_oembed` */



DROP TABLE IF EXISTS `default_ps_media_oembed`;



CREATE TABLE `default_ps_media_oembed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `stream_id` int(22) DEFAULT NULL,
  `oe_type` varchar(25) DEFAULT NULL,
  `provider_name` varchar(150) DEFAULT NULL,
  `provider_url` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author_name` varchar(150) DEFAULT NULL,
  `author_url` varchar(255) DEFAULT NULL,
  `media_main` text,
  `media_thumb` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;



/*Table structure for table `default_ps_wall` */



DROP TABLE IF EXISTS `default_ps_wall`;



CREATE TABLE `default_ps_wall` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(6) NOT NULL DEFAULT '0',
  `stream_type` varchar(55) COLLATE utf8_unicode_ci DEFAULT 'custom',
  `object_type` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  `body` text COLLATE utf8_unicode_ci,
  `created_on` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `ip_address` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `is_active` tinyint(1) DEFAULT NULL,
  `likes` int(6) DEFAULT NULL,
  `recent_comments` text COLLATE utf8_unicode_ci,
  `num_comments` int(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;



/*Table structure for table `default_ps_wall_images` */



DROP TABLE IF EXISTS `default_ps_wall_images`;



CREATE TABLE `default_ps_wall_images` (
  `id` bigint(22) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `stream_id` int(11) DEFAULT NULL,
  `added_on` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

/*Table structure for table `default_ps_comments` */



DROP TABLE IF EXISTS `default_ps_comments`;



CREATE TABLE `default_ps_comments` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `stream_type` varchar(25) COLLATE utf8_unicode_ci DEFAULT 'update',
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `created_on` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `ip_address` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `likes` int(7) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
