<?php

// require_once wordpress stuffs here
require_once 'wordpress/wp-load.php';
require_once 'wordpress/wp-includes/query.php';
require_once 'wordpress/wp-includes/post.php';

function migrate() {
    $post_content = array(
	'post_type' => 'page',
	'post_status' => 'publish',
    );
    $query = new WP_Query(array(
        'post_type' => 'page',
        'name' => 'authors'
    ));
    if (count($query->posts) == 0) {
        $post_content['post_title'] = 'Authors';
        $post_content['menu_order'] = 1;
        $id = wp_insert_post($post_content);
    }
    $query = new WP_Query(array(
        'post_type' => 'page',
        'name' => 'new-releases'
    ));
    if (count($query->posts) == 0) {
        $post_content['post_title'] = 'New Releases';
        $post_content['menu_order'] = 2;
        $id = wp_insert_post($post_content);
    }
}

function undo() {
    $query = new WP_Query(array(
        'post_type' => 'page',
        'name' => 'authors'
    ));
    if (count($query->posts) == 0) {
	foreach($query->posts as $post) {
	    wp_delete_post($post->ID);
	}
    }
    $query = new WP_Query(array(
        'post_type' => 'page',
        'name' => 'new-releases'
    ));
    if (count($query->posts) == 0) {
	foreach($query->posts as $post) {
	    wp_delete_post($post->ID);
	}
    }
}
