<?php
// Exit if accessed directly
	if( !defined( 'ABSPATH' ) ) exit;
//Register shortcodes
function register_reviews_shortcodes() {
    add_shortcode('twx_reviews', 'shortcode_reviews');
    add_shortcode('twx_reviews_snippet', 'shortcode_reviews_snippet');
}
add_action('init', 'register_reviews_shortcodes');

//run loop in shortcode
function shortcode_reviews($rev_atts) {
    global $reviews_loop;
    $i = 1;

    //set attributes
    $rev_atts = shortcode_atts(array(
        'number' => '',
        'location' => '',
        'provider' => '',
    ), $rev_atts);

    $rev_number = $rev_atts['number'];
    $rev_location = $rev_atts['location'];
    $rev_provider = $rev_atts['provider'];

    if ('all' == $rev_number) {
        $rev_number = -1;
    }

//if the number and location attr exists, display that number of posts from that location
    if ($rev_number && $rev_location)  {
    //if both parameters exist
        $rev_type = 'location';
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
        $rev_type = 'provider';
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
            $published = get_the_date();
            $review_tag = get_field('review_tag');

        switch ($rating) {
            case 1:
                $star_rating = ★☆☆☆☆;
                break;

            case 2:
                $star_rating = ★★☆☆☆;
                break;

            case 3:
                $star_rating = ★★★☆☆;
                break;

            case 4:
                $star_rating = ★★★★☆;
                break;

            case 5:
                $star_rating = ★★★★★;
                break;
        }

        if ($rev_type== 'provider') {
            $item = $rev_provider;
            $schema_type = 'Person';
        } else if ($rev_type== 'location') {
            $item = 'Thriveworks Counseling ' . $rev_location;
            $schema_type = 'LocalBusiness';
        }
            //content
            $content = $content .=
                 '<div itemscope itemtype="http://schema.org/Review" class="review">
                 <meta itemprop="datePublished" content="' . $published . '">
                 <meta itemprop="worstRating" content="1">
                 <meta itemprop="bestRating" content="5">
                 <span style="display:none;" itemprop="itemReviewed" itemscope itemtype="http://schema.org/' . $schema_type . '">' . $item . '</span>
                 <div itemprop="name" class="reviews_title"><h3>' .
                $title .
                '</h3></div>
                <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="reviews_rating"><meta itemprop="ratingValue" content="' . $rating . '">' .
                $star_rating .
                '</span></div>' .
                '<blockquote itemprop="reviewBody" class="reviews_content">' .
                $post_content . '</blockquote>
                <div itemprop="author" itemscope itemtype="http://schema.org/Person" class="reviews_name"> - ' .
                $name . '
                </div></div>';
        if ($i % 3 == 0 || $reviews_loop->post_count < 3) {
            $content = $content .= '</div>';
        }
        $i++;

            wp_reset_postdata();

            }
   // $content = $content .= '</div>';
                return $content;
        }

function shortcode_reviews_snippet($snip_atts) {
    global $snip_loop;
    $i = 1;
$parent = wp_get_post_parent_id($post->ID);
                                $parent = get_permalink($parent);
    //set attributes
    $snip_atts = shortcode_atts(array(
        'location' => '',
        'provider' => '',
    ), $snip_atts);

    $snip_location = $snip_atts['location'];
    $snip_provider = $snip_atts['provider'];



    if ($snip_location)  {
        $rev_type = 'location';
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
        $rev_type = 'provider';
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
    $snip_provider_format = preg_replace('/\s+/', '_', $snip_provider);
    return '<div class ="review_be_first"> There are no reviews for this provider. <br>
    Be the <a href="' . get_site_url() . '/review-providers/?redirect=redirect&provider=' .$snip_provider . '">first to review </a>them.</div>';
    }
    }
$snip_count = $snip_loop->post_count;
$snip_provider_id = str_replace(' ', '_', $snip_provider);

    while ($snip_loop->have_posts()) {
        $snip_loop->the_post();
        $snip_rating = get_field('rating');
        $snip_rating_total = $snip_rating_total + $snip_rating;
}
    $snip_rating_average = $snip_rating_total / $snip_count;
    $snip_rating_average = ceil($snip_rating_average);
      if ($rev_type== 'provider') {
            $item = $rev_provider;
            $schema_type = 'Person';
        } else if ($rev_type== 'location') {
            $item = 'Thriveworks Counseling ' . $rev_location;
            $schema_type = 'LocalBusiness';
        }
     switch ($snip_rating_average) {
            case 1:
                $snip_rating_average_stars = ★☆☆☆☆;
                break;

            case 2:
                $snip_rating_average_stars = ★★☆☆☆;
                break;

            case 3:
                $snip_rating_average_stars = ★★★☆☆;
                break;

            case 4:
                $snip_rating_average_stars = ★★★★☆;
                break;

            case 5:
                $snip_rating_average_stars = ★★★★★;
                break;
        }

                        if ( $snip_location ) {
                            if (is_page_template('content.php')) {
                                $snip_content = '<div itemscope itemtype="http://schema.org/' . $schema_type . '" class="reviews_snippet">
                                <span itemprop="name" style="display:none">' . $item . '</span>
                                <span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                                Overall Rating: <span style="display:none;" itemprop="ratingValue">' . $snip_rating_average . '</span><span class="snip_reviews_rating">' . $snip_rating_average_stars . ' </span>based on <a itemprop="ratingCount" href="' . $parent . '#reviews_container">' . $snip_count . '</a><div style="display:none">
						          <span itemprop="bestRating">5</span>
						          <span itemprop="worstRating">1</span>
					           </div></span> reviews.</div>';
                            }
                            else {
                                $snip_content = '<div itemscope itemtype="http://schema.org/' . $schema_type . '" class="reviews_snippet">
                                <span itemprop="name" style="display:none">' . $item . '</span>
                                <span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                                Overall Rating: <span style="display:none;" itemprop="ratingValue">' . $snip_rating_average . '</span><span class="snip_reviews_rating">' . $snip_rating_average_stars . ' </span>based on <a itemprop="ratingCount" href="#reviews_container">' . $snip_count . '</a><div style="display:none">
						          <span itemprop="bestRating">5</span>
						          <span itemprop="worstRating">1</span>
					           </div></span> reviews.</div>';
                            }
                        } elseif ($snip_provider) {
                             $snip_content = '<div itemscope itemtype="http://schema.org/' . $schema_type . '" class="reviews_snippet">
                                <span itemprop="name" style="display:none">' . $item . '</span>
                                <span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                                Overall Rating: <span style="display:none;" itemprop="ratingValue">' . $snip_rating_average . '</span><span class="snip_reviews_rating">' . $snip_rating_average_stars . ' </span>based on <a itemprop="ratingCount" href="#reviews_' . $snip_provider_id . '">' . $snip_count . '</a><div style="display:none">
						          <span itemprop="bestRating">5</span>
						          <span itemprop="worstRating">1</span>
					           </div></span> reviews.</div>';
                        }
    wp_reset_postdata();
    //$snip_content = '<div class="reviews_snippet">' . 'Overall Rating: <span class="snip_reviews_rating">' . $snip_rating_average . '</span> based on ' . $snip_count . ' reviews.</div>';
    return $snip_content;
;
}
