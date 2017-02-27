<?php

//Register shortcodes
function register_reviews_shortcodes() {
    add_shortcode('twx_reviews', 'shortcode_reviews');
}
add_action('init', 'register_reviews_shortcodes');

//run loop in shortcode
function shortcode_reviews($atts) {
    global $loop;
    $i = 1;

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


    while ($loop->have_posts()) {
        $loop->the_post();
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


            if (strlen($post_content) > 50) {
                $post_content_first = substr($post_content, 0, 50);
                $post_content_last = substr($post_content, 50);
                $post_content =  $post_content_first . '<span class="reviews_elipses" id="reviews_elipses_' . $ID . '">...</span><a class="reviews_read_more_link" id="reviews_read_more_link_' . $ID . '" href="#">Read more</a><span class="reviews_read_more" id="reviews_read_more_' . $ID . '">' . $post_content_last . '</span><a class="reviews_read_less_link" id="reviews_read_less_link_' . $ID . '" href="#">Read less</a>';
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

