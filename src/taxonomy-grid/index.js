import { registerBlockType, registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import Edit from './edit';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );

registerBlockVariation( metadata.name, {
	name:        'categories-grid',
	title:       __( 'Categories Grid', 'woo-taxonomy-blocks' ),
	description: __( 'Product categories as a card grid.', 'woo-taxonomy-blocks' ),
	icon:        'category',
	attributes:  { taxonomy: 'product_cat', columns: 3 },
	isActive:    [ 'taxonomy' ],
} );

registerBlockVariation( metadata.name, {
	name:        'tags-grid',
	title:       __( 'Tags Grid', 'woo-taxonomy-blocks' ),
	description: __( 'Product tags as a card grid.', 'woo-taxonomy-blocks' ),
	icon:        'tag',
	attributes:  { taxonomy: 'product_tag', columns: 4, showCount: true },
	isActive:    [ 'taxonomy' ],
} );

registerBlockVariation( metadata.name, {
	name:        'brands-grid',
	title:       __( 'Brands Grid', 'woo-taxonomy-blocks' ),
	description: __( 'Product brands as a logo grid.', 'woo-taxonomy-blocks' ),
	icon:        'star-filled',
	attributes:  { taxonomy: 'product_brand', columns: 4, showCount: false, showDescription: false },
	isActive:    [ 'taxonomy' ],
} );
