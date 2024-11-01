<?php

namespace Giltoptips;

/* --- Admin --- */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function giltoptips_add_custom_meta_boxes() {
	add_meta_box(
		'gil_top_tips_url_metabox',
		__('URL for related page or post','top-tips'),
		'Giltoptips\giltoptips_url_metabox_callback',
		'top-tips',
		'normal',
		'default'
	);
}
add_action('add_meta_boxes', 'Giltoptips\giltoptips_add_custom_meta_boxes');



function giltoptips_url_metabox_callback( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'giltoptips_url_nonce' );
	$gil_top_tips_stored_url = get_post_meta( $post->ID, '_giltoptips_url_value_key', true );
	echo '<input type="url" name="giltoptips_url_field" id="giltoptips_url_field" value="' . esc_attr( $gil_top_tips_stored_url ) . '" size="30">';
}



function giltoptips_save_url_meta( $post_id ) {
	if( !isset( $_POST[ 'giltoptips_url_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST[ 'giltoptips_url_nonce'] ) ) , basename( __FILE__ ) ) ) {
		return;
	}
	$url_data = esc_url_raw( $_POST['giltoptips_url_field'] );
	update_post_meta( $post_id, '_giltoptips_url_value_key', $url_data );
}
add_action('save_post', 'Giltoptips\giltoptips_save_url_meta');



// Add menu item for Top Tips settings in the WordPress dashboard
function giltoptips_add_admin_menu() {
	add_submenu_page( 'edit.php?post_type=top-tips', 'Top Tips Settings', 'Top Tips Settings', 'manage_options', 'giltoptips-settings', 'Giltoptips\giltoptips_settings_page', 99);
}
add_action( 'admin_menu', 'Giltoptips\giltoptips_add_admin_menu' );



// Add the media uploader script
function giltoptips_admin_scripts() {
	wp_enqueue_media();

	wp_register_script('giltoptips-admin-script', plugin_dir_url(__FILE__) . '/js/top-tips-admin.js', array('jquery'), '1.0.0', true);
	$translation_array = array(
		'url'           => get_option( 'giltoptips_image' ),
		'default_image' => plugins_url( GILTOPTIPS_LOGO )
	);
	wp_localize_script( 'giltoptips-admin-script', 'giltoptips_data', $translation_array );
	wp_enqueue_script( 'giltoptips-admin-script' );
	
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'my-script-handle', plugins_url('/js/colour-picker.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}
add_action('admin_enqueue_scripts', 'Giltoptips\giltoptips_admin_scripts');



// Settings Page HTML
function giltoptips_settings_page() {
	?>
	<div class="wrap">
		<h1>Top Tips Settings</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('giltoptips_settings');
				do_settings_sections('giltoptips_settings');
				$giltoptip_image       = get_option( 'giltoptips_image' ) ? get_option( 'giltoptips_image' ) : plugins_url( GILTOPTIPS_LOGO );
				$giltoptip_bg_colour   = get_option( 'giltoptips_bg_colour' ) ? get_option( 'giltoptips_bg_colour' ) : GILTOPTIPS_BG_COLOUR;
				$giltoptip_h_colour    = get_option( 'giltoptips_h_colour' ) ? get_option( 'giltoptips_h_colour' ) : GILTOPTIPS_H_COLOUR;
				$giltoptip_txt_colour  = get_option( 'giltoptips_txt_colour' ) ? get_option( 'giltoptips_txt_colour' ) : GILTOPTIPS_TXT_COLOUR;
				$giltoptip_a_colour    = get_option( 'giltoptips_a_colour' ) ? get_option( 'giltoptips_a_colour' ) : GILTOPTIPS_A_COLOUR ;
				$giltoptip_ahov_colour = get_option( 'giltoptips_ahov_colour' ) ? get_option( 'giltoptips_ahov_colour' ) : GILTOPTIPS_AHOV_COLOUR;
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Image','top-tips' ) ?>:</th>
						<td>
							<input type="hidden" id="giltoptips_image" name="giltoptips_image" value="<?php echo esc_url( $giltoptip_image ); ?>">
							<img id="giltoptips_image_preview" src="<?php echo esc_url( $giltoptip_image ); ?>" style="max-width:180px; height:auto; display:block; margin-bottom: 1em;">
							<input id="giltoptips_upload_image_button" type="button" class="gil-top-tips-button-primary" value="<?php esc_html_e( 'Choose Image','top-tips' ); ?>">
							<input id="giltoptips_revert_image_button" type="button" class="gil-top-tips-button-secondary" value="<?php esc_html_e( 'Revert to default','top-tips' ); ?>">
							<p class="description"><?php esc_html_e( 'Choose an image for the Top Tip (max. width 180px), this will update when saved','top-tips' ); ?>.</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Background colour','top-tips' ) ?>:</th>
						<td>
							<input type="text" id="giltoptips_bg_colour" name="giltoptips_bg_colour" class="gil-top-tips-bg-colour" data-default-color="<?php echo esc_html( GILTOPTIPS_BG_COLOUR ); ?>" value="<?php echo esc_html( $giltoptip_bg_colour ); ?>">
							<p class="description"><?php esc_html_e( 'Choose a background colour for the Top Tip','top-tips' ); ?>.</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Heading colour','top-tips' ) ?>:</th>
						<td>
							<input type="text" id="giltoptips_h_colour" name="giltoptips_h_colour" class="gil-top-tips-h-colour" data-default-color="<?php echo esc_html( GILTOPTIPS_H_COLOUR ); ?>" value="<?php echo esc_html( $giltoptip_h_colour ); ?>">
							<p class="description"><?php esc_html_e( 'Choose a heading colour for the Top Tip','top-tips' ); ?>.</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Text colour','top-tips' ) ?>:</th>
						<td>
							<input type="text" id="giltoptips_txt_colour" name="giltoptips_txt_colour" class="gil-top-tips-txt-colour" data-default-color="<?php echo esc_html( GILTOPTIPS_TXT_COLOUR ); ?>" value="<?php echo esc_html( $giltoptip_txt_colour ); ?>">
							<p class="description"><?php esc_html_e( 'Choose a text colour for the Top Tip','top-tips' ); ?>.</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Link colour','top-tips' ) ?>:</th>
						<td>
							<input type="text" id="giltoptips_a_colour" name="giltoptips_a_colour" class="gil-top-tips-a-colour" data-default-color="<?php echo esc_html( GILTOPTIPS_A_COLOUR ); ?>" value="<?php echo esc_html( $giltoptip_a_colour ); ?>">
							<p class="description"><?php esc_html_e( 'Choose a link colour for the Top Tip','top-tips' ); ?>.</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Link (hover) colour', 'top-tips' ) ?>:</th>
						<td>
							<input type="text" id="giltoptips_ahov_colour" name="giltoptips_ahov_colour" class="gil-top-tips-ahov-colour" data-default-color="<?php echo esc_html( GILTOPTIPS_AHOV_COLOUR ); ?>" value="<?php echo esc_html( $giltoptip_ahov_colour ); ?>">
							<p class="description"><?php esc_html_e( 'Choose a hover state link colour for the Top Tip','top-tips' ); ?>.</p>
						</td>
					</tr>

				</table>
			<?php submit_button(); ?>
		</form>
	</div>
<?php
}



function giltoptips_register_settings() {
	register_setting('giltoptips_settings', 'giltoptips_image');
	register_setting('giltoptips_settings', 'giltoptips_bg_colour');
	register_setting('giltoptips_settings', 'giltoptips_h_colour');
	register_setting('giltoptips_settings', 'giltoptips_txt_colour');
	register_setting('giltoptips_settings', 'giltoptips_a_colour');
	register_setting('giltoptips_settings', 'giltoptips_ahov_colour');
}
add_action('admin_init', 'Giltoptips\giltoptips_register_settings');
