<?php
/**
 * Featured image ALT text automation.
 * Build default ALT text for a post.
 * Format: {Post Title} – {Site Name}
 *
 * @param int $post_id
 * @return string
 */
function dg_org_generate_default_featured_image_alt( $post_id ) {
	$post_title = get_the_title( $post_id );
	if ( empty( $post_title ) ) {
		return '';
	}
	$site_name = get_bloginfo( 'name' );
	return trim( sprintf( '%s – %s', $post_title, $site_name ) );
}
/**
 * Automatically set ALT text for featured images when missing.
 * Applies ONLY to featured images
 */
function dg_org_set_featured_image_alt_on_save( $post_id ) {
	// Bail on autosave or revision
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	// Only apply to viewable post types
	$post_type = get_post_type( $post_id );
	if ( ! $post_type || ! is_post_type_viewable( $post_type ) ) {
		return;
	}
	// Require featured image
	$thumbnail_id = get_post_thumbnail_id( $post_id );
	if ( ! $thumbnail_id ) {
		return;
	}
	// Do not overwrite existing ALT text
	$current_alt = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
	if ( ! empty( $current_alt ) ) {
		return;
	}
	// Generate ALT text
	$alt_text = dg_org_generate_default_featured_image_alt( $post_id );
	if ( empty( $alt_text ) ) {
		return;
	}
	update_post_meta( $thumbnail_id, '_wp_attachment_image_alt', $alt_text );
}

add_action( 'save_post', 'dg_org_set_featured_image_alt_on_save', 20 );