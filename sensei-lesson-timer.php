<?php
/*
Plugin Name: Sensei Lesson Timer
Plugin URI: http://www.skinnycatsoftware.com
Description: This plugin adds timer to the Sensei lessons and enable/disable complete button.
Version: 1.1.1-20150514B
Author: Skinny Cat Software, LLC
Author URI: http://www.skinnycatsoftware.com
Text Domain: sensei-lesson-timer
Domain Path: /languages
*/
 ?>
<?php
if ( !class_exists( 'Sensei_Lesson_Timer' ) ) {
	
	class Sensei_Lesson_Timer {

		// Just a object-level flag to control between functions itf we are showing the timer or not. 
		var $_process_timer = false;
		
		var $version	= '1.0';
	
		// Contains the reference path to the plugin root directory. Used when other included plugin files 
		// need to include files relative to the plugin root.
		var $plugin_dir;
		
		// Contains the reference url to the plugin root directory. Used when other included plugin files 
		// need to refernece CSS/JS via URL
		var $plugin_url; 

		var $localize_data = array();

		// These are the post_types registered by Sensei. We start there. There is a filter where the user can add custom post types. 
		//var $sensei_post_types = array( 'course', 'lesson', 'quiz', 'question', 'multiple_question', 'sensei_message' );
		var $sensei_post_types = array( 'lesson', 'quiz' );
		
		// Used to hold the post_type slug => post_type Label set to pass to sensei settings 
		var $sensei_post_type_items = array();
		
		// Contains the role_slug => Role Name used for the Disable by Role on the Sensei settings
		var $roles = array();
		
		function __construct() {

			$this->plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) );
			$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );

			add_action( 'init', 					array( $this, 'init' ) );
			add_action( 'wp_head', 					array( $this, 'wp_head' ) );
			add_action( 'wp_enqueue_scripts', 		array( $this, 'wp_enqueue_scripts' ) );
			add_action( 'get_footer', 				array( $this, 'get_footer' ) );

			add_filter( 'sensei_settings_tabs', 	array( $this, 'sensei_settings_tabs' ) );
			add_filter( 'sensei_settings_fields', 	array( $this, 'sensei_settings_fields' ) ); 
			
			//register_activation_hook( __FILE__, array( $this, 'install' ) );
		}
		
		function init() {
			if (is_admin()) return;
			
			load_plugin_textdomain( 'sensei-lesson-timer', false, dirname( plugin_basename( __FILE__ ) ) .'/languages/' );
		}				

		function wp_enqueue_scripts() {
			if (is_admin()) return;
			
			wp_enqueue_script (
				'sensei-lesson-timer-js', 
				$this->plugin_url .'js/sensei-lesson-timer.js',
				array( 'jquery' ),
				$this->version,
				true
			);
			
			wp_enqueue_style (
				'sensei-lesson-timer-css', 
				$this->plugin_url .'css/sensei-lesson-timer.css',
				array( ),
				$this->version
			);
		}
		
		function wp_head() {
			global $woothemes_sensei;
			
			if (is_admin()) return;
			
			if ( ( !is_single() ) && ( !is_page() ) ) return;

			$queried_object = get_queried_object();
			
			$this->post_types = array('lesson');
			if ( isset( $woothemes_sensei->settings->settings[ 'slt_setting_post_types' ] ) ) {
				$this->post_types = array_values( $woothemes_sensei->settings->settings[ 'slt_setting_post_types' ] );
			} 
			$this->post_types = apply_filters('slt_setting_post_types', $this->post_types);
							
			if ((!isset($queried_object->post_type)) || (array_search($queried_object->post_type, $this->post_types) === false)) {
				return;
			}


			$disable_for_role = array();
			if ( isset( $woothemes_sensei->settings->settings[ 'slt_setting_disable_by_roles' ] ) ) {
				$disable_for_role = array_values( $woothemes_sensei->settings->settings[ 'slt_setting_disable_by_roles' ] );
			} 
			$disable_for_role = apply_filters('slt_setting_disable_for_role', $disable_for_role);
			$user_role = $this->get_user_role();
			if ((!empty($user_role)) && (!empty($disable_for_role))) {
				if (array_search($user_role, $disable_for_role) !== false) {
					return;
				}
			}


					
			$slt_timer = intval(get_post_meta( $queried_object->ID, "_lesson_length", true));
			$slt_timer = apply_filters('slt_setting_timer_countdown', $slt_timer);
			$slt_timer = intval($slt_timer);
			if (empty($slt_timer)) return;
			$this->localize_data['lesson_length'] = $slt_timer;
						
			$slt_warning_message = '';
			if ( isset( $woothemes_sensei->settings->settings[ 'slt_setting_warning_message' ] ) ) {
				$slt_warning_message = $woothemes_sensei->settings->settings[ 'slt_setting_warning_message' ];
			}
			$this->localize_data['unload_message'] = apply_filters('slt_setting_warning_message', $slt_warning_message);


			$auto_complete = false;
			if ( isset( $woothemes_sensei->settings->settings[ 'slt_setting_auto_complete' ] ) ) {
				$auto_complete = $woothemes_sensei->settings->settings[ 'slt_setting_auto_complete' ];
			}
			$this->localize_data['auto_complete'] = apply_filters('slt_setting_auto_complete', $auto_complete);


			$pause_on_unfocus = false;
			if ( isset( $woothemes_sensei->settings->settings[ 'slt_setting_pause_on_unfocus' ] ) ) {
				$pause_on_unfocus = $woothemes_sensei->settings->settings[ 'slt_setting_pause_on_unfocus' ];
			}
			$this->localize_data['pause_on_unfocus'] = apply_filters('slt_setting_pause_on_unfocus', $pause_on_unfocus);
			
			
			$placement = 'outside-right';
			if ( isset( $woothemes_sensei->settings->settings[ 'slt_setting_placement' ] ) ) {
				$placement = $woothemes_sensei->settings->settings[ 'slt_setting_placement' ];
			}

			if (($placement == 'outside-left') || ($placement == 'outside-right')) {
				$slt_form_element_outside_spacer						= 	' ';
				$this->localize_data['form_element_outside_spacer'] 	= 	apply_filters('slt_setting_outside_spacer', $slt_form_element_outside_spacer);
			}

			if (($placement == 'inside-left') || ($placement == 'inside-right')) {
				$slt_form_element_inside_spacer	= ' - ';
				$this->localize_data['form_element_inside_spacer'] 	= 	apply_filters('slt_setting_inside_spacer', $slt_form_element_inside_spacer);
			}
			
			$this->localize_data['placement'] = apply_filters('slt_setting_placement', $placement);
			
			
			$this->localize_data['form_elements'] = array();
			
			if (array_search('lesson', $this->post_types) !== false) {
				$this->localize_data['form_elements'][] = 'section.lesson-meta form.lesson_button_form input.complete[name="quiz_complete"]';
				$this->localize_data['form_elements'][] = '#lesson_complete a.button[title="View the lesson quiz"]';
			}
			if (array_search('quiz', $this->post_types) !== false) {
				$this->localize_data['form_elements'][] = 'section.entry div.lesson-meta form input.quiz-submit[name="quiz_complete"]';
			}
			$this->localize_data['form_elements'] = apply_filters('sensei_lesson_timer_form_elements', $this->localize_data['form_elements']);
			
			$this->_process_timer = true;
		}
				
		function get_footer() {
			
			if ($this->_process_timer == true) {
				wp_localize_script( 'sensei-lesson-timer-js', 'sensei_lesson_time_plugin_data', $this->localize_data );
			} else {
				wp_deregister_script( 'sensei-lesson-timer-js' );
				wp_deregister_style( 'sensei-lesson-timer-css' );
			} 
		}
		
		function sensei_settings_tabs( $sections ) {
			if (!isset( $sections['sensei-lesson-timer'] ) ) {
				$sections['sensei-lesson-timer'] = array(
					'name' 			=> __( 'Lesson Timer', 'sensei-lesson-timer' ),
					'description'	=> __( 'Sensei Lesson Timer Settings.', 'sensei-lesson-timer' )
				);
			}
			
			return $sections;
		}
		
		function sensei_settings_fields( $fields ) {
			//echo "fields<pre>"; print_r($fields['course_page']); echo "</pre>";
						
			$fields['slt_setting_auto_complete'] = array(
				'name' 			=> 	__('Auto Complete', 'sensei-lesson-timer'),
				'description' 	=> 	__('Auto-Complete the Lesson when the timer reaches zero', 'sensei-lesson-timer'),
				'section' 		=> 	'sensei-lesson-timer',
				'type' 			=> 	'checkbox',
				'default' 		=> 	false,
			);

			$fields['slt_setting_pause_on_unfocus'] = array(
				'name' 			=> 	__('Pause Timer', 'sensei-lesson-timer'),
				'description' 	=> 	__('Pause the Lesson Timer when the browser is not being viewed. This can help prevent the user from switching to another browser window while the timer counts down. This is only effective for modern browsers.', 'sensei-lesson-timer'),
				'section' 		=> 	'sensei-lesson-timer',
				'type' 			=> 	'checkbox',
				'default' 		=> 	true,
			);

			$fields['slt_setting_placement'] = array(
				'name' 			=> 	__('Timer Placement', 'sensei-lesson-timer'),
				'description' 	=> 	__("Controls where the Lesson Timer will be displayed in relation to the 'Complete Lesson' button.", 'sensei-lesson-timer'),
				'section' 		=> 	'sensei-lesson-timer',
				'type' 			=> 	'select',
				'default' 		=> 	'outside-right',
				'required' 		=> 	0,
				'options' 		=> 	array(
										'outside-right' 	=> 	__('Disable Button, Timer right of button', 'sensei-lesson-timer') .' ('. __('default', 'sensei-lesson-timer') .')',
										'outside-left' 		=> 	__('Disable Button, Timer left of button', 'sensei-lesson-timer'),
										'outside-replace' 	=> 	__('Hide Button, Show Timer', 'sensei-lesson-timer'),
										'inside-right' 		=> 	__('Add Timer to Right of Button Text', 'sensei-lesson-timer'),
										'inside-left' 		=> 	__('Add Timer to Left of Button Text', 'sensei-lesson-timer'),
										'inside-replace' 	=> 	__('Replace Button Text with Timer', 'sensei-lesson-timer'),
									)
			);
						
			$fields['slt_setting_warning_message'] = array(
				'name' 			=> 	__( 'Warning Message', 'sensei-lesson-timer' ),
				'description' 	=> 	__('Message shown when the user attempts to leave the page where an active time is running. Leave blank to disable the warning message. This message will show in most modern browsers except Firefox.', 'sensei-lesson-timer'),
				'section' 		=> 	'sensei-lesson-timer',
				'type' 			=> 	'textarea',
				'default' 		=> 	'',
				'required' 		=> 	0
			);
			
			$this->load_sensei_post_type_items();
			$this->sensei_post_type_items = apply_filters('slt_setting_post_types_options', $this->sensei_post_type_items);
			if (!empty($this->sensei_post_type_items)) {
				$fields['slt_setting_post_types'] = array(
					'name' 			=> 	__( 'Add Lesson Timer to the which Sensei items', 'sensei-lesson-timer' ),
					'description' 	=> 	__( 'The Lesson Timer will be displayed on the following Sensei items', 'sensei-lesson-timer' ),
					'section' 		=> 	'sensei-lesson-timer',
					'type' 			=> 	'multicheck',
					'options' 		=> 	$this->sensei_post_type_items,
					'defaults' 		=> 	array( 'lesson' ),
				);
			}
			
			$this->load_user_roles();
			$this->roles = apply_filters('slt_setting_disable_by_roles_options', $this->roles);
			if (!empty($this->roles)) {
				$fields['slt_setting_disable_by_roles'] = array(
					'name' 			=> 	__( 'Disable Lesson Timer by Role', 'sensei-lesson-timer' ),
					'description' 	=> 	__( 'The Lesson Timer can be disable by specific user roles. By default the Lesson Time is shown to all user roles.', 'sensei-lesson-timer' ),
					'section' 		=> 	'sensei-lesson-timer',
					'type' 			=> 	'multicheck',
					'options' 		=> 	$this->roles,
					'defaults' 		=> 	array( ),
				);
			}
			
			return $fields;
		}
		
		function load_sensei_post_type_items() {
			if (empty($this->sensei_post_types)) return;
			
			$queried_post_types = get_post_types(
				array(
					'public' 				=> 	true,
					'publicly_queryable'	=>	true,
				), 
				'objects',
				'and'
			);
			
			if (empty($queried_post_types)) return;
			
			$this->sensei_post_type_items = array();
			foreach($queried_post_types as $post_type_slug => $post_type) {
				if (array_search($post_type_slug, $this->sensei_post_types) !== false) {
					$this->sensei_post_type_items[$post_type_slug] = $post_type->label;
				}
			}
		}

		function load_user_roles() {
			
			if (!function_exists('get_editable_roles')) {
				include (ABSPATH .'wp-admin/includes/user.php');
			}
			
			$roles = get_editable_roles();
			if (empty($roles)) return;
			//echo "roles<pre>"; print_r($roles); echo "</pre>";
			
			$this->roles = array();
			foreach($roles as $role_slug => $role) {
				$this->roles[$role_slug] = $role['name'];
			}
		}

		function get_user_role( ) {

			$user_id 	= get_current_user_id();
			if (!empty($user_id)) {
				$data 		= get_userdata($user_id);
		    	$role 		= array_shift(array_keys($data->wp_capabilities));
				return $role;
			}
		}

		
		/*
		function install() {
			
			// IF the user was running Sensei Lesson Timer 1.1.0 then we transfer the previously stored
			// get_option setting 'slt_warning_message' into the Sensei settings structure. Play nice!
			$woothemes_sensei_settings = get_option('woothemes-sensei-settings', array());
			if (!isset($woothemes_sensei_settings['slt_setting_warning_message'])) {
				$slt_warning_message = get_option('slt_warning_message');
				if ($slt_warning_message !== false) { 
					$woothemes_sensei_settings['slt_setting_warning_message'] = $slt_warning_message;
					$update_ret = update_option('woothemes-sensei-settings', $woothemes_sensei_settings);
					if ($update_ret === true) {
						delete_option('slt_warning_message');
					}
				}
			}
		}
		*/
	}
		
	$sensei_lesson_timer = new Sensei_Lesson_Timer;
}
 

 
   
