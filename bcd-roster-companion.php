<?php
/*
	Plugin Name: BCD Roster Companion
	Plugin URI: http://blog.duhjones.com/
	Description: Adds custom fields to the BCD Roster custom post type along with a new shortcode for displaying a new roster
	Author: Frank Jones
	Version: 1.0
	Author URI: http://blog.duhjones.com/
*/


// ----------------------------------------------------------------------------------------------------
// Set the plugin's url to a variable

define ( 'BCDRC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


// ----------------------------------------------------------------------------------------------------
// Deactivate this plugin if BCD Roster is not active

add_action( 'admin_init', 'bcdrc_requires_bcd_roster' );

function bcdrc_requires_bcd_roster() {
	$plugin_bcd_roster = 'bcd-roster/bcd-roster.php';
	$plugin = plugin_basename( __FILE__ );

	$plugin_data = get_plugin_data( __FILE__, false );
	
	if ( !is_plugin_active( $plugin_bcd_roster ) ) {
		deactivate_plugins ( $plugin );
		wp_die( '<strong>' . $plugin_data['Name'] . '</strong> requires <strong>BCD Roster</strong> and has been deactivated! Please activate <strong>BCD Roster</strong> and try again.<br /><br />Back to the WordPress <a href="' . get_admin_url( null, 'plugins.php' ) . '">Plugins page</a>.' );
	}
}


// ----------------------------------------------------------------
// Only load admin function if user is an admin

if ( is_admin() ) {
	require_once ( dirname( __FILE__ ) . '/includes/bcdrc-admin.php' );
}


// ----------------------------------------------------------------------------------------------------
// Register the styles used by the plugin

// Styles are now loaded at the time they are needed
function bcdrc_enqueue() {
	wp_enqueue_style( 'bcdrc-css', BCDRC_PLUGIN_URL . 'css/bcdrc-css.css', false );
}


// ----------------------------------------------------------------------------------------------------
// Add shortcode to display roster members

add_shortcode( 'bcdrc', 'bcdrc_sc_rostercompanion' );
function bcdrc_sc_rostercompanion( $atts ) {
	bcdrc_enqueue();
	
	extract( shortcode_atts( array(
		'showpicture' => 'yes',
		'sortorder' => 'asc'
	), $atts ) );
	
	$output = '';

	global $post;
	$tmp_post = $post;
	
	$qry_args = array(
		'post_type' => 'bcd_cpt_member',
		'orderby' => 'title',
		'posts_per_page' => -1
	);
	
	if ( 'desc' == $sortorder )
		$qry_args['order'] = 'DESC';
	else
		$qry_args['order'] = 'ASC';
	
	$my_query = new WP_Query( $qry_args );
	
	$output .= '<div id="bcdrc_roster">';
		
	while ( $my_query->have_posts() ) {
		$my_query->the_post();
		$post_custom = get_post_custom( $post->ID );
		
		$jobtitle = $post_custom['bcdrc_member_info_jobtitle'][0];
		$city = $post_custom['bcdrc_member_info_city'][0];
		$state = $post_custom['bcdrc_member_info_state'][0];
		$link = $post_custom['bcdrc_member_info_link'][0];
		$quote = $post_custom['bcdrc_member_info_quote'][0];
		
		$output .= '<div class="bcdrc-member">';

		if ( 'yes' == $showpicture ) {
			if ( has_post_thumbnail() ) {
				$post_images = wp_get_attachment_image_src( get_post_thumbnail_id() );
				$img = $post_images[0];
			}
			else {
				$img = BCDRC_PLUGIN_URL . '/images/image-placeholder.jpg';
			}
			$output .= '<img src="'. $img .'" align="right" />';
		}
		
		$output .= '<div class="bcdrc-member-info-title">' . get_the_title() . '</div>';
		
		if ( '' == $jobtitle ) {
			$output .= '<div class="bcdrc-member-info-jobtitle">&nbsp;</div>';
		}
		else {
			$output .= '<div class="bcdrc-member-info-jobtitle">' . $jobtitle . '</div>';
		}
		
		if ( '' == $city ) {
			if ( '' == $state ) {
				$output .= '<div class="bcdrc-member-info-citystate">&nbsp;</div>';
			}
			else {
				$output .= '<div class="bcdrc-member-info-citystate">' . $state . '</div>';
			}
		}
		else {
			$output .= '<div class="bcdrc-member-info-citystate">' . $city . ', ' . $state . '</div>';
		}
		
		if ( '' == $link ) {
			$output .= '<div class="bcdrc-member-info-link">&nbsp;</div>';
		}
		else {
			$output .= '<div class="bcdrc-member-info-link"><a href="http://' . $link . '" target="_blank">' . $link . '</a></div>';
		}
		$output .= '<blockquote>&ldquo;' . $quote . '&rdquo;</blockquote>';
		$output .= '' . get_the_content() . '';
		$output .= '<hr />';
		
		$output .= '</div>';
	}
	
	wp_reset_query();
	
	$output .= '</div>';
	
	return $output;
}

?>
