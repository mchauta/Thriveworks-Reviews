<?php

//Register shortcodes
function register_reviews_shortcodes() {
    add_shortcode('twx_reviews', 'shortcode_reviews');
}
add_action('init', 'register_reviews_shortcodes');

//run loop in shortcode
function shortcode_reviews($atts) {
    global $loop;

    //set attributes
    $atts = shortcode_atts(array(
        'number' => '',
        'location' => '',
    ), $atts);

    $number = $atts['number'];
    $location = $atts['location'];

    if ('all' == $number) {
        $number = -1;
    }

//if the number and location attr exists, display that number of posts from that location
    if ($number && $location)  {
    //if both parameters exist
        //find posts with both taxonomies
        $loop = new WP_Query(array(
            'posts_per_page' => $atts['number'],
            'post_type' => 'reviews',
            'orderby' => 'date menu_order',
            'order' => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'providers_location',
                    'field' => 'name',
                    'terms' => $atts['location'],
                ),
            )
        ));

    }  else {
        //or if neither exists, display warning/tip
        echo '<p>You must provide a "location" and a "number" parameter.  </p>';
    }


    if (!$loop->have_posts()) {
        return false;
    }

    $content = '<div class="review-group" id="' . $location . '_reviews_container">';

    while ($loop->have_posts()) {
        $loop->the_post();
        //post content
            //variables
            //get terms
            $post_content = get_the_content();
            $post_content = wpautop( $post_content );
            $rating = get_field('rating');
            $name = get_field('first_name');
            $title = the_title();

            //content
            $content = $content .=
                 '<div class="review">
                 <div class="reviews_title">' .
                $name .
                '</div>
                <div class="reviews_rating">' .
                $rating .
                '</div>' .
                '<div class="reviews_content">' .
                $post_content . '</div>
                </div>';

            wp_reset_postdata();

            }

        }

    return $content;
