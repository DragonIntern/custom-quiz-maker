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
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		title text NOT NULL,
		UNIQUE KEY id (id)
		);";
	$table_question = $wpdb->prefix . "dragon_quiz_question";
	$sql_question = "CREATE TABLE ".$table_question." (
		id mediumint(9) NOT NULL AUTO_INCREMENT, 
		quizid mediumint(9) NOT NULL,
		question text NOT NULL,
		type text NOT NULL,
		answers text NOT NULL,
		results text NOT NULL,
		weight text NOT NULL,
		UNIQUE KEY id (id)
		);";
	$table_result = $wpdb->prefix . "dragon_quiz_result";
	$sql_result = "CREATE TABLE ".$table_result." (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
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
		'title' => 'My Quiz'
		));
	$wpdb->insert( $table_question, array( 
		'quizid' => 1, 
		'question' => 'What Is Your Favorite Fruit?', 
		'type' => 'radio', 
		'answers' => 'Apple, Blueberry', 
		'results' => '0,1',
		'weight' => '1,1' 
		));
	$wpdb->insert( $table_question, array( 
		'quizid' => 1, 
		'question' => 'What is Your Favorite Flower?', 
		'type' => 'radio', 
		'answers' => 'Violet, Rose', 
		'results' => '1,0',
		'weight' => '1,1' 
		));
	$wpdb->insert( $table_result, array( 
		'name' => 'Red', 
		'image' => WP_PLUGIN_DIR . '/custom-quiz-maker/ds-logo.png' , 
		'content' => '<p>Your Favorite Color is Red!</p>'
		));
	$wpdb->insert( $table_result, array( 
		'name' => 'Blue', 
		'image' => WP_PLUGIN_DIR . '/custom-quiz-maker/ds-logo.png' , 
		'content' => '<p>Your Favorite Color is Blue!</p>'
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
		$atts = get_quiz( $id );

		// Display the title of the Quiz
		echo "<h2 class='quiz-title'>";
			echo $atts['title'];
		echo "</h2>";

		// The entire list of quiz questions
		echo "<div class='quiz-questions'>";

			// Cycle through each Question ID in the list
			$ds_questions = get_questions( $id );
			foreach ($ds_questions as $ds_ask) {
				
				echo '<div class="one-question">';

					// Display the question contents
					echo '<h3>'.$ds_ask['question'].'</h3>';

					?>
						<form>
							<?php
								// Parse the data into an array
								$answerList = explode( ',' , $ds_ask['answers']);
								$resultList = explode( ',' , $ds_ask['results']);
								$weightList = explode( ',' , $ds_ask['weight']);

								// Cycle through each item in the answer array
								foreach ( $answerList as $key => $value ) {
									$answer = $answerList[$key];
									$result = $resultList[$key];
									$weight = $weightList[$key];
									echo '<input name="ds-quiz" value="result-' . $result . ' weight-' . $weight . '" type="'. $ds_ask['type'] .'" class="one-answer">'.$answer.'</input>';
								}
							?>
						</form>
					<?php
					
				echo '</div><!-- .one-question -->';
			}
		echo "</div><!-- .quiz-questions -->";

		/*
		echo "<div class='quiz-results'>";
			echo "<h2>Results:</h2>";
			$resultList = explode( ',' ,  $atts['results'] );
			foreach ($resultList as $oneResult) {
				$oneResult = get_results( $oneResult );
				echo "<div class='one-result'>"
					echo "<h3>" . $oneResult['name'] . "</h3>";
					echo "<img src='" . $oneResult['image'] . "' />";
					echo $oneResult['content'];
				echo "</div><!-- .one-result -->";
			}
		echo "</div><!-- .quiz-results -->";
		*/

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
		$atts = get_quiz_by_name( $input );
		return $atts['id'];
	}
}

// Get a quiz's information from the table by it's name.  
// =========================================================================
function get_quiz_by_name( $name ) {
	// Get the table data
	global $wpdb;
	$table_name = $wpdb->prefix . "dragon_quiz_list";
	$values = $wpdb->get_row( 'SELECT * FROM ' . $table_name . ' WHERE title = "' . $name . '"' , ARRAY_A  );
	return $values;
}
// Get a quiz's information from the table.  
// =========================================================================
function get_quiz( $id ) {
	// Get the table data
	global $wpdb;
	$table_name = $wpdb->prefix . "dragon_quiz_list";
	// If our ID is -1 then the user wants all of the quizzes.
	if($id == -1) {
		$values = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE 1" , ARRAY_A );
	}
	else {
		$values = $wpdb->get_row( "SELECT * FROM " . $table_name . " WHERE id = " . $id , ARRAY_A );
	}
	return $values;
}
// Get a question's information from the table.  
// =========================================================================
function get_questions( $id ) {
	// Get the table data
	global $wpdb;
	$table_name = $wpdb->prefix . "dragon_quiz_question";
	$values = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE quizid = " . $id  , ARRAY_A );
	return $values;
}
// Get a result's information from the table.  
// =========================================================================
function get_results( $id ) {
	// Get the table data
	global $wpdb;
	$table_name = $wpdb->prefix . "dragon_quiz_result";
	$values = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE id = " . $id  , ARRAY_A );
	return $values;
}

// Backend Graphical User Interface
// =========================================================================


// Displays a link to the options pane in the Settings submenu.
add_action('admin_menu', 'dragon_quiz_options');
function dragon_quiz_options() {  
    add_options_page("Custom Quiz Maker Settings", "Custom Quiz Maker", 'manage_options', "dragon-quiz-option", "dragon_quiz_display_options");  
}
// Displays the Options Pane
function dragon_quiz_display_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap">
		<form method="post" action="options.php">
			
			<a href="http://www.dragonsearchmarketing.com/" target="_blank">
				<img src="<?php echo plugins_url(); ?>/custom-quiz-maker/ds-logo.png" />
			</a>

			<h2>Custom Quiz Maker Settings</h2>
			
			<p>
				Create and customize your quizes, questions, answers, and results on your post's content. 
			</p>

			<style type="text/css">
				.setting {
					margin: 1em;
					padding: 0.5em;
					border: 1px solid #000000;
					border-radius: 10px;
					background-color: #CCC;
					display: block;
				}
				.setting .title {
					padding-left: 0.5em;
					border: 1px dotted #000000;
					border-radius: 10px;
					background-color: #FFF;
					display: inline-block;
					cursor: pointer;
				}
				.setting .option {
					margin: 0.5em;
					display: none;
					margin-left: 2em;
				}
				.setting .option .question {
					display: block;
					margin-left: 4em;
				}
				.setting .option .question input {
					width: 30em;
				}
				.setting .option .answer-and-weight {
					display: block;
					margin-left: 2em;
				}
				.setting .option .answer-and-weight .answer {
					width: 20em;
				}
				.setting .option .answer-and-weight .weight {
					width: 2em;
				}
				.add , .remove {
					display: inline-block;
					width: 17px;
					height: 17px;
					padding: 0;
					margin: 0.3em;
					border: 1px solid #000;
					border-radius: 10px;
					text-align: center;
					color: white;
					box-shadow: 3px 3px 4px #333;
					cursor: pointer;
					font-size: 20px;
				}
				.add {
					background-color: green;
				}
				.remove {
					background-color: red;
				}
			</style>
			<script type="text/javascript">
				function toggleDisplay(e) {
					// Display the child as a block
					var parent = e.parentNode;
					var child = parent.getElementsByClassName('option');
					
					if( child[0].style.display == "none" || child[0].style.display == "" ) {
						child[0].style.display = "block"; 
					}
					else {
						child[0].style.display = "none";
					}
				}
			</script>

			<?php 
				// Get all of the quizzes.
				$quizList = get_quiz( -1 );
				foreach ($quizList as $oneQuiz) {
					?>
						<div class="setting">
							<h2 onclick="toggleDisplay(this);" class="title">
								<?php echo $oneQuiz['title']; ?>
							</h2>
							<div class="remove">-</div>
							<div class="add" onclick="newQuiz();">+</div>
							<div class="option">
								<span>Name: </span>
								<input type="text" value="<?php echo $oneQuiz['title']; ?>" />
								<?php 
									// Get the Question List
									$questionList = get_questions( $oneQuiz['id'] ); 

									// For a single question
									foreach( $questionList as $oneQuestion ) {

										// Get each answer and it's weight
										$answerList = explode( ',' , $oneQuestion['answers'] );
										$weightList = explode( ',' , $oneQuestion['weight'] );
								?>
										<div class="question">
											<span>Question: </span>
											<input type="text" value="<?php echo $oneQuestion['question']; ?>" />
											<div class="remove">-</div>
											<div class="add">+</div>
								<?php
										// Iterate through the list of answers, display it's answer and weight
										foreach( $answerList as $key => $value) {
								?>
											<div class="answer-and-weight">
												<span>Answer: </span>
												<input class="answer" type="text" value="<?php echo $answerList[$key]; ?>"> </input>
												<span>Weight: </span>
												<input class="weight" type="text" value="<?php echo $weightList[$key]; ?>"> </input>
												<div class="remove">-</div>
												<div class="add">+</div>
											</div><!-- .answer-and-weight-->

								<?php
										}
								?>
										</div><!-- .question -->
								<?php
									}
								?>
							</div><!-- .option -->
						</div><!-- .setting -->

					<?php
				}
			?>
			<div class="add">+</div>

			<p>
				<span class="description">Want your customized quiz elsewhere on your page?  Just call this php function on your template: get_dragon_quiz();</span>
			</p>
			We'd love to hear from you! 
			Contact us on <a href="https://twitter.com/dragonsearch" target='_blank'>Twitter</a>, <a href="http://www.facebook.com/DragonSearch" target='_blank'>Facebook</a> or visit us at <a href="http://www.dragonsearchmarketing.com/" target='_blank'>Dragonsearchmarketing.com</a>

			<a href="https://twitter.com/dragonsearch" target='_blank'></a>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
	<?php
}