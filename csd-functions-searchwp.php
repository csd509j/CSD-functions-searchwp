<?php
/*
Plugin Name: CSD Functions - SearchWP
Version: 1.3
Description: SearchWP Plugin Customizations for CSD Schools Theme
Author: Josh Armentano
Author URI: http://abidewebdesign.com
Plugin URI: http://abidewebdesign.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WP_CONTENT_DIR . '/plugins/plugin-update-checker-master/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/csd509j/CSD-functions-searchwp',
	__FILE__,
	'CSD-functions-searchwp'
);

$myUpdateChecker->setBranch('master'); 

/*
 * Fix bug with SearchWP not sorting results correctly
 *
 */
 
function csd_searchwp_query_orderby( $sql ) {
	
  return str_replace( 'finalweight ASC', 'finalweight DESC', $sql );

}
add_filter( 'searchwp_query_orderby', 'csd_searchwp_query_orderby' );

/*
 * Automatically convert permalinks to PDFs in search results to the PDF itself, not the Attachment page
 *
 */

function csd_force_direct_pdf_links( $permalink ){
	
	global $post;

	if ( is_search() && 'application/pdf' == get_post_mime_type( $post->ID ) ) {
		
		// if the result is a PDF, link directly to the file not the attachment page
		$permalink = wp_get_attachment_url( $post->ID );
		
	}

	return esc_url( $permalink );
}
add_filter( 'the_permalink', 'csd_force_direct_pdf_links' );
add_filter( 'attachment_link', 'csd_force_direct_pdf_links' );

/*
 * Link directly to Media files instead of Attachment pages in search results
 *
 */
 
function my_search_media_direct_link( $permalink, $post ) {
	
	if ( is_search() && 'attachment' === get_post_type( $post ) ) {
		
		$permalink = wp_get_attachment_url( $post->ID );
		
	}

	return esc_url( $permalink );
}
add_filter( 'the_permalink', 'my_search_media_direct_link', 10, 2 );

/*
 * Adjust mysql select statement limits
 *
 */
 
add_filter( 'searchwp_big_selects', '__return_true' );

/*
 * Add custom fields to search
 *
 */
function csd_searchwp_acf_repeater_keys( $keys ) {
	$keys[] = 'page_content_blocks_%';

	return $keys;
}

add_filter( 'searchwp_custom_field_keys', 'csd_searchwp_acf_repeater_keys' );

/*
 * Add search highlight
 *
 */
function searchwp_term_highlight_auto_excerpt( $excerpt ) {
	global $post;

	if ( ! is_search() ) {
		return $excerpt;
	}

	// prevent recursion
	remove_filter( 'get_the_excerpt', 'searchwp_term_highlight_auto_excerpt' );

	$global_excerpt = '...' . searchwp_term_highlight_get_the_excerpt_global( $post->ID, null, get_search_query() ) . '...';

	add_filter( 'get_the_excerpt', 'searchwp_term_highlight_auto_excerpt' );

	return wp_kses_post( $global_excerpt );
}

add_filter( 'get_the_excerpt', 'searchwp_term_highlight_auto_excerpt' );