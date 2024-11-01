<?php
// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

$option_name = 'gil_top_tips_settings';

delete_option( $option_name );

// for site options in Multisite
delete_site_option( $option_name );

// delete custom post type posts
$toptips_args  = array( 'post_type' => 'top-tips', 'post_status' => 'any', 'posts_per_page' => -1) ;
$toptips_posts = get_posts( $toptips_args );
foreach ( $toptips_posts as $post ) {
	wp_delete_post( $post->ID, false );
}
?>