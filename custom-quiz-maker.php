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
	// Table initialization
	global $wpdb;
	$table_quiz = $wpdb->prefix . "dragon_quiz_list";
	$sql_quiz = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL,
		ds_title text NOT NULL,
		ds_questions text NOT NULL,
		ds_results text NOT NULL,
		UNIQUE KEY id (id)
		);";
	$table_question = $wpdb->prefix . "dragon_quiz_question";
	$sql_question = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL,
		ds_question text NOT NULL,
		ds_type text NOT NULL,
		ds_answers text NOT NULL,
		ds_results text NOT NULL,
		ds_weight text NOT NULL,
		UNIQUE KEY id (id)
		);";
	$table_result = $wpdb->prefix . "dragon_quiz_result";
	$sql_result = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL,
		ds_name text NOT NULL,
		ds_image text NOT NULL,
		ds_content text NOT NULL,
		UNIQUE KEY id (id)
		);";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	dbDelta($sql_quiz);
	dbDelta($sql_question);
	dbDelta($sql_result);

	// Added some welcome data to ensure proper functionality.
	$wpdb->insert( $sql_quiz, array( 
		'id' => 0, 
		'ds_title' => 'My Quiz', 
		'ds_questions' => '0', 
		'ds_results' => '0', 
		));
	$wpdb->insert( $sql_question, array( 
		'id' => 0, 
		'ds_question' => 'Question', 
		'ds_type' => 'radio', 
		'ds_answers' => 'Answer', 
		'ds_results' => '0',
		'ds_weight' => '1' 
		));
	$wpdb->insert( $sql_result, array( 
		'id' => 0, 
		'ds_name' => 'Result', 
		'ds_image' => WP_PLUGIN_DIR . '/custom-quiz-maker/ds-logo.png' , 
		'ds_content' => '<p>This is the Result</p>'
		));
}

// Shortcode used to display the quiz
function dragon_quiz_shortcode( $atts ) {
	$atts = extract( shortcode_atts( array( 'id'=>'0', 'name'=>'My Quiz' ),$atts ) );
	echo 'SHORTCODE['.$id.','.$name.']';
	if( $id != null ){
		the_dragon_quiz( $id );
	}
	else if( $name != null ) {
		the_dragon_quiz( $name );
	}
}
add_shortcode( 'custom-quiz','dragon_quiz_shortcode' );

// Calls the code needed to display the quix and returns it as a string.
function get_dragon_quiz( $input ) {

	echo 'GETINPUT['.$input.']';

	// Get the ID of the quiz in the table
	$id = dragon_quiz_get_id( $input );

	echo 'ID['.$id.']';

	echo '<div class="quiz">';

		// Get the specific quiz from the quiz list.
		$atts = get_row( 'list' , 'id = $id' );
		print_r($atts);
		$ds_title = $atts['ds_title'];
		$ds_questions = explode( ',' , $atts['ds_questions'] );
		$ds_results = explode( ',' ,  $atts['ds_results'] );
		print_r($ds_title);
		print_r($ds_questions);
		print_r($ds_results);

	echo '</div><!-- .quiz -->';
}

// Displays the coded for the quiz
function the_dragon_quiz( $input ) {
	echo 'THEINPUT['.$input.']';
	echo get_dragon_quiz( $input );
}

// Returns the ID of the specified quiz
function dragon_quiz_get_id( $input ) {
	// Return the ID if only a number is specified
	if( is_numeric($input) ) {
		return $input;
	}
	else {
		// The Quiz Name was obviously given.  Search the list of quizzes for it.
		$atts = get_row( 'list' , 'ds_title = ' . $input );
		return $atts['id'];
	}
}

// Generic Table reading shortener
function get_row( $table_suffix , $criteria ) {
	// Get the table data
	global $wpdb;
	echo "CRITERIA[".$criteria."]";
	$table_name = $wpdb->prefix . "dragon_quiz_" . $table_suffix;
	$values = $wpdb->get_row( "SELECT * FROM $table_name WHERE " . $criteria , ARRAY_A );
	return $values;
}