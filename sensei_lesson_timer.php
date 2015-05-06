<?php
/**
 * Plugin Name: Sensei Lesson Timer
 * Plugin URI: http://www.skinnycatsoftware.com
 * Description: This plugin adds timer to the Sensei lessons and enable/disable complete button.
 * Version: 1.1
 * Author: Skinny Cat Software, LLC
 * Author URI: http://www.skinnycatsoftware.com
 */
 ?>
<?php

add_action("wp_head", "slt_header");
 
function slt_header(){
	
	if (is_single() || is_page()) {
		global $post;
		$slt_timer = intval(get_post_meta( $post->ID, "_lesson_length", true));
	
		if($slt_timer > 0) {
			?>
			<style type='text/css'>
			#slt_timer{
				background:rgb(155, 158, 27);
				border:2px solid grey;
				font-size:18px;
				color:white;
				width:auto;
				height:auto;
				float:left;
				font-family:arial;
				margin-left:10px;
			}
	
			#slt_minutes, #slt_seconds{
				background:black;
				border:1px solid grey;
				color:white;	
				font-weight:bold;
				margin:2px;
				padding:2px;
				float:left;
			}
			#slt_separator{
				color:blue;
				font-weight:bold;
				float:left;
				padding: 4px 0px;	
			}
			</style>
	
			<script type='text/javascript'>
			jQuery(document).ready(function($){
				if ($('input[name=quiz_complete]').length) {
					<?php 
						$slt_warning_message = get_option('slt_warning_message', '');
						if (!empty($slt_warning_message)) {
							?>
							window.onbeforeunload = function() {
								return '<?php echo addslashes($slt_warning_message) ?>';
							}
							<?php
						}
					?>
				
					$('input[name=quiz_complete]').css('float', 'left');
					var countdown =  <?php echo $slt_timer ?>  * 60 * 1000;
					var min = 0;
					var sec = 0;
					$("<div id='slt_timer'></div>").insertAfter( 'input[name=quiz_complete]' );
					var timerId = setInterval(function(){
						countdown -= 1000;
						min = Math.floor(countdown / (60 * 1000));
						//var sec = Math.floor(countdown - (min * 60 * 1000));  // wrong
						sec = Math.floor((countdown - (min * 60 * 1000)) / 1000);  //correct

						if (countdown <= 0) {
							$('#slt_timer').hide(); 
							$('input[name=quiz_complete]').removeAttr('disabled');
							//alert('30 min!');
							clearInterval(timerId);
						
							// Clear our warning mesage if the user leaves
							window.onbeforeunload = null;
							console.log('window.onbeforeunload has ben unset');
						
							//doSomething();
						} else {
							$('#slt_timer').html("<span id='slt_minutes'>" + min + "</span><span id='slt_separator'>:</span><span id='slt_seconds'>" + sec + "</span>");
							$('input[name=quiz_complete]').attr('disabled','disabled');
						
							if (window.onbeforeunload == null) {
								console.log('window.onbeforeunload is null');
							} else {
								console.log('window.onbeforeunload NOT null');
							}
						}
					}, 1000); //1000ms. = 1sec
				}
			});
			</script>
			<?php
		}
	}
}

function slt_settings_api_init() {
 	add_settings_section(
		'slt_setting_section',
		'Sensei Lesson Timer',
		'slt_setting_section_callback_function',
		'reading'
	);
 	
 	add_settings_field(
		'slt_warning_message',
		'Warning Message',
		'slt_setting_callback_function',
		'reading',
		'slt_setting_section'
	);
 	
 	register_setting( 'reading', 'slt_warning_message' );
 } 
 
 add_action( 'admin_init', 'slt_settings_api_init' );
   
function slt_setting_section_callback_function() {
	echo '<p>This following options control the Sensei Lesson Timer</p>';
}
 
function slt_setting_callback_function() {
	echo '<input name="slt_warning_message" id="slt_warning_message" type="text" value="'. get_option('slt_warning_message', '')  .'" class="large-text" /><br /><span class="description">The above message will be shown when the user attempts to leave the page where an active time is still running. Leave this message empty to disable the warning message.';
}
