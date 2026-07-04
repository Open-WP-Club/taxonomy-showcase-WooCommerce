<?php
/**
 * Render callback for the Taxonomy Showcase block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block inner content (unused — dynamic block).
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$taxonomy          = $attributes['taxonomy']       ?? 'product_cat';
$term_ids          = $attributes['termIds']        ?? [];
$limit             = max( 1, (int) ( $attributes['limit']   ?? 3 ) );
$orderby           = $attributes['orderby']        ?? 'count';
$order             = strtoupper( $attributes['order'] ?? 'DESC' );
$hide_empty        = (bool) ( $attributes['hideEmpty']        ?? true );
$image_size        = $attributes['imageSize']      ?? 'large';
$min_height        = max( 100, (int) ( $attributes['minHeight']   ?? 400 ) );
$border_radius     = (int) ( $attributes['borderRadius']     ?? 12 );
$overlay_color     = $attributes['overlayColor']   ?? '#000000';
$overlay_opacity   = max( 0, min( 100, (int) ( $attributes['overlayOpacity'] ?? 40 ) ) );
$text_color        = $attributes['textColor']      ?? '#ffffff';
$show_count        = (bool) ( $attributes['showCount']        ?? true );
$show_description  = (bool) ( $attributes['showDescription']  ?? true );
$button_text       = $attributes['buttonText']     ?? __( 'Shop Now', 'woo-taxonomy-blocks' );
$button_style      = $attributes['buttonStyle']    ?? 'outline';
$show_products     = (bool) ( $attributes['showProducts']     ?? false );
$products_per_term = max( 1, (int) ( $attributes['productsPerTerm'] ?? 4 ) );
$placeholder_id    = (int) ( $attributes['placeholderImageId'] ?? 0 );
$placeholder_color = $attributes['placeholderColor'] ?? '#cccccc';
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

/** Filters the WP_Term_Query args for the Taxonomy Showcase block. */
$query_args = apply_filters( 'woo_taxonomy_blocks_showcase_query_args', $query_args, $attributes );

$terms = get_terms( $query_args );

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	return '';
}

// Convert overlay opacity (0-100) to CSS decimal (0.0–1.0).
$overlay_opacity_css = round( $overlay_opacity / 100, 2 );

$css_vars = implode( ';', [
	'--wtb-overlay-color:'   . esc_attr( $overlay_color ),
	'--wtb-overlay-opacity:' . $overlay_opacity_css,
	'--wtb-text-color:'      . esc_attr( $text_color ),
	'--wtb-border-radius:'   . $border_radius . 'px',
	'--wtb-min-height:'      . $min_height . 'px',
	'--wtb-placeholder-color:' . esc_attr( $placeholder_color ),
] );

$classes = implode( ' ', array_filter( [
	'wtb-taxonomy-showcase',
	'wtb-btn-' . sanitize_html_class( $button_style ),
	$show_products ? 'wtb-has-products' : '',
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
		$term = apply_filters( 'woo_taxonomy_blocks_showcase_term', $term, $attributes );

		$image_url = WTB_Term_Image::get_url( $term, $image_size, $placeholder_id );
		$srcset    = WTB_Term_Image::get_srcset( $term, $image_size, $placeholder_id );
		$term_link = get_term_link( $term );

		if ( is_wp_error( $term_link ) ) {
			continue;
		}
	?>
		<div class="wtb-showcase-item">
			<?php if ( $image_url ) : ?>
				<div class="wtb-showcase-item__image" aria-hidden="true">
					<img
						src="<?php echo esc_url( $image_url ); ?>"
						<?php if ( $srcset ) : ?>
							srcset="<?php echo esc_attr( $srcset ); ?>"
							sizes="100vw"
						<?php endif; ?>
						alt=""
						loading="lazy"
					/>
				</div>
			<?php else : ?>
				<div class="wtb-showcase-item__image wtb-showcase-item__placeholder" aria-hidden="true"></div>
			<?php endif; ?>

			<div class="wtb-showcase-item__overlay" aria-hidden="true"></div>

			<div class="wtb-showcase-item__content">
				<a href="<?php echo esc_url( $term_link ); ?>" class="wtb-showcase-item__link">
					<span class="wtb-showcase-item__name"><?php echo esc_html( $term->name ); ?></span>

					<?php if ( $show_count ) : ?>
						<span class="wtb-showcase-item__count">
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
						<p class="wtb-showcase-item__description">
							<?php echo esc_html( wp_trim_words( $term->description, 20 ) ); ?>
						</p>
					<?php endif; ?>

					<?php if ( $button_text ) : ?>
						<span class="wtb-showcase-item__button"><?php echo esc_html( $button_text ); ?></span>
					<?php endif; ?>
				</a>
			</div>

			<?php if ( $show_products ) :
				$products = get_posts( [
					'post_type'      => 'product',
					'posts_per_page' => $products_per_term,
					'post_status'    => 'publish',
					'tax_query'      => [ [
						'taxonomy' => $term->taxonomy,
						'terms'    => $term->term_id,
					] ],
				] );

				if ( $products ) :
			?>
				<div class="wtb-showcase-products" style="--wtb-product-columns:<?php echo (int) $products_per_term; ?>">
					<?php foreach ( $products as $product_post ) :
						$product_link  = get_permalink( $product_post->ID );
						$product_thumb = get_the_post_thumbnail_url( $product_post->ID, 'woocommerce_thumbnail' );
						$product_title = get_the_title( $product_post->ID );
					?>
						<a class="wtb-product-card" href="<?php echo esc_url( $product_link ); ?>">
							<?php if ( $product_thumb ) : ?>
								<img src="<?php echo esc_url( $product_thumb ); ?>" alt="<?php echo esc_attr( $product_title ); ?>" loading="lazy" />
							<?php endif; ?>
							<span class="wtb-product-card__title"><?php echo esc_html( $product_title ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; endif; ?>
		</div>
	<?php endforeach; ?>
</div>
<?php
return ob_get_clean();
