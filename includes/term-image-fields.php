<?php
/**
 * Adds an image field to taxonomy term edit screens for taxonomies
 * that don't natively support term images (e.g. product_tag, pa_*).
 * product_cat is excluded — WooCommerce already handles it.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_enqueue_scripts', function ( string $hook ) {
	if ( ! in_array( $hook, [ 'edit-tags.php', 'term.php' ], true ) ) return;
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->taxonomy, wtb_get_image_field_taxonomies(), true ) ) return;
	wp_enqueue_media();
	wp_enqueue_script(
		'wtb-term-image-admin',
		WTB_PLUGIN_URL . 'assets/js/term-image-admin.js',
		[ 'jquery' ],
		WTB_VERSION,
		true
	);
	wp_enqueue_style(
		'wtb-term-image-admin',
		WTB_PLUGIN_URL . 'assets/css/term-image-admin.css',
		[],
		WTB_VERSION
	);
} );

function wtb_get_image_field_taxonomies(): array {
	$all = get_object_taxonomies( 'product', 'names' );
	return array_values( array_diff( $all, [ 'product_cat' ] ) );
}

function wtb_render_image_field( int $term_id ): void {
	$image_id  = $term_id ? (int) get_term_meta( $term_id, 'wtb_image_id', true ) : 0;
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
	wp_nonce_field( 'wtb_term_image_save', 'wtb_term_image_nonce' );
	?>
	<div class="wtb-term-image-field">
		<div class="wtb-image-preview" <?php echo $image_url ? '' : 'style="display:none"'; ?>>
			<img src="<?php echo esc_url( $image_url ); ?>" alt="" />
		</div>
		<input type="hidden" name="wtb_image_id" value="<?php echo esc_attr( $image_id ); ?>" />
		<button type="button" class="button wtb-upload-image">
			<?php echo $image_id
				? esc_html__( 'Change Image', 'woo-taxonomy-blocks' )
				: esc_html__( 'Upload Image', 'woo-taxonomy-blocks' ); ?>
		</button>
		<?php if ( $image_id ) : ?>
			<button type="button" class="button wtb-remove-image">
				<?php esc_html_e( 'Remove', 'woo-taxonomy-blocks' ); ?>
			</button>
		<?php else : ?>
			<button type="button" class="button wtb-remove-image" style="display:none">
				<?php esc_html_e( 'Remove', 'woo-taxonomy-blocks' ); ?>
			</button>
		<?php endif; ?>
	</div>
	<?php
}

function wtb_save_image_field( int $term_id ): void {
	if (
		empty( $_POST['wtb_term_image_nonce'] ) ||
		! wp_verify_nonce( sanitize_key( $_POST['wtb_term_image_nonce'] ), 'wtb_term_image_save' )
	) {
		return;
	}

	$image_id = isset( $_POST['wtb_image_id'] ) ? absint( $_POST['wtb_image_id'] ) : 0;

	if ( $image_id ) {
		update_term_meta( $term_id, 'wtb_image_id', $image_id );
	} else {
		delete_term_meta( $term_id, 'wtb_image_id' );
	}
}

// Register hooks per taxonomy after WooCommerce has registered its taxonomies (priority 5).
add_action( 'init', function () {
	foreach ( wtb_get_image_field_taxonomies() as $taxonomy ) {
		register_term_meta( $taxonomy, 'wtb_image_id', [
			'type'              => 'integer',
			'single'            => true,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
		] );

		add_action( "{$taxonomy}_add_form_fields", function () {
			echo '<div class="form-field">';
			echo '<label>' . esc_html__( 'Image', 'woo-taxonomy-blocks' ) . '</label>';
			wtb_render_image_field( 0 );
			echo '</div>';
		} );

		add_action( "{$taxonomy}_edit_form_fields", function ( WP_Term $term ) {
			echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Image', 'woo-taxonomy-blocks' ) . '</th><td>';
			wtb_render_image_field( $term->term_id );
			echo '</td></tr>';
		} );

		add_action( "created_{$taxonomy}", 'wtb_save_image_field' );
		add_action( "edited_{$taxonomy}", 'wtb_save_image_field' );
	}
}, 20 );
