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

/** Filters the WP_Term_Query args for the Taxonomy Grid block. */
$query_args = apply_filters( 'woo_taxonomy_blocks_grid_query_args', $query_args, $attributes );

$terms = get_terms( $query_args );

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	return '';
}

$css_vars = implode( ';', [
	'--wtb-columns:'      . $columns,
	'--wtb-aspect-ratio:' . esc_attr( $aspect_ratio ),
	'--wtb-border-radius:'. $border_radius . 'px',
	'--wtb-placeholder-color:' . esc_attr( $placeholder_color ),
] );

$classes = implode( ' ', array_filter( [
	'wtb-taxonomy-grid',
	'wtb-columns-' . $columns,
	'wtb-hover-' . sanitize_html_class( $hover_effect ),
	$card_shadow ? 'wtb-has-shadow' : '',
] ) );

$wrapper_attrs = get_block_wrapper_attributes( [
	'class' => $classes,
	'style' => $css_vars,
] );

ob_start();
?>
<div <?php echo $wrapper_attrs; ?>>
	<?php foreach ( $terms as $term ) :
		/** Filters each term object before rendering. */
		$term = apply_filters( 'woo_taxonomy_blocks_grid_term', $term, $attributes );

		$image_url = WTB_Term_Image::get_url( $term, $image_size, $placeholder_id );
		$srcset    = WTB_Term_Image::get_srcset( $term, $image_size, $placeholder_id );
		$term_link = get_term_link( $term );

		if ( is_wp_error( $term_link ) ) {
			continue;
		}
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
	<?php endforeach; ?>
</div>
<?php
return ob_get_clean();
