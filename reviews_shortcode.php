<?php

//Register shortcodes
function register_reviews_shortcodes() {
    add_shortcode('twx_reviews', 'shortcode_reviews');
    add_shortcode('twx_reviews_snippet', 'shortcode_reviews_snippet');
}
add_action('init', 'register_reviews_shortcodes');

//run loop in shortcode
function shortcode_reviews($atts) {
    global $reviews_loop;
    $i = 1;

    //set attributes
    $atts = shortcode_atts(array(
        'number' => '',
        'location' => '',
        'provider' => '',
    ), $atts);

    $rev_number = $atts['number'];
    $rev_location = $atts['location'];
    $rev_provider = $atts['provider'];

    if ('all' == $rev_number) {
        $rev_number = -1;
    }

//if the number and location attr exists, display that number of posts from that location
    if ($rev_number && $rev_location)  {
    //if both parameters exist
        //find posts with both taxonomies
        $reviews_loop = new WP_Query(array(
            'posts_per_page' => $rev_number,
            'post_type' => 'reviews',
            'orderby' => 'date menu_order',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'approved',
                    'value' => 'Approved',
                )),
            'tax_query' => array(
                array(
                    'taxonomy' => 'providers_location',
                    'field' => 'name',
                    'terms' => $rev_location,
                ),
            )
        ));

    }  elseif ($rev_number && $rev_provider) {
        $reviews_loop = new WP_Query(array(
            'posts_per_page' => $rev_number,
            'post_type' => 'reviews',
            'orderby' => 'date menu_order',
            'order' => 'ASC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'review_tag',
                    'value' => $rev_provider,
                ),
                array(
                    'key' => 'approved',
                    'value' => 'Approved',
                ),
            )
        ));
    } else {
        //or if neither exists, display warning/tip
        echo '<p>You must provide a "location" and a "number" parameter.  </p>';
    }


    if (!$reviews_loop->have_posts()) {

        return 'There are currently no reviews.';
    }


    while ($reviews_loop->have_posts()) {
        $reviews_loop->the_post();
        //post content
        $ID = get_the_ID();
        $script = '
        <script>jQuery(function($){

$("#reviews_read_more_link_' . $ID . '").click(function(){
        $("#reviews_read_more_link_' . $ID . '").css("display", "none");
        $("#reviews_read_more_' . $ID . '").show();
        $("#reviews_elipses_' . $ID . '").hide();
    $("#reviews_read_less_link_' . $ID . '").css("display", "block");
    });
    $("#reviews_read_less_link_' . $ID . '").click(function(){
        $("#reviews_read_less_link_' . $ID . '").css("display", "none");
        $("#reviews_read_more_' . $ID . '").hide();
        $("#reviews_elipses_' . $ID . '").show();
        $("#reviews_read_more_link_' . $ID . '").css("display", "block");
    });
});</script>';
            //variables

            $post_content = get_the_content();
        if($i % 3 == 1) {
        $content = $content .= '<div class="review-group" id="' . $i . '_reviews_group">';
        }


            if (strlen($post_content) > 100) {
                $post_content_first = substr($post_content, 0, 100);
                $post_content_last = substr($post_content, 100);
                $post_content =  $post_content_first . '<span class="reviews_elipses" id="reviews_elipses_' . $ID . '">...</span><a class="reviews_read_more_link" id="reviews_read_more_link_' . $ID . '" href="#reviews_read_more_' . $ID . '">Read more</a><span class="reviews_read_more" id="reviews_read_more_' . $ID . '">' . $post_content_last . '</span><a class="reviews_read_less_link" id="reviews_read_less_link_' . $ID . '" href="#reviews_read_more_link_' . $ID . '">Read less</a>';
            }
            $post_content = wpautop( $post_content );
            $post_content = $script .= $post_content;
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
        if($i % 3 == 0) {
            $content = $content .= '</div>';
        }
        $i++;

            wp_reset_postdata();

            }
    $content = $content .= '</div>';
                return $content;
        }

function shortcode_reviews_snippet($atts) {
    global $snip_loop;
    $i = 1;

    //set attributes
    $atts = shortcode_atts(array(
        'location' => '',
        'provider' => '',
    ), $atts);

    $snip_location = $atts['location'];
    $snip_provider = $atts['provider'];


//if the number and location attr exists, display that number of posts from that location
    if ($snip_location)  {
    //if both parameters exist
        //find posts with both taxonomies
        $snip_loop = new WP_Query(array(
            'posts_per_page' => -1,
            'post_type' => 'reviews',
            'orderby' => 'date menu_order',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'approved',
                    'value' => 'Approved',
                )),
            'tax_query' => array(
                array(
                    'taxonomy' => 'providers_location',
                    'field' => 'name',
                    'terms' => $snip_location,
                ),
            )
        ));

    }  elseif ($snip_provider) {
        $snip_loop = new WP_Query(array(
            'posts_per_page' => -1,
            'post_type' => 'reviews',
            'orderby' => 'date menu_order',
            'order' => 'ASC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'review_tag',
                    'value' => $snip_provider,
                ),
                array(
                    'key' => 'approved',
                    'value' => 'Approved',
                ),
            )
        ));
    }

    if (!$snip_loop->have_posts()) {
if ($snip_location)  {
        return 'There are no reviews for this location. ';
} elseif ($snip_provider)  {
    return 'There are no reviews for this provider. <br>
    Be the <a href="#' . $snip_provider . '">first to review </a>them.';
    }
    }
$snip_count = $snip_loop->post_count;

    while ($snip_loop->have_posts()) {
        $snip_loop->the_post();
        $snip_rating = get_field('rating');
        $snip_rating_total = $snip_rating_total + $snip_rating;
}
    $snip_rating_average = $snip_rating_total / $snip_count;
    $snip_rating_average = ceil($snip_rating_average);
     switch ($snip_rating_average) {
            case 1:
                $snip_rating_average = ★☆☆☆☆;
                break;

            case 2:
                $snip_rating_average = ★★☆☆☆;
                break;

            case 3:
                $snip_rating_average = ★★★☆☆;
                break;

            case 4:
                $snip_rating_average = ★★★★☆;
                break;

            case 5:
                $snip_rating_average = ★★★★★;
                break;
        }

                        if ( $snip_location ) {
                             $snip_content = '<div class="reviews_snippet">' . 'Overall Rating: <span class="snip_reviews_rating">' . $snip_rating_average . '</span> based on <a href="#reviews_container">' . $snip_count . '</a> reviews.</div>';
                        } elseif ($snip_provider) {
                            $snip_content = '<div class="reviews_snippet">' . 'Overall Rating: <span class="snip_reviews_rating">' . $snip_rating_average . '</span> based on <a href="#reviews_' . $post->ID . '">' . $snip_count . '</a> reviews.</div>';
                        } else {
                            $parent = wp_get_post_parent_id($post->ID);
                            $parent = get_permalink($parent);
                            $snip_content = '<div class="reviews_snippet">' . 'Overall Rating: <span class="snip_reviews_rating">' . $snip_rating_average . '</span> based on <a href="' . $parent . '/#reviews_container">' . $snip_count . '</a> reviews.</div>';
                        }
    //$snip_content = '<div class="reviews_snippet">' . 'Overall Rating: <span class="snip_reviews_rating">' . $snip_rating_average . '</span> based on ' . $snip_count . ' reviews.</div>';
    return $snip_content;
;
}
