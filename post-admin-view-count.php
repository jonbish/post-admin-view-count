<?php
/*
  Plugin Name: Post Admin View Count
  Plugin URI: http://www.jonbishop.com/downloads/wordpress-plugins/post-admin-view-count/
  Description: Adds a sortable column to the admin's post manager, displaying the view count for each post.
  Version: 1.0
  Author: Jon Bishop
  Author URI: http://www.jonbishop.com
  License: GPL2
 */

class PostAdminViewCount {

    function init() {
        if (is_admin()) {
            add_filter('manage_edit-post_sortable_columns', array(&$this, 'pvc_column_register_sortable'));
            add_filter('posts_orderby', array(&$this, 'pvc_column_orderby'), 10, 2);
            add_filter("manage_posts_columns", array(&$this, "pvc_columns"));
            add_action("manage_posts_custom_column", array(&$this, "pvc_column"));
            add_action("admin_footer-edit.php",array(&$this, "pvc_update_date"));
            add_action("admin_head-edit.php",array(&$this, "pvc_get_date"));
            
        }
    }
    
    // Get views for post ID
    function pvc_get_views($post_ID) {
        $stats_options = get_option( 'stats_options' );
        $blog_id = $stats_options['blog_id'];
        $api_key = $stats_options['api_key'];
        $query_string = "http://stats.wordpress.com/csv.php?api_key=f4e5da02b97d&blog_id=3778956&table=postviews&days=-1&format=json";
        // Make it happen
        return $views;
    }
    
    // Add new columns to action post type
    function pvc_columns($columns) {
        $columns["post_word_count"] = "Word Count";
        return $columns;
    }

    // Add data to new columns of action post type
    function pvc_column($column) {
        global $post, $pvc_last;
        if ("post_word_count" == $column) {
            // Grab a fresh word count
            $word_count = pvc_get_views($post->ID);
            echo $word_count;
        }
    }

    // Queries to run when sorting
    // new columns of action post type
    function pvc_column_orderby($orderby, $wp_query) {
        global $wpdb;

        if ('post_word_count' == @$wp_query->query['orderby'])
            $orderby = "(SELECT CAST(meta_value as decimal) FROM $wpdb->postmeta WHERE post_id = $wpdb->posts.ID AND meta_key = '_post_view_count') " . $wp_query->get('order');

        return $orderby;
    }

    // Make new columns to action post type sortable
    function pvc_column_register_sortable($columns) {
        $columns['post_word_count'] = 'post_word_count';
        return $columns;
    }
    
    function pvc_get_date(){
        global $post, $pvc_last;
        // Check last updated
        $pvc_last = get_option('pvc_last_checked');

        // Check to make sure we have a post and post type
		if ( $post && $post->post_type ){
			
			// Grab all posts with post type
			$args = array(
				'post_type' => $post->post_type,
				'posts_per_page' => -1
				);

			// Grab the posts
			$post_list = new WP_Query($args);
			if ( $post_list->have_posts() ) : while ( $post_list->have_posts() ) : $post_list->the_post(); 
				
                    // Grab a fresh view count
	            $word_count = pvc_get_views($post->ID);

	            // If post has been updated since last check
	            if ($post->post_modified > $pvc_last || $pvc_last == "") {
	            	// Grab word count from post meta
	                $saved_word_count = get_post_meta($post->ID, '_post_view_count', true);
	                // Check if new wordcount is different than old word count
	                if ($saved_word_count != $word_count || $saved_word_count == "") {
	                	// Update word count in post meta
	                    update_post_meta($post->ID, '_post_view_count', $word_count, $saved_word_count);
	                }
	            }
			endwhile; 
			endif;

			// Let WordPress do it's thing
			wp_reset_query();
		}
    }
    
    function pvc_update_date(){
    	// Save the last time this page was generated
        $current_date = current_time('mysql');
        update_option('pvc_last_checked', $current_date);
    }

}

$postAdminViewCount = new PostAdminViewCount();
$postAdminViewCount->init();
?>