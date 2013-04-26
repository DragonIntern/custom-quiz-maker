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
// =========================================================================
register_activation_hook(__FILE__,'dragon_quiz_install');
function dragon_quiz_install() {
	// Table initialization
	global $wpdb;
	$table_quiz = $wpdb->prefix . "dragon_quiz_list";
	$sql_quiz = "CREATE TABLE ".$table_quiz." (
		id mediumint(9) NOT NULL,
		title text NOT NULL,
		questions text NOT NULL,
		results text NOT NULL,
		UNIQUE KEY id (id)
		);";
	$table_question = $wpdb->prefix . "dragon_quiz_question";
	$sql_question = "CREATE TABLE ".$table_question." (
		id mediumint(9) NOT NULL,
		question text NOT NULL,
		type text NOT NULL,
		answers text NOT NULL,
		results text NOT NULL,
		weight text NOT NULL,
		UNIQUE KEY id (id)
		);";
	$table_result = $wpdb->prefix . "dragon_quiz_result";
	$sql_result = "CREATE TABLE ".$table_result." (
		id mediumint(9) NOT NULL,
		name text NOT NULL,
		image text NOT NULL,
		content text NOT NULL,
		UNIQUE KEY id (id)
		);";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	dbDelta($sql_quiz);
	dbDelta($sql_question);
	dbDelta($sql_result);

	// Added some welcome data to ensure proper functionality.
	$wpdb->insert( $table_quiz, array( 
		'id' => 0, 
		'title' => 'My Quiz', 
		'questions' => '0', 
		'results' => '0', 
		));
	$wpdb->insert( $table_question, array( 
		'id' => 0, 
		'question' => 'Question', 
		'type' => 'radio', 
		'answers' => 'Answer', 
		'results' => '0',
		'weight' => '1' 
		));
	$wpdb->insert( $table_result, array( 
		'id' => 0, 
		'name' => 'Result', 
		'image' => WP_PLUGIN_DIR . '/custom-quiz-maker/ds-logo.png' , 
		'content' => '<p>This is the Result</p>'
		));
}

// Shortcode used to display the quiz
// =========================================================================
function dragon_quiz_shortcode( $atts ) {
	$atts = extract( shortcode_atts( array( 'id'=>'-1', 'name'=>'' ),$atts ) );

	if( $id != -1 ){
		the_dragon_quiz( $id );
	}
	else if( $name != null ) {
		the_dragon_quiz( $name );
	}
}
add_shortcode( 'custom-quiz','dragon_quiz_shortcode' );

// Calls the code needed to display the quix and returns it as a string.
// =========================================================================
function get_dragon_quiz( $input ) {

	// Get the ID of the quiz in the table
	$id = dragon_quiz_get_id( $input );

	echo '<div class="quiz">';

		// Get the specific quiz data from the quiz list.
		$atts = get_row( 'list' , 'id = '. $id );

		// Display the title of the Quiz
		echo "<div class='quiz_title'>";
			echo $atts['title'];
		echo "</div>";

		// The entire list of quiz questions
		echo "<div class='quiz_questions'>";

			// Cycle through each Question ID in the list
			$ds_questionIDs = explode( ',' , $atts['questions'] );
			foreach ($ds_questionIDs as $ds_quesID) {

				// For a single Question ID, get the specific Question row from the table
				$ds_ask = get_row('question','id = '.$ds_quesID);

				echo '<div class="one_question">';
					// Display the question contents
					echo '<h2>'.$ds_ask['question'].'</h2>';

					// For Now, display whether it is a radio button question or not.
					echo '<div>'.$ds_ask['radio'].'</div>';

					// Cycle through each Answer String in the list
					$answerList = explode( ',' , $ds_ask['answers']);
					foreach ( $answerList as $answer ) {
						echo '<div class="one_answer"'.$answer.'</div>';
					}

					// Display the results and weight for now.
					echo '<div>'.$ds_ask['results'].'</div>';
					echo '<div>'.$ds_ask['weight'].'</div>';

				echo '</div><!-- .one_question -->';
			}
		echo "</div><!-- .quiz_questions -->";

		echo "<div class='quiz_results'>";
			echo "<h2>Results:</h2>";
			$ds_resultIDs = explode( ',' ,  $atts['results'] );
			$ds_resultList = explode(',' , $ds_resultIDs);
			foreach ($ds_resultList as $ds_resultID) {
				echo $ds_resultID;
			}
		echo "</div>";

	echo '</div><!-- .quiz -->';
}

// Displays the coded for the quiz
// =========================================================================
function the_dragon_quiz( $input ) {
	echo get_dragon_quiz( $input );
}

// Returns the ID of the specified quiz
// =========================================================================
function dragon_quiz_get_id( $input ) {
	// Return the ID if only a number is specified
	if( is_numeric($input) ) {
		return $input;
	}
	else {
		// The Quiz Name was obviously given.  Search the list of quizzes for it.
		$atts = get_row( 'list' , 'title = "' . $input . '"');
		return $atts['id'];
	}
}

// Generic Table reading shortener
// =========================================================================
function get_row( $table_suffix , $criteria ) {
	// Get the table data
	global $wpdb;
	$table_name = $wpdb->prefix . "dragon_quiz_" . $table_suffix;
	$values = $wpdb->get_row( "SELECT * FROM $table_name WHERE " . $criteria , ARRAY_A );
	return $values;
}

// Backend Graphical User Interface
// =========================================================================

