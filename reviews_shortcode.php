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

    $content = '<div class="review-group" id="' . $location . '_reviews_container">

    ';

    while ($loop->have_posts()) {
        $loop->the_post();
        //post content
            //variables
            //get terms
            $post_content = get_the_content();
            if (strlen($post_content) > 50) {
                $post_content_first = substr($post_content, 0, 50);
                $post_content_last = substr($post_content, 50);
                $post_content = $post_content_first . '<span class="reviews_elipses">...</span><a class="reviews_read_more_link" href="#">Read more</a><span class="reviews_read_more">' . $post_content_last . '</span>';
            }
            $post_content = wpautop( $post_content );
            $rating = get_field('rating');
            $name = get_field('first_name');
            $title = get_the_title();

        switch ($rating) {
            case 1:
                $rating = ★☆☆☆☆;
                break;

            case 2:
                $rating = ★★☆☆☆;
                break;

            case 3:
                $rating = ★★★☆☆;
                break;

            case 4:
                $rating = ★★★★☆;
                break;

            case 5:
                $rating = ★★★★★;
                break;
        }

            //content
            $content = $content .=
                 '<div class="review">
                 <div class="reviews_title"><h3>' .
                $title .
                '</h3></div>
                <div class="reviews_rating">' .
                $rating .
                '</div>' .
                '<blockquote class="reviews_content">' .
                $post_content . '</blockquote>
                <div class="reviews_name"> - ' .
                $name . '
                </div></div>';

            wp_reset_postdata();

            }
    $content = $content .= '</div>';
                return $content;
        }

