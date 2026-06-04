# WooCommerce Taxonomy Blocks

Two Gutenberg blocks for displaying WooCommerce taxonomy terms — categories, tags, brands, attributes — as a responsive grid or a hero-style showcase.

## Blocks

### Taxonomy Grid
Displays taxonomy terms as a card grid with image, name, and product count. Configurable columns, aspect ratio, hover effects, and card styling.

### Taxonomy Showcase
Hero-style panels — large featured image with overlay text and a call-to-action button. Optionally shows a product strip below each term. Suited for brands and collections.

## Requirements

- WordPress 6.3+
- WooCommerce (any recent version)
- PHP 8.0+

## Installation

1. Download the ZIP from the [latest release](../../releases/latest)
2. In WordPress admin: **Plugins → Add New → Upload Plugin**
3. Activate the plugin

## Term Images

`product_cat` uses WooCommerce's built-in thumbnail field.

For all other taxonomies (tags, attributes, custom), the plugin adds an **Image** field to the term edit screen. If no image is set, it automatically falls back to the featured image of the first product in that term.

Fallback chain:
```
WooCommerce thumbnail_id → custom wtb_image_id meta → first product's featured image → placeholder
```

## Customization

### Filters

```php
// Modify query args for Taxonomy Grid
add_filter( 'woo_taxonomy_blocks_grid_query_args', function ( $args, $attributes ) {
    return $args;
}, 10, 2 );

// Modify query args for Taxonomy Showcase
add_filter( 'woo_taxonomy_blocks_showcase_query_args', function ( $args, $attributes ) {
    return $args;
}, 10, 2 );

// Modify individual term data before rendering (Grid)
add_filter( 'woo_taxonomy_blocks_grid_term', function ( $term, $attributes ) {
    return $term;
}, 10, 2 );

// Modify individual term data before rendering (Showcase)
add_filter( 'woo_taxonomy_blocks_showcase_term', function ( $term, $attributes ) {
    return $term;
}, 10, 2 );
```

### CSS Custom Properties

Both blocks expose CSS custom properties on the wrapper element, making it easy to override styles from a theme:

```css
/* Taxonomy Grid */
.wp-block-woo-taxonomy-blocks-taxonomy-grid {
    --wtb-columns: 3;
    --wtb-aspect-ratio: 4/3;
    --wtb-border-radius: 8px;
    --wtb-gap: 1.5rem;
    --wtb-placeholder-color: #f0f0f0;
}

/* Taxonomy Showcase */
.wp-block-woo-taxonomy-blocks-taxonomy-showcase {
    --wtb-overlay-color: #000;
    --wtb-overlay-opacity: 0.4;
    --wtb-text-color: #fff;
    --wtb-border-radius: 12px;
    --wtb-gap: 2rem;
    --wtb-min-height: 400px;
}
```