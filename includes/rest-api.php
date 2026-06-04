<?php

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', function () {
	register_rest_route( 'woo-taxonomy-blocks/v1', '/taxonomies', [
		'methods'             => 'GET',
		'callback'            => 'wtb_rest_get_taxonomies',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
	] );
} );

function wtb_rest_get_taxonomies(): array {
	$result = [];
	foreach ( get_object_taxonomies( 'product', 'objects' ) as $taxonomy ) {
		$result[] = [
			'value' => $taxonomy->name,
			'label' => $taxonomy->label,
		];
	}
	return $result;
}
