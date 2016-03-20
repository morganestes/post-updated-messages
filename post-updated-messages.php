<?php
/*
Plugin Name: Post Updated Messages
Description: Updated messages that actually look like they belong.
Version:     1.0.9
Plugin URI:  https://morganestes.com/post-updated-messages-plugin/
Author:      Morgan Estes
Author URI:  https://morganestes.com/
Text Domain: post-updated-messages
Domain Path: /language/

License:     GPL v2 or later

Copyright © 2016 Morgan Estes

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

define( 'PUM_VERSION', '0.1.0' );

add_action( 'admin_init', 'pum_setup' );

function pum_setup() {

	add_filter( 'post_updated_messages', 'pum_single_messages', 10, 1 );
	add_filter( 'bulk_post_updated_messages', 'pum_bulk_messages', 10, 2 );
	//add_action( 'plugins_loaded', 'pum_load_plugin_translation' );

}

function pum_load_plugin_translation() {


}

/**
 * Get the post types to use in the plugin.
 *
 * @since 0.1.0
 *
 * @return array The filtered array of post types.
 */
function get_pum_post_types() {
	/**
	 * Filter the post types to exclude from custom messages.
	 *
	 * By default, all post types will have custom messages applied.
	 * Adding a post type to this list will use the default 'post' messages.
	 *
	 * @since 0.1.0
	 *
	 * @param array $post_types_nofilter Array of post type slugs to exclude. Default 'post' and 'page'.
	 */
	$post_types_nofilter = apply_filters( 'pum_post_types_nofilter', array( 'post', 'page' ) );

	if ( ! is_array( $post_types_nofilter ) && is_string( $post_types_nofilter ) ) {
		$post_types_nofilter = array( $post_types_nofilter );
	}

	return $post_types_nofilter;
}

/**
 * Customize the update messages for the post type.
 *
 * @since    0.1.0
 * @callback 'post_updated_messages' filter.
 *
 * @param array $messages A post-type-indexed array of message strings.
 * @return array The updated array of messages.
 */
function pum_single_messages( $messages ) {
	global $post_type, $post_type_object, $post;

	if ( in_array( $post_type, get_pum_post_types(), true ) ) {
		return $messages;
	}

	$permalink = get_permalink( $post->ID );
	if ( ! $permalink ) {
		$permalink = '';
	}

	$preview_post_link_html = $scheduled_post_link_html = $view_post_link_html = '';
	$scheduled_date         = date_i18n( __( 'M j, Y @ H:i' ), strtotime( $post->post_date ) );
	$preview_url            = get_preview_post_link( $post );
	$viewable               = is_post_type_viewable( $post_type_object );
	$labels                 = get_post_type_labels( $post_type_object );

	/* translators: unless otherwise noted, %s: post type label */
	$actions = array(
		'updated'       => __( '%s updated.', 'post-updated-messages' ),
		'draft_updated' => __( '%s draft updated.', 'post-updated-messages' ),
		'field_updated' => __( 'Custom field updated.', 'post-updated-messages' ),
		'field_deleted' => __( 'Custom field deleted.', 'post-updated-messages' ),
		'saved'         => __( '%s saved.', 'post-updated-messages' ),
		'submitted'     => __( '%s submitted.', 'post-updated-messages' ),
		'published'     => __( '%s published.', 'post-updated-messages' ),
		/* translators: 1: post type label, 2: scheduled publish date and time */
		'scheduled'     => __( '%1$s scheduled for: %2$s.', 'post-updated-messages' ),
		/* translators: 1: post type label, 2: date and time of the revision */
		'revision'      => __( '%1$s restored to revision from %2$s.' ),
		'preview'       => __( 'Preview %s.', 'post-updated-messages' ),
	);

	/**
	 * Filter the updated messages.
	 *
	 * The labels can be modified with the {@see "post_type_labels_{$post_type}"} filter
	 * prior to combining them with the actions strings. This provides one last chance to
	 * change the message before they're used.
	 *
	 * @since 0.1.0
	 *
	 * @param array $actions The strings for each of the actions performed on save.
	 */
	$actions = apply_filters( 'pum_post_actions', $actions );

	if ( ! is_array( $actions ) ) {
		return $messages;
	}

	if ( $viewable ) {
		// Preview post link.
		/* translators: 1: preview URL, 2: post type label */
		$preview_post_link_html = sprintf( '&nbsp;<a target="_blank" href="%1$s">%2$s</a>.',
			esc_url( $preview_url ),
			sprintf( esc_html( $actions['preview'] ), $labels->singular_name )
		);

		// Scheduled post preview link.
		/* translators: 1: preview URL, 2: post type label */
		$scheduled_post_link_html = sprintf( '&nbsp;<a target="_blank" href="%1$s">%2$s</a>.',
			esc_url( $permalink ),
			sprintf( esc_html( $actions['preview'] ), $labels->singular_name )
		);

		// View post link.
		/* translators: 1: preview URL, 2: "View Item" label */
		$view_post_link_html = sprintf( '&nbsp;<a href="%1$s">%2$s</a>.',
			esc_url( $permalink ),
			esc_html( $labels->view_item )
		);
	}

	$messages[ $post_type ] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( esc_html( $actions['updated'] ), $labels->singular_name ) . $view_post_link_html,
		2  => esc_html( $actions['field_updated'] ),
		3  => esc_html( $actions['field_deleted'] ),
		4  => sprintf( esc_html( $actions['updated'] ), $labels->singular_name ),
		5  => isset( $_GET['revision'] ) ?
			sprintf( esc_html( $actions['revision'] ),
				$labels->singular_name,
				wp_post_revision_title( (int) $_GET['revision'], false )
			) :
			false,
		6  => sprintf( esc_html( $actions['published'] ), $labels->singular_name ) . $view_post_link_html,
		7  => sprintf( esc_html( $actions['saved'] ), $labels->singular_name ),
		8  => sprintf( esc_html( $actions['submitted'] ), $labels->singular_name ) . $preview_post_link_html,
		9  => sprintf( esc_html( $actions['scheduled'] ), $labels->singular_name, '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
		10 => sprintf( esc_html( $actions['draft_updated'] ), $labels->singular_name ) . $preview_post_link_html,
	);

	return $messages;
}

/**
 * Add custom messages to the bulk actions for custom post types.
 *
 * @since    0.1.0
 * @callback 'bulk_post_updated_messages' filter.
 *
 * @param array $bulk_messages Message strings to filter.
 * @param array $bulk_counts   The counts for each of the message types.
 * @return array The custom messages for the appropriate count.
 */
function pum_bulk_messages( $bulk_messages, $bulk_counts ) {
	global $post_type, $post_type_object;

	$labels = get_post_type_labels( $post_type_object );

	/*
	 * Core runs the filtered strings through sprintf(), which means our string needs the '%s' placeholder for the count.
	 */
	$bulk_messages[ $post_type ] = array(
		/* translators: 1: the literal string '%s', 2: post type name */
		'updated'   => _n(
			sprintf( esc_html__( '%1$s %2$s updated.', 'post-updated-messages' ), '%s', $labels->singular_name ),
			sprintf( esc_html__( '%1$s %2$s updated.', 'post-updated-messages' ), '%s', $labels->name ),
			$bulk_counts['updated']
		),
		'locked'    => ( 1 === $bulk_counts['locked'] ) ?
			sprintf( esc_html__( '1 %s not updated, somebody is editing it.', 'post-updated-messages' ), $labels->singular_name ) :
			_n(
				sprintf( esc_html__( '%1$s %2$s not updated, somebody is editing it.', 'post-updated-messages' ), '%s', $labels->singular_name ),
				sprintf( esc_html__( '%1$s %2$s not updated, somebody is editing them.', 'post-updated-messages' ), '%s', $labels->name ),
				$bulk_counts['locked']
			),
		'deleted'   => _n(
			sprintf( esc_html__( '%1$s %2$s permanently deleted.', 'post-updated-messages' ), '%s', $labels->singular_name ),
			sprintf( esc_html__( '%1$s %2$s permanently deleted.', 'post-updated-messages' ), '%s', $labels->name ),
			$bulk_counts['deleted']
		),
		'trashed'   => _n(
			sprintf( esc_html__( '%1$s %2$s moved to the Trash.', 'post-updated-messages' ), '%s', $labels->singular_name ),
			sprintf( esc_html__( '%1$s %2$s moved to the Trash.', 'post-updated-messages' ), '%s', $labels->name ),
			$bulk_counts['trashed']
		),
		'untrashed' => _n(
			sprintf( esc_html__( '%1$s %2$s restored from the Trash.', 'post-updated-messages' ), '%s', $labels->singular_name ),
			sprintf( esc_html__( '%1$s %2$s restored from the Trash.', 'post-updated-messages' ), '%s', $labels->name ),
			$bulk_counts['untrashed']
		),
	);

	return $bulk_messages;
}
