<?php

defined( 'ABSPATH' ) || exit;

class WTB_Term_Image {

	/**
	 * Returns the attachment ID for a term's image using a fallback chain:
	 * 1. WooCommerce native thumbnail (product_cat)
	 * 2. Custom term meta (wtb_image_id) — set via our admin field
	 * 3. Featured image of the first product in the term
	 */
	public static function get_id( WP_Term $term ): int {
		$id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
		if ( $id ) return $id;

		$id = (int) get_term_meta( $term->term_id, 'wtb_image_id', true );
		if ( $id ) return $id;

		$products = get_posts( [
			'post_type'      => 'product',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'tax_query'      => [ [
				'taxonomy' => $term->taxonomy,
				'terms'    => $term->term_id,
			] ],
		] );

		if ( $products ) {
			$id = (int) get_post_thumbnail_id( $products[0] );
			if ( $id ) return $id;
		}

		return 0;
	}

	public static function get_url( WP_Term $term, string $size = 'medium', int $placeholder_id = 0 ): string {
		$id = self::get_id( $term );

		if ( ! $id && $placeholder_id ) {
			$id = $placeholder_id;
		}

		if ( $id ) {
			$src = wp_get_attachment_image_url( $id, $size );
			if ( $src ) return $src;
		}

		return '';
	}

	public static function get_srcset( WP_Term $term, string $size = 'medium', int $placeholder_id = 0 ): string {
		$id = self::get_id( $term );

		if ( ! $id && $placeholder_id ) {
			$id = $placeholder_id;
		}

		if ( $id ) {
			$srcset = wp_get_attachment_image_srcset( $id, $size );
			if ( $srcset ) return $srcset;
		}

		return '';
	}
}
