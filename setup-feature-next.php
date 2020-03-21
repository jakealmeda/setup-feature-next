<?php
/**
 * Plugin Name: Setup Feature Next
 * Description: Displays the previous, next or a list of entries based on the current entry's location in the loop.
 * Version: 1.0
 * Author: Jake Almeda
 * Author URI: http://smarterwebpackages.com/
 * Network: true
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

############

add_shortcode( 'ss_list_entries', 'setup_starter_list_entries' );
function setup_starter_list_entries( $atts ) {
    // $atts['foo'] -> get attribute contents
    
    // validate post type
    if( $atts[ 'post_type' ] ) {
        $post_type = $atts[ 'post_type' ];
    } else {
        $post_type = get_post_type( $post ); // get post type slug
    }
    
    // check for taxonomy
	if( $atts[ 'tax_name' ] ) {
		$condition = TRUE;
	} else {
		$condition = FALSE;
	}
    
    // wp-query arguments
    $args = array(
        'posts_per_page'    => -1,
        'post_type'         => $post_type,
		'post_status'    	=> 'publish',
        'orderby'           => 'date',
        'order'             => 'DESC',
        'category_name'     => $category->slug,
        //'post__not_in'    => array( get_the_ID() )
    ) + ( $condition ? array(
		'tax_query' 		=> array(
			array(
				'taxonomy' 		=> $atts[ 'tax_name' ],
				'field'    		=> 'slug',
				'terms'    		=> $atts[ 'tax_term' ],
			),
	)) : array());
            
    // pass more variables
    $more_args = array(
        'display'           => $atts[ 'display' ],
        'template'          => $atts[ 'template' ],
        'texts'             => $atts[ 'text' ],
    );
    
    // validate variable if integer
    if( is_numeric( $atts[ 'post_count' ] ) && !$atts[ 'display' ] ) {
        
        // ERROR HANDLING: validate variable's amount - should be greater than 3, 1 each for previous, current and next
        if( $atts[ 'post_count' ] <= 3 ) {
            
            $out = '<p>Entries to display should be more than 3. Please specify correct post_count amount.</p>';
            
        } else {
            
            // pass more variables
            $more_args = array(
                'post_count'        => $atts[ 'post_count' ],
                'display'           => $atts[ 'display' ],
                'template'          => $atts[ 'template' ],
            );
            
            // this will display 1 before current entry, current entry (without link) and x number of enter after the current (x == post_count - 1 before - current)
            $out = setup_starter_wp_query( $args, get_the_ID(), $more_args );
            
        }
        
    } else {
        
        // displays previous, current (optional) and next post entries
        if( $atts[ 'display' ] ) {
            
            if( $atts[ 'display' ] == 'previous' ) {
                
                $out = setup_starter_wp_query( $args, get_the_ID(), $more_args );
                
            } elseif( $atts[ 'display' ] == 'next' ) {
                
                $out = setup_starter_wp_query( $args, get_the_ID(), $more_args );
                
            } else {
                
                // ERROR HANDLING
                $out = '<p>Please specify what to show, PREVIOUS or NEXT.</p>';
                
            }
               
        } else {
            
            // ERROR HANDLING: improper data for the variable, show
            $out = '<p>Please enter a number on how many entries you wish to show.</p>';
            
        }
        
    }
    
    // validate if $out has contents
    if( $out ) {

        return $atts[ 'tag_open' ].$out.$atts[ 'tag_close' ];

    }
    
    // reset query
    setup_starter_reset_query();
    
}

// WP_QUERY LOOP
function setup_starter_wp_query( $args, $current_post, $more_args ) {
    
    global $this_pid, $cpid, $texts;
    
    $texts = $more_args[ 'texts' ];
    
    $out = ''; // empty variable for cases where the same loop is called twice
    
    $loop = new WP_Query( $args );

    if( $loop->have_posts() ):
        
        // get all post IDs
        while( $loop->have_posts() ): $loop->the_post();
            
            /**
             * Uncomment the line below to show a guide so you know where you are in the loop
             * Warning: this might make the page be VERY LONG - it will list ALL entries based on the $args
             *
             */
            //echo '<a href="'.get_the_permalink().'">'.get_the_ID().'</a> == '.get_the_title().'<br />';
            
            // use a simplier variable
            $pid = get_the_ID();
            
            // filter available entries only
            if( $pid ) {
                
                /*if( !$more_args[ 'display' ] && $args[ 'post_type' ] ) {
                    echo $pid.' | '.get_the_title( $pid ).' <br />';
                }*/
                
                if( $pid == $current_post ) {
                    
                    // PREVIOUS ENTRY ==============================
                    if( $prev_post_id ) {
                        
                        // set specific ID to be picked up by the templates
                        $this_pid = $prev_post_id;
                        
                        // set variable to false | indicator if post in the loop is currently viewed
                        if( $cpid ) {
                            $cpid = FALSE;
                        }
                        
                        // output
                        if( $more_args[ 'display' ] != "next" ) {
                            // show if next entry is NOT being queried
                            // this argument kicks in when the last entry is being viewed - this will hide the next option
                            $out .= setup_starter_get_template( $more_args[ 'template' ] );
                        }
                        
                        // stop loop so we can get the previous entry only
                        if( $more_args[ 'display' ] == "previous" ) {
                            break;
                        }
                        
                    }
                    
                    // set specific ID to pick up
                    $this_pid = $pid;
                    
                    // current post ID
                    $cpid = TRUE;
                    
                    // CURRENT ENTRY ==============================
                    if( ! $more_args[ 'display' ] ) {
                        // show if not previous/next is being queried
                        $out .= setup_starter_get_template( $more_args[ 'template' ] );
                    }
                    
                    // trigger capture of next posts
                    $go_next = 1;
                    
                    if( $x == 0 ) {
                        // first entry - no previous should be showed
                        
                        // current post is the first, add 1 more to succeeding entries
                        $add_to_succeeding = 1;
                    }
                    
                } else {
                    
                    if( $x != 0 && $go_next ) {
                        
                        // SUCCEEDING ENTRIES ==============================
                        
                        // set specific ID to be picked up by the templates
                        $this_pid = $pid;
                        
                        // set variable to false | indicator if post in the loop is currently viewed
                        if( $cpid ) {
                            $cpid = FALSE;
                        }
                        
                        // output
                        // exit loop if only NEXT entry is queried
                        if( $more_args[ 'display' ] == "next" ) {
                            
                            $out = setup_starter_get_template( $more_args[ 'template' ] );
                            break;
                            
                        }
                        
                        if( ! $more_args[ 'display' ]) {
                            // add to variable | echo all succeeding entries
                            $out .= setup_starter_get_template( $more_args[ 'template' ] );
                        }
                        
                        // set counter for succeeding entries
                        $s++;
                        
                        // break loop also if maxed out via $more_args[ 'post_count' ], less x number of previous post and current
                        // this argument will be ignored if no more $pid
                        if( $s == ( ( $more_args[ 'post_count' ] - 2 ) + $add_to_succeeding ) ) {
                            break;
                        }
                        
                    }
                    
                } // end of if( $pid == $current_post ) {
                
            }
            
            // catch previous ID
            $prev_post_id = $pid;
            
            $x++;
            
        endwhile;
        
    endif;
    
    echo $out;

}

// RESET QUERIES
if( !function_exists( 'setup_starter_reset_query' ) ) {
    function setup_starter_reset_query() {

        wp_reset_query();
        wp_reset_postdata();

    }
}

// GET CONTENTS OF THE TEMPLATE FILE
if( !function_exists( 'setup_starter_get_template' ) ) {
    function setup_starter_get_template( $filename ) {
        
        ob_start();
        //include get_stylesheet_directory().'/partials/setup_starter_templates/'.$filename.'.php';
        include plugin_dir_path( __FILE__ ).'templates/'.$filename.'.php';
        return ob_get_clean();

    }
}
