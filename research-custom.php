<?php namespace cahnrswp\cahnrs\research;
/**
 * Plugin Name: CAHNRS Office of Research
 * Plugin URI:  http://cahnrs.wsu.edu/communications/
 * Description: Additional Features for the Office of Research
 * Version:     1.1
 * Author:      CAHNRS Communications, Danial Bleile
 * Author URI:  http://cahnrs.wsu.edu/communications/
 * License:     Copyright Washington State University
 * License URI: http://copyright.wsu.edu
 */

class cahnrs_or {


	public function __construct() {
		$this->define_constants(); // Define constants
		$this->init_autoload(); // Activate custom autoloader for classes
	}

	private function define_constants() {
		define( __NAMESPACE__ . '\URL', plugin_dir_url( __FILE__ )  ); // Plugin base URL
		define( __NAMESPACE__ . '\DIR', plugin_dir_path( __FILE__ ) ); // Directory path
	}

	private function init_autoload() {
		require_once 'controls/autoload_control.php'; // Require autoloader control
		$autoload = new autoload_control(); // Init autoloader to eliminate further dependency on require
	}

	public function init_plugin() {
		\add_action( 'init', array( $this , 'create_rfp_post_type' ) );
		\add_action( 'add_meta_boxes', array( $this , 'add_rfp_metabox' ) );
		\add_action( 'save_post', array( $this , 'save_rfp_metabox' ) );
		if ( !is_admin() ){
			\add_action( 'template_redirect', array( $this , 'rfp_redirect' ) );
		}
		//$widgets = new widget_control();
		//$scripts = new script_control();
		//$taxonomy = new taxonomy_control();
		//$feeds = new feed_control();
		//$feeds->init_feed_control();
		//$metabox = new metabox_control();
		//$metabox->init();
		/*********************************************
		** ADD CUSTOM POST TYPES **
		*********************************************/
		//$post_types = new post_type_control();
		
		//\add_action( 'init', array( $post_types , 'register_post_types' ) );
	}
	
	public function create_rfp_post_type() {
		register_post_type( 'rfp',
			array(
			  'labels' => array(
				'name' => __( 'RFPs' ),
				'singular_name' => __( 'RFP' )
			  ),
			'public' => true,
			'has_archive' => true,
			'supports' => array( 'title', 'editor' ),
			)
		);
	}
	
	public function add_rfp_metabox() {
		$screens = array( 'rfp' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				'rfp_settings',
				__( 'RFP SETTINGS' ),
				array( $this , 'render_rfp_metabox' ),
				$screen
			);
		}
	}
	
	public function rfp_redirect(){
		 global $post;
		 $redirect_types = array( 'rfp');
		 if( in_array( $post->post_type , $redirect_types ) && ( is_singular('rfp') ) && is_main_query() ){
			 $meta = \get_post_meta( $post->ID , '_redirect_to' , true );
			 if( $meta ){
				 \wp_redirect( $meta , 302 );
			 }
		 }
	 }
	
	public function render_rfp_metabox( $post ){
		$date = \get_post_meta( $post->ID , '_post_date', true );
		$date = ( $date )? date( 'm', $date ).'/'.date( 'd', $date ).'/'.date( 'y', $date ) : $date;
		$redirect = \get_post_meta( $post->ID , '_redirect_to', true );
		echo '<div class="or_input_wrap" style="width: 17%; display: inline-block; margin-right: 2%;">';
			echo '<label>Due Date: ( m/d/y )</label><br />';
			echo '<input value="'.$date.'" class="datepicker" type="text" name="_post_date" style="width: 90%;"/>';
		echo '</div>';
		echo '<div class="or_input_wrap" style="width: 80%; display: inline-block; ">';
			echo '<label>Link To: ( Redirect )</label><br />';
			echo '<input value="'.$redirect.'" type="text" name="_redirect_to" style="width: 90%;" />';
		echo '</div>';
	}
	
	public function save_rfp_metabox( $post_id ) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */
	
		// Check if our nonce is set.
		//if ( ! isset( $_POST['myplugin_meta_box_nonce'] ) ) {
			//return;
		//}
	
		// Verify that the nonce is valid.
		//if ( ! wp_verify_nonce( $_POST['myplugin_meta_box_nonce'], 'myplugin_meta_box' ) ) {
			//return;
		//}
	
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
	
		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
	
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
	
		} else {
	
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}
	
		/* OK, it's safe for us to save the data now. */
		
		// Make sure that it is set.
		$inputs = array( '_post_date' , '_redirect_to' );
		foreach ( $inputs as $input ){
			if ( isset( $_POST[ $input ] ) ) {
				$the_data = sanitize_text_field( $_POST[ $input ] );
				if( '_post_date' == $input ){
					$the_data = strtotime( $the_data );
				}
				// Sanitize user input.
				// Update the meta field in the database.
				\update_post_meta( $post_id, $input, $the_data );
			}
		}
	}

}

$cahnrs_or = new cahnrs_or();
$cahnrs_or->init_plugin();
?>