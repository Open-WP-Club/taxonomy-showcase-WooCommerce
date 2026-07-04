<?php

declare( strict_types=1 );

require_once __DIR__ . '/../../vendor/autoload.php';

// Minimal constant expected by the plugin files.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

/**
 * Minimal WP class stubs — just the properties the plugin reads.
 */
class WP_Term {
    public int    $term_id     = 0;
    public string $taxonomy    = '';
    public string $name        = '';
    public int    $count       = 0;
    public string $description = '';
}

class WP_Error {
    public function __construct( private readonly string $message = '' ) {}
    public function get_error_message(): string { return $this->message; }
}

/**
 * No-op stubs for WP registration APIs called at module load time.
 * The callbacks themselves are tested directly; we never fire these hooks.
 */
function add_action( ...$args ): void {}
function add_filter( ...$args ): void {}

// Include the plugin files under test.
require_once __DIR__ . '/../../includes/class-term-image.php';
require_once __DIR__ . '/../../includes/term-image-fields.php';
require_once __DIR__ . '/../../includes/rest-api.php';
