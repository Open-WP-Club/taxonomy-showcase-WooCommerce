import { registerBlockType, registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import Edit from './edit';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );

registerBlockVariation( metadata.name, {
	name:        'categories-showcase',
	title:       __( 'Categories Showcase', 'woo-taxonomy-blocks' ),
	description: __( 'Hero-style panels for product categories.', 'woo-taxonomy-blocks' ),
	icon:        'category',
	attributes:  { taxonomy: 'product_cat', limit: 3, orderby: 'count', order: 'DESC' },
	isActive:    [ 'taxonomy' ],
} );

registerBlockVariation( metadata.name, {
	name:        'brands-showcase',
	title:       __( 'Brands Showcase', 'woo-taxonomy-blocks' ),
	description: __( 'Hero-style panels for brands with featured products.', 'woo-taxonomy-blocks' ),
	icon:        'star-filled',
	attributes:  { taxonomy: 'product_brand', limit: 4, showProducts: true, productsPerTerm: 4 },
	isActive:    [ 'taxonomy' ],
} );
