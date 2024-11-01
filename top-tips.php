<?php
/*
* Plugin Name: Top Tips
* Plugin URI: https://www.routetoweb.co.uk/wordpress/plugin-development/top-tips/
* Author: Andy Gilbert
* Author URI: https://www.routetoweb.co.uk
* Description: Display you favourite top tip next to a post with a matching category. An optional feature is to add a link to another post containing further information. Use the shortcode [top-tips] either in the editor or template.
* Version: 0.0.5
* License: GPLv3 or later
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain: top-tips
* Domain Path: /languages
*/

namespace Giltoptips;

require_once 'includes/top-tips-config.php' ;

function giltoptips_frontend_styles() {
	wp_register_style('gil_top_tips', plugins_url('/public/css/top-tips.css', __FILE__));
	wp_enqueue_style('gil_top_tips');
}
add_action('wp_enqueue_scripts', 'Giltoptips\giltoptips_frontend_styles');



function giltoptips_register_top_tips_post_type() {
	register_post_type('top-tips',
		array(
			'labels' => array(
				'name'               => __( 'Top Tips Knowledge Bank', 'top-tips' ),
				'singular_name'      => __( 'Top Tip', 'top-tips' ),
				'menu_name'          => __( 'Top Tips', 'top-tips' ),
				'parent_item_colon'  => __( 'Parent Top Tip', 'top-tips' ),
				'all_items'          => __( 'Knowledge Bank', 'top-tips' ),
				'view_item'          => __( 'View Top Tip', 'top-tips' ),
				'add_new_item'       => __( 'Add New Top Tip', 'top-tips' ),
				'add_new'            => __( 'Add New', 'top-tips' ),
				'edit_item'          => __( 'Edit Top Tip', 'top-tips' ),
				'update_item'        => __( 'Update Top Tip', 'top-tips' ),
				'search_items'       => __( 'Search Top Tips', 'top-tips' ),
				'not_found'          => __( 'Not Found', 'top-tips' ),
				'not_found_in_trash' => __( 'Not found in Trash', 'top-tips' ),
			),
			'description'         => __( 'Top Tips to help you', 'top-tips' ),
			'public'              => true,
			'has_archive'         => false,
			'menu_icon'           => 'dashicons-megaphone',
			'rewrite'             => array('slug' => 'top-tips'),
			'supports'            => array('title', 'editor', 'revisions'),
			'taxonomies'          => array( 'category' ),
			'exclude_from_search' => true,
			'menu_position'       => 66,
		)
	);
}
add_action('init', 'Giltoptips\giltoptips_register_top_tips_post_type');



/**
* if no attributes are set the default is to randomly pick a tip
* tipid can be a single numeric string or an array of tip ids
* tipid=123
* if a string only that tip will display
* tipid=[123,456,789]
* if tipid is an array limit the tips to ones included in the array
* set postcats=true to select tips assigned that post category
* set cats="1,2,3" to manually select the categories
*/



function giltoptips_display_top_tips( $atts ) {
	if ( !is_home() && !is_front_page() ) {
		ob_start();
		$atts = shortcode_atts(
			array(
				'id'       => 0,
				'tipid'    => '',
				'postcats' => '',
				'cats'     => '',
			),
			$atts, 'giltoptips_display_top_tips'
		);
		$atts['tipid']    = explode(',', $atts['tipid'] );
		$atts['postcats'] = filter_var( $atts['postcats'], FILTER_VALIDATE_BOOLEAN );
		$atts['cats']     = explode(',', $atts['cats'] );


		$args = array(
			'post_type'      => 'top-tips',
			'orderby'        => 'rand',
			'posts_per_page' => 1,
		);

		if ( isset( $atts['tipid'] ) && $atts['tipid'][0] != '' ) {
			$args['post__in'] = $atts['tipid'];
		}
		elseif ( $atts['cats'] && $atts['cats'][0] != '' ) {
			$args['category__in'] = $atts['cats'][0];
		}
		else {
			$post_cats_ids = giltoptips_get_cat_ids( get_the_ID() );
			if ( !empty( $post_cats_ids ) ) {
				$args['category__in'] = $post_cats_ids;
			}
			else {
				$args['category__in'] = [0];
			}
		}

		$query = new \WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				if ( is_singular() ){
			?>
			<div class="gil-top-tips">
				<div class="gil-top-tips-image">
				<?php
				$image_url = get_option( 'giltoptips_image' );
				if ( $image_url ) { ?>
					<img src="<?php echo esc_attr( $image_url )?>" style="max-width: 300px; height: auto;">
				<?php } ?>
				<?php
				$top_tips_cat_names = giltoptips_get_cat_names( get_the_ID() );
				if ( $top_tips_cat_names ) {
					$plural =( count( giltoptips_get_cat_ids( get_the_ID() ) ) > 1 ) ? 'ies' : 'y';
					echo '<span class="gil-top-tips-cat">';
					echo 'From the <span>' . wp_kses_post( $top_tips_cat_names ) . '</span> categor' . esc_html( $plural );
					echo '</span>';
				}
				?>
				</div>
				<div class="gil-top-tips-content">
				<?php
				the_title( '<h2>', '</h2>', true );
				$get_post = get_the_content();
				$postwithbreaks = wpautop( $get_post, true );
				if ( $postwithbreaks ) {
					echo wp_kses_post( $postwithbreaks );
				}
				else { ?>
					<p>Top Tip content not set</p>
				<?php
				}
				$url = get_post_meta( get_the_ID(), '_gil_top_tips_url_value_key', true );

				/* if the custom url is set use that
				* otherwise go to the full tip
				*/
				if ( $url ) {
					$tip_url = $url; ?>
					<div class="gil-top-tips-url">
					<a href="<?php echo esc_url( $tip_url ) ?>"><?php echo esc_html__( 'Learn more&hellip;', 'top-tips' ) ?></a>
					</div>
					<?php
				} ?>
				</div>
			</div>
			<?php
			}
			}
		}
		wp_reset_postdata();
		return ob_get_clean();
	}
}
add_shortcode('top-tips', 'Giltoptips\giltoptips_display_top_tips');



/**
* return an array of category ids associated with the given post ID
*/
function giltoptips_get_cat_ids( $toptip_id ) {
	$categories = get_the_category( $toptip_id );
	$cats_ary   = array();
	$count      = 0;
	if ( !empty( $categories ) ) {
		foreach ( $categories as $cats ) {
			$cats_ary[ $count ] = $cats->term_id;
			$count ++;
		}
	}
	return $cats_ary;
}


/**
* return a comma & ampersand separated string of category names
* accpets int
*/
function giltoptips_get_cat_names( $toptip_id ) {
	$cat_ids = giltoptips_get_cat_ids( $toptip_id );
	$cats_ary   = array();
	$ret        = '';
	if ( !empty( $cat_ids ) ) {
		foreach ( $cat_ids as $cats ) {
			$ret .= get_cat_name( $cats ) . ', ';
		}
	}
	$ret = rtrim( $ret, ', ' );
	$ret = giltoptips_str_lreplace( ', ', ' &amp; ', $ret );
	return $ret;
}



function giltoptips_str_lreplace( $search, $replace, $subject ) {
	$pos = strrpos( $subject, $search );
	if( $pos !== false ) {
		$subject = substr_replace( $subject, $replace, $pos, strlen( $search ) );
	}
	return $subject;
}



function giltoptips_set_css(  ) {
	if ( !is_home() && !is_front_page() ) {
		$giltoptips_bg_colour   = get_option('giltoptips_bg_colour') ? get_option('giltoptips_bg_colour') : GILTOPTIPS_BG_COLOUR;
		$giltoptips_h_colour    = get_option('giltoptips_h_colour') ? get_option('giltoptips_h_colour') : GILTOPTIPS_H_COLOUR;
		$giltoptips_txt_colour  = get_option('giltoptips_txt_colour') ? get_option('giltoptips_txt_colour') : GILTOPTIPS_TXT_COLOUR;
		$giltoptips_a_colour    = get_option('giltoptips_a_colour') ? get_option('giltoptips_a_colour') : GILTOPTIPS_A_COLOUR;
		$giltoptips_ahov_colour = get_option('giltoptips_ahov_colour') ? get_option('giltoptips_ahov_colour') : GILTOPTIPS_AHOV_COLOUR;
		ob_start();
		?>
		<style type="text/css">
			:root {
				--gil-top-tip-colour-bg: <?php echo esc_html( $giltoptips_bg_colour ); ?>;
				--gil-top-tip-colour-h: <?php echo esc_html( $giltoptips_h_colour ); ?>;
				--gil-top-tip-colour-txt: <?php echo esc_html( $giltoptips_txt_colour ); ?>;
				--gil-top-tip-colour-a: <?php echo esc_html( $giltoptips_a_colour ); ?>;
				--gil-top-tip-colour-ahov: <?php echo esc_html( $giltoptips_ahov_colour ); ?>;
			}
		</style>
		<?php
		echo ob_get_clean();
	}
}
add_action('wp_head', 'Giltoptips\giltoptips_set_css');



if ( is_admin() ) {
	require_once 'admin/top-tips-admin.php';
}
?>