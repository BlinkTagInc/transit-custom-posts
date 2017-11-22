<?php

require_once( 'custom-post-type.php' );

// Singleton Alert object
class TCP_BoardMeeting extends TCP_CustomPostType {
	private static $instance;
	
	protected function __construct() {
		parent::__construct('Board Meeting', array(
				'menu_icon' => 'dashicons-clipboard',
				'rewrite'	=> array( 'slug' => 'board-meetings' ),
		));
		$this->add_meta_box('Required Fields', array(
			'Date'	=> array(
				'type' => 'date',
			),
		), 'normal', 'high');
		
		if ( get_option('tcp_board_fields')) {
			$cust_board_fields = get_option('tcp_board_fields');
			$field_arr = array();
			if ( in_array('tcp_location_field', $cust_board_fields) ) {
				$field_arr['Location'] = array( 'type' => 'text' );
			}
			// if ( in_array('tcp_agenda_field', $cust_board_fields) ) {
			// 	$field_arr['Agenda (PDF)'] = array( 'type' => 'file' );
			// }
			// if ( in_array('tcp_minutes_field', $cust_board_fields) ) {
			// 	$field_arr['Minutes (PDF)'] = array( 'type' => 'file' );
			// }
			
			$this->add_meta_box('Meeting Information', $field_arr, 'side');
		}
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