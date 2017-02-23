<?php
/*
Plugin Name: Thriveworks Reviews
Plugin URI: http://thriveworks.com/reviews
Description: a plugin created to provide reviews functionality to thriveworks.com
Version: 1.0
Author: Matt Chauta
Author URI: http://chauta.carbonmade.com/
License: GPL2
*/
?>
<?php

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'REVIEWS_VERSION', '1.0' );
define( 'REVIEWS__MINIMUM_WP_VERSION', '3.7' );
//define( 'REVIEWS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'REVIEWS_DELETE_LIMIT', 100000 );

//This plugin requires the plugin Advanced Custom Fields to work. Check if ACF is active. If not, display error.

function sample_admin_notice__success() {
    $acf_active = is_plugin_active( 'advanced-custom-fields/acf.php' );
    if ( false === $acf_active ) {
?>
    <div class= "notice notice-error">
        <p><?php
         _e( 'The Thriveworks Reviews plugin relies on the <strong>"Advanced Custom Fields"</strong> plugin to work, please activate it before continuing.', 'sample-text-domain' );
?></p>
    </div>
    <?php
    }
}

add_action( 'admin_notices', 'sample_admin_notice__success' );

//enqueue style.css
function reg_reviews_styles() {
    $css_path = get_stylesheet_directory() . '/style.css';
// Example: /home/user/var/www/wordpress/wp-content/plugins/my-plugin/
    wp_enqueue_style('reviews-style', '/wp-content/plugins/reviews/css/style.css', array(), filemtime($css_path));
}
add_action('wp_enqueue_scripts', 'reg_reviews_styles');

// Creates Custom Post Type 'Reviews'
function reviews_init() {
    $args = array(
        'labels' => array(
            'name' => __( 'Reviews' ),
            'singular_name' => __( 'Review' ),
            'search_items' => 'Search Reviews',
        ),
        'public' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'query_var' => true,
        'menu_icon' => 'dashicons-star-half',
        'exclude_from_search' => (true),
        'taxonomies' => array (
            'providers_location',
        ),
        'rewrite' => array (
            'with_front' => false,
        ),
        'supports' => array (
            'title',
            'editor',
            'revisions',
            'page-attributes',
        )
    );
    register_post_type( 'reviews', $args );
}

add_action( 'init', 'reviews_init' );


//Add Last Modified to columns on custom post edit page
function review_table_head($defaults)
{
    //add columns
    $defaults['order'] = 'Order';
    $defaults['modified'] = 'Last Modified';
    return $defaults;
}

add_filter('manage_reviews_posts_columns', 'review_table_head');

//function for custom columns
function populate_custom_columns($column, $post_id) {

    // http://andrewnorcross.com/tutorials/modified-date-display/
    // popluates the modified column to provide the name of the last editor, date and time.
    if ($column == 'modified') {
        $m_orig    = get_post_field('post_modified', $post_id, 'raw');
        $m_stamp   = strtotime($m_orig);
        $modified  = date('n/j/y @ g:i a', $m_stamp);
        $modr_id   = get_post_meta($post_id, '_edit_last', true);
        $auth_id   = get_post_field('post_author', $post_id, 'raw');
        $user_id   = !empty($modr_id) ? $modr_id : $auth_id;
        $user_info = get_userdata($user_id);
        echo '<p class="mod-date">';
        echo '<em>' . $modified . '</em><br />';
        echo 'by <strong>' . $user_info->display_name . '<strong>';
        echo '</p>';
    }
    if ($column == 'order') {
        $order = get_post_field('menu_order', $post_id, 'raw');
        echo '<p>' . $order . '</p>';
    }
}

add_action('manage_reviews_posts_custom_column', 'populate_custom_columns', 10, 2);

/*
 * Display a custom taxonomy dropdown in admin
 * @author Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_action('restrict_manage_posts', 'tsm_reviews_filter_post_type_by_taxonomy');
function tsm_reviews_filter_post_type_by_taxonomy() {
	global $typenow;
	$post_type = 'reviews'; // change to your post type
	$taxonomy  = 'providers_location'; // change to your taxonomy
	if ($typenow == $post_type) {
		$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
		$info_taxonomy = get_taxonomy($taxonomy);
		wp_dropdown_categories(array(
			'show_option_all' => __("Show All {$info_taxonomy->label}"),
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'orderby'         => 'name',
			'selected'        => $selected,
			'show_count'      => true,
			'hide_empty'      => true,
		));
	};
}
/**
 * Filter posts by taxonomy in admin
 * @author  Mike Hemberger
 * @link http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
 */
add_filter('parse_query', 'tsm_reviews_convert_id_to_term_in_query');
function tsm_reviews_convert_id_to_term_in_query($query) {
	global $pagenow;
	$post_type = 'reviews'; // change to your post type
	$taxonomy  = 'providers_location'; // change to your taxonomy
	$q_vars    = &$query->query_vars;
	if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
		$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
		$q_vars[$taxonomy] = $term->slug;
	}
}

require_once( REVIEWS__PLUGIN_DIR . 'shortcode.php' );

add_action( 'template_redirect', 'reviews_redirect_post' );

function reviews_redirect_post() {
  $queried_post_type = get_query_var('post_type');
         if (!is_user_logged_in() || !current_user_can('administrator')) {
                if ( is_single() && 'reviews' == $queried_post_type) {
                    wp_redirect( home_url(), 301 );
                    exit;
            }

        }
}
