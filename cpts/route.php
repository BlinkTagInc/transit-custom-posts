<?php

require_once( 'custom-post-type.php' );

// Singleton Alert object
class TCP_Route extends TCP_CustomPostType {
	private static $instance;
	
	protected function __construct() {
		parent::__construct('Route', array(
				'menu_icon' => 'dashicons-location-alt',
				'rewrite'	=> array( 'slug' => 'routes' ),
		));
		$this->add_meta_box('Route Fields', array(
			'Route ID' 			=> array(),
			'Route Short Name' 	=> array(),
			'Route Long Name'	=> array(),
			'Route Description'	=> array(),
			'Route Color'		=> array(),
			'Route Text Color'	=> array(),
			'Route Sort Order'	=> array(),
			'Agency ID'			=> array(),	
		));
	}
	
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public static function isActive() {
		return isset(self::$instance);
	}
}