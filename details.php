<?php defined('BASEPATH') or exit('No direct script access allowed');

class Module_Pyrosocial extends Module {

	public $version = '0.1';
	
	public function info()
	{
		return array(
			'name' => array('en' => 'Pyrosocial'),
			'description' => array(
				'en' => 'Social Community module.',
			),
			'frontend' => FALSE,
			'backend'  => TRUE,
			'menu'	  => FALSE
		);
	}
	
	public function install()
	{
		return TRUE;		
	}

	public function uninstall()
	{
		return TRUE;
	}

	public function upgrade($old_version)
	{
		return TRUE;
	}
	
	public function help()
	{
		// Return a string containing help info
		// You could include a file and return it here.
		return "<h4>Overview</h4>
		<p>Comming soon.</p>";
	}
}
/* End of file details.php */
