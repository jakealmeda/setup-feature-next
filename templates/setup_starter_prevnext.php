<?php

if ( ! defined( "ABSPATH" ) ) {
    exit; // Exit if accessed directly
}

global $this_pid, // this is the post ID that's going to be passed from the main file
    $texts;

// WHEN MAKING A TEMPLATE, ALWAYS COPY THE LINES FROM THE VERY TOP TO HERE

if( $texts ) {
    echo '<strong>'.$texts.'</strong>';
}
echo '<a href="'.get_the_permalink( $this_pid ).'">'.get_the_post_thumbnail( $this_pid, 'thumbnail' ).'</a>';
echo '<a href="'.get_the_permalink( $this_pid ).'">'.get_the_title( $this_pid ).'</a>';

//EOF