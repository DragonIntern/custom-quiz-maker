<?php  
/*
Plugin Name: Custom Quiz Maker
Plugin URI: http://www.dragonsearchmarketing.com/dragon-quiz/
Description: Easily create a unique quiz on your pages that you can customize.
Version: 1.0
Author: DragonSearch
Author URI: http://dragonsearchmarketing.com/
License: GPL 2
*/

/**
* This is a custom Wordpress addon that will display a simple docked GUI for the user to input custom quiz data.
**/

// Installation which will occur upon Activation
register_activation_hook(__FILE__,'dragon_quiz_install');
function dragon_quiz_install() {
	// Table creation
	global $wpdb;
	$table_name = $wpdb->prefix . "dragon_quiz";
	  
	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL,
		ds_title text NOT NULL,
		ds_type text NOT NULL,
		ds_answers text NOT NULL,
		ds_results text NOT NULL,
		UNIQUE KEY id (id)
		);";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	dbDelta($sql);

	// Added some welcome data to ensure proper functionality.
	$rows_affected = $wpdb->insert( $table_name, array( 
		'id' => 0, 
		'ds_title' => 'Welcome', 
		'ds_type' => 'DragonSearch', 
		'ds_answers' => 'DragonSearch', 
		'ds_results' => ' ' 
		));
}