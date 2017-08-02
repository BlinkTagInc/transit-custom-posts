<?php

require_once( 'custom-post-type.php' );

// Singleton Alert object
class TCP_Alert extends TCP_CustomPostType {
	private static $instance;
	
	protected function __construct() {
		parent::__construct('Alert', array(
				'menu_icon' => 'dashicons-warning',
				'rewrite'	=> array( 'slug' => 'alerts' ),
		));
		$this->add_meta_box('Alert Fields', array(
			'Affected Routes' => array(
				'helper'	=> 'comma-separated route short names',
				'placeholder'	=> '1,2A,etc',
			),
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