<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Pyrosocial extends Module {

	public $version = '0.1';
	
	public function info()
	{
		return array(
			'name' => array('en' => 'Pyrosocial'),
			'description' => array(
				'en' => 'Let users setup a profile.',
			),
			'frontend' => FALSE,
			'backend'  => TRUE,
			'menu'	  => FALSE
		);
	}
	
	public function install()
	{
		/*$this->dbforge->drop_table('profile_group');
		$this->dbforge->drop_table('profile_fields');
		$this->dbforge->drop_table('profile_vals');

		$profile_group = "
			CREATE TABLE ".$this->db->dbprefix('profile_group')." (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `title` varchar(255) NOT NULL,
			  `slug` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `slug` (`slug`),
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
		";

		$profile_fields = "
			CREATE TABLE ".$this->db->dbprefix('profile_fields')." (
		  `field_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
		  `label` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
		  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  `default_value` text COLLATE utf8_unicode_ci NOT NULL,		  
		  `alias` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
		  `required` tinyint(1) NOT NULL DEFAULT '0',
		  `display` tinyint(1) unsigned NOT NULL,
		  `publish` tinyint(1) unsigned NOT NULL DEFAULT '0',
		  `on_search` tinyint(1) unsigned NOT NULL DEFAULT '0',
		  `on_signup` tinyint(1) unsigned NOT NULL DEFAULT '0',
		  `order` smallint(3) unsigned NOT NULL DEFAULT '999',
		  `config` text COLLATE utf8_unicode_ci,
		  `validators` text COLLATE utf8_unicode_ci,
		  `error` text COLLATE utf8_unicode_ci,
		  PRIMARY KEY (`field_id`)
		) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		";
		$profile_vals = "
		CREATE TABLE ".$this->db->dbprefix('profile_vals')." (
		 `item_id` int(11) unsigned NOT NULL,
		  `field_id` int(11) unsigned NOT NULL,
		  `index` smallint(3) unsigned NOT NULL DEFAULT '0',
		  `value` text COLLATE utf8_unicode_ci NOT NULL,
		  PRIMARY KEY (`item_id`,`field_id`,`index`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
*/
		
			return TRUE;
		
	}

	public function uninstall()
	{
		//it's a core module, lets keep it around
		return FALSE;
	}

	public function upgrade($old_version)
	{
		// Your Upgrade Logic
		return TRUE;
	}
	
	public function help()
	{
		// Return a string containing help info
		// You could include a file and return it here.
		return "<h4>Overview</h4>
		<p>The Users module works together with Groups and Permissions to give PyroCMS access control.</p>
		<h4>Add a User</h4><hr>
		<p>Fill out the user's details (including a password) and save. If you have activation emails enabled in Settings
		an email will be sent to the new user with an activation link.</p>
		<h4>Activating New Users</h4><hr>
		<p>If activation emails are disabled in Settings users that register on the website front-end will appear under the Inactive Users
		menu item until you either approve or delete their account. If activation emails are enabled users may register silently, without an admin's help.</p>";
	}
}
/* End of file details.php */
