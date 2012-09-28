<?php
namespace CityIndex\WP\PostExternal\Modules;
use CityIndex\WP\PostExternal\Controller;

class NetworkAdmin extends Controller{
	
	function __construct(){
		
		$this->action_wp = array(
			'network_admin_menu' => array(&$this, 'admin_menu')
		);
		
		parent::__construct( __CLASS__ );
	}
	
	public function admin_menu(){
		add_menu_page("Plugin Framework", "Plugin Framework", "options", "plugin-framework", array(&$this, 'get_page'));
	}
}