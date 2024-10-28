<?php
/*
Admin hooks and functions for the BCD Roster Companion
This file is loaded conditionally based on is_admin().
*/


// ----------------------------------------------------------------------------------------------------
// Create the meta box that will contain the custom fields

add_action( 'add_meta_boxes', 'bcdrc_mb_create_member_info' );
function bcdrc_mb_create_member_info() {
	add_meta_box(
		'bcdrc_member_info',
		__( 'Member Info', 'bcdrc_td_member_info' ),
		'bcdrc_populate_member_info',
		'bcd_cpt_member'
	);
}


// ----------------------------------------------------------------------------------------------------
// Display the custom fields and populate them with any saved information

function bcdrc_populate_member_info( $post ) {
	wp_nonce_field( plugin_basename( __FILE__ ), 'bcdrc_nf_member_info' );
	
	$post_custom = get_post_custom( $post->ID );
	$jobtitle = $post_custom['bcdrc_member_info_jobtitle'][0];
	$city = $post_custom['bcdrc_member_info_city'][0];
	$state = $post_custom['bcdrc_member_info_state'][0];
	$link = $post_custom['bcdrc_member_info_link'][0];
	$quote = $post_custom['bcdrc_member_info_quote'][0];
	?>
	<div>
		<table>
			<tr>
				<td><strong>Title</strong></td>
				<td><input type="text" id="bcdrc_member_info_jobtitle" name="bcdrc_member_info_jobtitle" size="35" value="<?php echo $jobtitle; ?>" /></td>
				<td>&nbsp;</td>
				<td><strong>City</strong></td>
				<td><input type="text" id="bcdrc_member_info_city" name="bcdrc_member_info_city" size="35" value="<?php echo $city; ?>" /></td>
			</tr>
			<tr>
				<td><strong>Link</strong></td>
				<td><input type="text" id="bcdrc_member_info_link" name="bcdrc_member_info_link" size="35" value="<?php echo $link; ?>" /></td>
				<td>&nbsp;</td>
				<td><strong>State</strong></td>
				<td><input type="text" id="bcdrc_member_info_state" name="bcdrc_member_info_state" size="35" value="<?php echo $state; ?>" /></td>
			</tr>
			<tr>
				<td><strong>Quote</strong></td>
				<td colspan="4"><input type="text" id="bcdrc_member_info_quote" name="bcdrc_member_info_quote" size="100" value="<?php echo $quote; ?>" /></td>
			</tr>
		</table>
	</div>
<?php
}


// ----------------------------------------------------------------------------------------------------
// Save the data entered in the custom fields in the meta box

add_action( 'save_post', 'bcdrc_save_member_info' );
function bcdrc_save_member_info( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	
	if ( !wp_verify_nonce( $_POST['bcdrc_nf_member_info'], plugin_basename( __FILE__ ) ) )
		return;
	
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return;
	}
	else {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return;
	}
	
	update_post_meta($post_id, 'bcdrc_member_info_jobtitle', $_POST['bcdrc_member_info_jobtitle']);
	update_post_meta($post_id, 'bcdrc_member_info_city', $_POST['bcdrc_member_info_city']);
	update_post_meta($post_id, 'bcdrc_member_info_state', $_POST['bcdrc_member_info_state']);
	update_post_meta($post_id, 'bcdrc_member_info_link', $_POST['bcdrc_member_info_link']);
	update_post_meta($post_id, 'bcdrc_member_info_quote', $_POST['bcdrc_member_info_quote']);
}


// ----------------------------------------------------------------------------------------------------
// Add roster companion fields to roster manage view

function bcdrc_get_photo( $post_id ) {
	$post_thumbnail_id = get_post_thumbnail_id( $post_id );
	if ( $post_thumbnail_id ) {
		$post_thumbnail_img = wp_get_attachment_image_src( $post_thumbnail_id, 'featured_preview' );
		return $post_thumbnail_img[0];
	}
}

add_filter( 'manage_edit-bcd_cpt_member_columns', 'bcdbb_add_manager_columns' );
function bcdbb_add_manager_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'photo' => 'Photo',
		'title' => __('Title'),
		'bcdrcjobtitle' => 'Job Title',
		'bcdrccity' => 'City',
		'bcdrcstate' => 'State',
		'category' => 'Category'
	);
	return $columns;
}


add_action( 'manage_bcd_cpt_member_posts_custom_column',  'bcdrc_show_manager_columns', 10, 2 );
function bcdrc_show_manager_columns ( $column, $post_id ) {
	switch ( $column ) {
		case 'photo':
			$photo = bcdrc_get_photo( $post_id );
			if ( $photo ) {
				echo '<img src="' . $photo . '" width="50px" height="60px"';
			}
			else {
				$img = BCDRC_PLUGIN_URL . '/images/image-placeholder.jpg';
				echo '<img src="' . BCDRC_PLUGIN_URL . '/images/image-placeholder.jpg" width="50px" height="60px"';
			}
			break;
		case 'bcdrcjobtitle':
			$jobtitle = get_post_meta( $post_id, 'bcdrc_member_info_jobtitle', true);
			echo $jobtitle;
			break;
		case 'bcdrccity':
			$city = get_post_meta( $post_id, 'bcdrc_member_info_city', true);
			echo $city;
			break;
		case 'bcdrcstate':
			$state = get_post_meta( $post_id, 'bcdrc_member_info_state', true);
			echo $state;
			break;
		case 'category':
			$terms = get_the_term_list( $post_id, 'bcd_tx_member_category', '', ', ', '' );
			echo $terms;
			break;
	}
}

?>
