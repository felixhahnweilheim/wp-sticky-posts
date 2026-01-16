<?php
/**
 * Plugin Name: Felix' Sticky Posts on Categories
 * Description: Keep sticky posts on top of category pages
 * Version: 1.0.0
 * Author: Felix Hahn
 * License: GPL v3 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Modify the query to show sticky posts first on category pages
 */
function sticky_posts_on_categories( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_category() ) {
		$sticky_posts = get_option( 'sticky_posts' );
		
		if ( ! empty( $sticky_posts ) ) {
			// Mark that we need to modify the orderby clause
			$query->sticky_posts = $sticky_posts;
			add_filter( 'posts_orderby', 'sticky_posts_orderby_filter', 10, 2 );
		}
	}
	
	return $query;
}

/**
 * Filter to modify the ORDER BY clause to put sticky posts first
 */
function sticky_posts_orderby_filter( $orderby, $query ) {
	if ( isset( $query->sticky_posts ) && ! empty( $query->sticky_posts ) ) {
		global $wpdb;
		$sticky_ids = implode( ',', array_map( 'intval', $query->sticky_posts ) );
		$orderby = "CASE WHEN {$wpdb->posts}.ID IN ({$sticky_ids}) THEN 0 ELSE 1 END ASC, {$wpdb->posts}.post_date DESC";
		remove_filter( 'posts_orderby', 'sticky_posts_orderby_filter', 10 );
	}
	return $orderby;
}

add_action( 'pre_get_posts', 'sticky_posts_on_categories' );
