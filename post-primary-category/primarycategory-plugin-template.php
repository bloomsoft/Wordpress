<?php
/*
 * Plugin Name: BloomSoft Post Primary Category
 * Version: 1.0
 * Plugin URI: http://www.bloomsoft.net/
 * Description: This plugin will let you select a category as Primary category of Post.
 * Author: Muhammad Zakria
 * Author URI: http://www.bloomsoft.net/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: post-primary-category
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Muhammad Zakria
 * @since 1.0.0
 */


//*****************************************************************************//
// META BOX RELATED CODE SEGMENTS //
//******************* STARTS HHERE ********************************//

/**
 * Register meta box(es).
 */
function wpdocs_register_meta_boxes() {
    add_meta_box( 'meta-box-id', __( 'Primary category of post', 'textdomain' ), 'wpdocs_my_display_callback', 'post' );    // FOR DEFAULT POST TYPE
	add_meta_box( 'meta-box-id', __( 'Primary category of post', 'textdomain' ), 'wpdocs_my_display_callback', 'product' );  // FOR DEFAULT PRODUCT TYPE CUSTOM POST IF WE HAVE WOOCOMM INSTALLED
	
}
add_action( 'add_meta_boxes', 'wpdocs_register_meta_boxes' );
 
 /**
 * Dropdown list generator to show categories in the MetaBox
 **/

 function get_terms_dropdown($taxonomies, $args, $defaultselect){
    $myterms = get_terms($taxonomies, $args);
    $output ="<select name='cat'>";
    foreach($myterms as $term){
        $root_url = get_bloginfo('url');
        $term_taxonomy=$term->taxonomy;
        $term_slug=$term->slug;
        $term_name =$term->name;
		$term_id =$term->term_id;
        $link = $term_slug;
		if ($defaultselect!=''){
			if ($defaultselect==$term_id){
				$output .="<option value='".$term_id."' selected>".$term_name."</option>";
			}
			else{
				$output .="<option value='".$term_id."'>".$term_name."</option>";
			}
		}
		else{
			$output .="<option value='".$term_id."'>".$term_name."</option>";
		}
    }
    $output .="</select>";
return $output;
}

/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */

function wpdocs_my_display_callback( $post ) {
    // Display code/markup goes here. Don't forget to include nonces!
	 // Add an nonce field so we can check for it later.
        wp_nonce_field( 'meta-box-id', 'meta_box_id_nonce' );
 
        // Use get_post_meta to retrieve an existing value from the database.
        $value = get_post_meta( $post->ID, '_my_meta_value_key', true );
 
        // Display the form, using the current value.
		
        ?>
		
		<?php
		global $wpdb;
		// this adds the prefix which is set by the user upon instillation of wordpress
		
		$retrieve_data = $wpdb->get_results( "SELECT * FROM `".$wpdb->prefix."postprimarycatg` where `post_id` = " . $post->ID);
		$primcatgid=0;
		foreach ($retrieve_data as $retrieved_data){
		$primcatgid=$retrieved_data->term_id;
		}	
		
		$terms1 = get_terms('primarycategory', 'hide_empty=0'); ?>
		
        <span><?php _e( 'Select primary category of this post', 'textdomain' ); ?><?php $terms1 ?></span>
			<?php 
			//wp_dropdown_categories( 'show_option_none=Select primary category' ); 
			//wp_dropdown_categories ( array( 'selected' => $primcatgid, 'show_option_none' => "Select $tax_name" ) );
			echo get_terms_dropdown($taxonomies, $args, $primcatgid);
			
}
 
/**
 * Save meta box content.
 *
 * @param int $post_id Post ID
 */
function wpdocs_save_meta_box( $post_id ) {
    // Save logic goes here. Don't forget to include nonce checks!
	
	if ( !wp_verify_nonce( $_POST['meta_box_id_nonce'], 'meta-box-id' ) )
	{
		return;
	}
	
	if( isset( $_POST['cat'] ) )
	{
		if ($_POST['cat']!='-1'){
			global $wpdb;
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	   
			$del_sql ="DELETE FROM `".$wpdb->prefix."postprimarycatg` where `post_id`=".$post_id;

			//dbDelta($del_sql);
			
			$wpdb->query($del_sql);
			$bbb=true;
			if( isset( $_GET['post'] ) ){
				if( $_GET['post'] != $post_id) {
					$bbb=false;
				}
			}
			if ($bbb==true){
				$insert_sql ="INSERT INTO `".$wpdb->prefix."postprimarycatg` (`term_id`,`post_id`) VALUES (".$_POST['cat'].",".$post_id.")";

				dbDelta($insert_sql);
			}
		}
	}
		
}
add_action( 'save_post', 'wpdocs_save_meta_box' );

//***************************ENDS HHERE***********************************//
// META BOX RELATED CODE SEGMENTS //
//*****************************************************************************//



//*****************************************************************************//
// SHORTCODE RELATED CODE SEGMENTS //
//******************* STARTS HHERE ********************************//
//*****SHORT CODE EXAMPLE******//
// [pcatg catgid="<catgid will come here>"][/pcatg] //
//****************************//

function pcatg_shortcode($atts = [], $content = null, $tag = '')
{
	global $wpdb;
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);
 
    // override default attributes with user attributes
    $wporg_atts = shortcode_atts([
                                     'catgid' => 'WordPress.org',
                                 ], $atts, $tag);
 
    // start output
    $o = '';
 
    // start box
    $o .= '<div class="wporg-box">';
 
	$querystr = "SELECT $wpdb->posts.* FROM $wpdb->posts, ".$wpdb->prefix."postprimarycatg WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_date < NOW() AND $wpdb->posts.ID=".$wpdb->prefix."postprimarycatg.post_id AND ".$wpdb->prefix."postprimarycatg.term_id=" . esc_html__($wporg_atts['catgid'], 'wporg');
 
	$pageposts = $wpdb->get_results($querystr, OBJECT);
	
	
	$o .= '<ul>';
  
  if ($pageposts):
   global $post; 
   foreach ($pageposts as $post): 
		
		$o .= '<li><a href="' . esc_url( get_permalink($post->ID) ) . '" rel="bookmark">' . $post->post_title . '</a></li>';
		
   endforeach;	
  else :
		$o .= '<h2 class="center">Not Found</h2>';
  endif;

    $o .= '</ul>';
    
    // end box
    
	$o .= '</div>';
	
    // return output
    return $o;
}
 
function pcatg_shortcodes_init()
{
    add_shortcode('pcatg', 'pcatg_shortcode');
}
 
add_action('init', 'pcatg_shortcodes_init');

//***************************ENDS HHERE***********************************//
// SHORT CODE RELATED CODE SEGMENTS //
//*****************************************************************************//



if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-primarycategory-plugin-template.php' );
require_once( 'includes/class-primarycategory-plugin-template-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-primarycategory-plugin-template-admin-api.php' );
require_once( 'includes/lib/class-primarycategory-plugin-template-post-type.php' );
require_once( 'includes/lib/class-primarycategory-plugin-template-taxonomy.php' );

/**
 * Returns the main instance of WordPress_Plugin_Template to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object WordPress_Plugin_Template
 */
function PrimaryCategory_Plugin_Template () {
	$instance = PrimaryCategory_Plugin_Template::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = PrimaryCategory_Plugin_Template_Settings::instance( $instance );
	}

	return $instance;
}

PrimaryCategory_Plugin_Template();