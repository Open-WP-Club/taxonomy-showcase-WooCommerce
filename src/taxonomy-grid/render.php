<?php
/**
 * Render callback for the Taxonomy Grid block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — dynamic block).
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$taxonomy          = $attributes['taxonomy']          ?? 'product_cat';
$term_ids          = $attributes['termIds']           ?? [];
$limit             = max( 1, (int) ( $attributes['limit']   ?? 6 ) );
$orderby           = $attributes['orderby']           ?? 'name';
$order             = strtoupper( $attributes['order'] ?? 'ASC' );
$hide_empty        = (bool) ( $attributes['hideEmpty']        ?? true );
$columns           = max( 1, min( 6, (int) ( $attributes['columns'] ?? 3 ) ) );
$aspect_ratio      = $attributes['aspectRatio']       ?? '4/3';
$image_size        = $attributes['imageSize']         ?? 'medium';
$show_count        = (bool) ( $attributes['showCount']        ?? true );
$show_description  = (bool) ( $attributes['showDescription']  ?? false );
$border_radius     = (int) ( $attributes['cardBorderRadius']  ?? 8 );
$card_shadow       = (bool) ( $attributes['cardShadow']       ?? true );
$hover_effect      = $attributes['hoverEffect']       ?? 'lift';
$placeholder_id    = (int) ( $attributes['placeholderImageId'] ?? 0 );
$placeholder_color = $attributes['placeholderColor']  ?? '#f0f0f0';
$show_alphabet     = (bool) ( $attributes['showAlphabetFilter'] ?? false );
$show_search       = (bool) ( $attributes['showSearch']         ?? false );
$search_placeholder = $attributes['searchPlaceholder'] ?? '';
$exclude_term_ids  = array_map( 'absint', $attributes['excludeTermIds'] ?? [] );

if ( ! taxonomy_exists( $taxonomy ) ) {
	return '';
}

$query_args = [
	'taxonomy'   => $taxonomy,
	'hide_empty' => $hide_empty,
	'number'     => $limit,
	'orderby'    => $orderby,
	'order'      => $order,
];

if ( ! empty( $term_ids ) ) {
	$query_args['include'] = array_map( 'absint', $term_ids );
}

if ( ! empty( $exclude_term_ids ) ) {
	$query_args['exclude'] = $exclude_term_ids;
}

// Alphabet mode needs alphabetical order to make sense.
if ( $show_alphabet ) {
	$query_args['orderby'] = 'name';
	$query_args['order']   = 'ASC';
}

/** Filters the WP_Term_Query args for the Taxonomy Grid block. */
$query_args = apply_filters( 'woo_taxonomy_blocks_grid_query_args', $query_args, $attributes );

$terms = get_terms( $query_args );

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	return '';
}

// ── Wrapper attributes ────────────────────────────────────────────────────────

$css_vars = implode( ';', [
	'--wtb-columns:'           . $columns,
	'--wtb-aspect-ratio:'      . esc_attr( $aspect_ratio ),
	'--wtb-border-radius:'     . $border_radius . 'px',
	'--wtb-placeholder-color:' . esc_attr( $placeholder_color ),
] );

$classes = implode( ' ', array_filter( [
	'wtb-taxonomy-grid',
	'wtb-columns-' . $columns,
	'wtb-hover-' . sanitize_html_class( $hover_effect ),
	$card_shadow     ? 'wtb-has-shadow'  : '',
	$show_alphabet   ? 'wtb-has-alphabet' : '',
] ) );

$extra_parts = [];
if ( $show_alphabet ) $extra_parts[] = 'data-alpha="1"';
if ( $show_search )   $extra_parts[] = 'data-search="1"';
$extra_attrs = implode( ' ', $extra_parts );

$wrapper_attrs = get_block_wrapper_attributes( [
	'class' => $classes,
	'style' => $css_vars,
] );

// ── Card renderer (closure to avoid global function conflicts) ────────────────

$render_card = static function ( WP_Term $term, string $term_link, string $image_url, string $srcset ) use ( $show_count, $show_description, $columns ): string {
	ob_start();
	?>
	<a class="wtb-card" href="<?php echo esc_url( $term_link ); ?>">
		<div class="wtb-card__image-wrap">
			<?php if ( $image_url ) : ?>
				<img
					src="<?php echo esc_url( $image_url ); ?>"
					<?php if ( $srcset ) : ?>
						srcset="<?php echo esc_attr( $srcset ); ?>"
						sizes="(max-width: 600px) 100vw, <?php echo esc_attr( round( 100 / $columns ) ); ?>vw"
					<?php endif; ?>
					alt="<?php echo esc_attr( $term->name ); ?>"
					loading="lazy"
				/>
			<?php else : ?>
				<div class="wtb-card__placeholder" aria-hidden="true"></div>
			<?php endif; ?>
		</div>
		<div class="wtb-card__body">
			<span class="wtb-card__name"><?php echo esc_html( $term->name ); ?></span>
			<?php if ( $show_count ) : ?>
				<span class="wtb-card__count">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: product count */
							_n( '%d product', '%d products', $term->count, 'woo-taxonomy-blocks' ),
							$term->count
						)
					);
					?>
				</span>
			<?php endif; ?>
			<?php if ( $show_description && $term->description ) : ?>
				<p class="wtb-card__description">
					<?php echo esc_html( wp_trim_words( $term->description, 12 ) ); ?>
				</p>
			<?php endif; ?>
		</div>
	</a>
	<?php
	return ob_get_clean();
};

// ── Render ────────────────────────────────────────────────────────────────────

ob_start();
echo '<div ' . $wrapper_attrs;
if ( $extra_attrs ) echo ' ' . $extra_attrs;
echo '>';

if ( $show_search ) {
	$placeholder = $search_placeholder ?: __( 'Search…', 'woo-taxonomy-blocks' );
	printf(
		'<div class="wtb-search"><input class="wtb-search__input" type="search" placeholder="%s" aria-label="%s" /></div>',
		esc_attr( $placeholder ),
		esc_attr__( 'Filter terms', 'woo-taxonomy-blocks' )
	);
}

if ( $show_alphabet ) {

	// Group terms by first letter; non-Latin chars go under '#'.
	$grouped = [];
	foreach ( $terms as $term ) {
		$term   = apply_filters( 'woo_taxonomy_blocks_grid_term', $term, $attributes );
		$first  = mb_strtoupper( mb_substr( $term->name, 0, 1, 'UTF-8' ), 'UTF-8' );
		$letter = preg_match( '/^[A-Z]$/', $first ) ? $first : '#';

		$term_link = get_term_link( $term );
		if ( is_wp_error( $term_link ) ) continue;

		$grouped[ $letter ][] = [
			'term'       => $term,
			'term_link'  => $term_link,
			'image_url'  => WTB_Term_Image::get_url( $term, $image_size, $placeholder_id ),
			'srcset'     => WTB_Term_Image::get_srcset( $term, $image_size, $placeholder_id ),
		];
	}

	// Keep A–Z sorted; move '#' to end.
	ksort( $grouped );
	if ( isset( $grouped['#'] ) ) {
		$other = $grouped['#'];
		unset( $grouped['#'] );
		$grouped['#'] = $other;
	}

	$block_uid     = wp_unique_id( 'wtb-grid-' );
	$active_letters = array_filter( array_keys( $grouped ), static fn( $l ) => $l !== '#' );

	// ── Alphabet nav ──────────────────────────────────────────────────────────
	echo '<nav class="wtb-alpha-nav" aria-label="' . esc_attr__( 'Browse by letter', 'woo-taxonomy-blocks' ) . '">';

	foreach ( range( 'A', 'Z' ) as $letter ) {
		if ( in_array( $letter, $active_letters, true ) ) {
			printf(
				'<a class="wtb-alpha-nav__btn" href="#%s" data-letter="%s">%s</a>',
				esc_attr( $block_uid . '-' . $letter ),
				esc_attr( $letter ),
				esc_html( $letter )
			);
		} else {
			printf( '<span class="wtb-alpha-nav__btn is-empty">%s</span>', esc_html( $letter ) );
		}
	}

	if ( isset( $grouped['#'] ) ) {
		printf(
			'<a class="wtb-alpha-nav__btn" href="#%s" data-letter="#">#</a>',
			esc_attr( $block_uid . '-other' )
		);
	}

	echo '</nav>';

	// ── Letter groups ─────────────────────────────────────────────────────────
	foreach ( $grouped as $letter => $items ) {
		$group_id = $block_uid . '-' . ( $letter === '#' ? 'other' : $letter );
		printf(
			'<div class="wtb-letter-group" id="%s" data-letter="%s">',
			esc_attr( $group_id ),
			esc_attr( $letter )
		);
		printf( '<h3 class="wtb-letter-group__heading">%s</h3>', esc_html( $letter ) );
		echo '<div class="wtb-letter-group__grid">';

		foreach ( $items as $item ) {
			echo $render_card( $item['term'], $item['term_link'], $item['image_url'], $item['srcset'] );
		}

		echo '</div></div>';
	}

} else {

	foreach ( $terms as $term ) {
		$term = apply_filters( 'woo_taxonomy_blocks_grid_term', $term, $attributes );

		$term_link = get_term_link( $term );
		if ( is_wp_error( $term_link ) ) continue;

		echo $render_card(
			$term,
			$term_link,
			WTB_Term_Image::get_url( $term, $image_size, $placeholder_id ),
			WTB_Term_Image::get_srcset( $term, $image_size, $placeholder_id )
		);
	}
}

echo '</div>';
return ob_get_clean();
