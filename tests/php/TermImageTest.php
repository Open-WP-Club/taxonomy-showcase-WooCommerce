<?php

declare( strict_types=1 );

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class TermImageTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function term( int $id, string $taxonomy = 'product_cat' ): WP_Term {
        $t           = new WP_Term();
        $t->term_id  = $id;
        $t->taxonomy = $taxonomy;
        return $t;
    }

    // ── get_id() ──────────────────────────────────────────────────────────────

    public function test_get_id_returns_woocommerce_thumbnail_id(): void {
        Functions\expect( 'get_term_meta' )
            ->with( 101, 'thumbnail_id', true )
            ->once()
            ->andReturn( 42 );

        $this->assertSame( 42, WTB_Term_Image::get_id( $this->term( 101 ) ) );
    }

    public function test_get_id_falls_back_to_wtb_image_id_when_no_thumbnail(): void {
        Functions\expect( 'get_term_meta' )
            ->with( 102, 'thumbnail_id', true )->once()->andReturn( 0 );
        Functions\expect( 'get_term_meta' )
            ->with( 102, 'wtb_image_id', true )->once()->andReturn( 99 );

        $this->assertSame( 99, WTB_Term_Image::get_id( $this->term( 102 ) ) );
    }

    public function test_get_id_falls_back_to_first_product_featured_image(): void {
        Functions\expect( 'get_term_meta' )
            ->with( 103, 'thumbnail_id', true )->once()->andReturn( 0 );
        Functions\expect( 'get_term_meta' )
            ->with( 103, 'wtb_image_id', true )->once()->andReturn( 0 );
        Functions\expect( 'get_posts' )->once()->andReturn( [ 55 ] );
        Functions\expect( 'get_post_thumbnail_id' )->once()->with( 55 )->andReturn( 77 );

        $this->assertSame( 77, WTB_Term_Image::get_id( $this->term( 103 ) ) );
    }

    public function test_get_id_returns_zero_when_no_image_anywhere(): void {
        Functions\expect( 'get_term_meta' )
            ->with( 104, 'thumbnail_id', true )->once()->andReturn( 0 );
        Functions\expect( 'get_term_meta' )
            ->with( 104, 'wtb_image_id', true )->once()->andReturn( 0 );
        Functions\expect( 'get_posts' )->once()->andReturn( [] );

        $this->assertSame( 0, WTB_Term_Image::get_id( $this->term( 104 ) ) );
    }

    public function test_get_id_caches_result_so_get_posts_fires_only_once(): void {
        // Both meta keys miss → get_posts runs → result cached for second call.
        Functions\expect( 'get_term_meta' )
            ->with( 105, 'thumbnail_id', true )->once()->andReturn( 0 );
        Functions\expect( 'get_term_meta' )
            ->with( 105, 'wtb_image_id', true )->once()->andReturn( 0 );
        Functions\expect( 'get_posts' )->once()->andReturn( [] );

        $term = $this->term( 105 );
        WTB_Term_Image::get_id( $term );
        WTB_Term_Image::get_id( $term ); // cache hit — no extra DB calls
        $this->addToAssertionCount( 1 ); // Brain Monkey verifies call counts in tearDown
    }

    // ── get_url() ─────────────────────────────────────────────────────────────

    public function test_get_url_returns_attachment_url(): void {
        Functions\expect( 'get_term_meta' )
            ->with( 106, 'thumbnail_id', true )->andReturn( 10 );
        Functions\expect( 'wp_get_attachment_image_url' )
            ->once()->with( 10, 'medium' )->andReturn( 'https://example.com/img.jpg' );

        $this->assertSame(
            'https://example.com/img.jpg',
            WTB_Term_Image::get_url( $this->term( 106 ) )
        );
    }

    public function test_get_url_falls_back_to_placeholder_image(): void {
        Functions\expect( 'get_term_meta' )
            ->with( 107, 'thumbnail_id', true )->andReturn( 0 );
        Functions\expect( 'get_term_meta' )
            ->with( 107, 'wtb_image_id', true )->andReturn( 0 );
        Functions\expect( 'get_posts' )->once()->andReturn( [] );
        Functions\expect( 'wp_get_attachment_image_url' )
            ->once()->with( 9, 'medium' )->andReturn( 'https://example.com/placeholder.jpg' );

        $this->assertSame(
            'https://example.com/placeholder.jpg',
            WTB_Term_Image::get_url( $this->term( 107 ), 'medium', 9 )
        );
    }

    public function test_get_url_returns_empty_string_when_no_image_and_no_placeholder(): void {
        Functions\expect( 'get_term_meta' )
            ->with( 108, 'thumbnail_id', true )->andReturn( 0 );
        Functions\expect( 'get_term_meta' )
            ->with( 108, 'wtb_image_id', true )->andReturn( 0 );
        Functions\expect( 'get_posts' )->once()->andReturn( [] );

        $this->assertSame( '', WTB_Term_Image::get_url( $this->term( 108 ) ) );
    }

    // ── get_srcset() ─────────────────────────────────────────────────────────

    public function test_get_srcset_returns_srcset_string(): void {
        Functions\expect( 'get_term_meta' )
            ->with( 109, 'thumbnail_id', true )->andReturn( 10 );
        Functions\expect( 'wp_get_attachment_image_srcset' )
            ->once()->with( 10, 'medium' )->andReturn( 'img-300.jpg 300w, img-600.jpg 600w' );

        $this->assertSame(
            'img-300.jpg 300w, img-600.jpg 600w',
            WTB_Term_Image::get_srcset( $this->term( 109 ) )
        );
    }

    public function test_get_srcset_returns_empty_string_when_no_image(): void {
        Functions\expect( 'get_term_meta' )
            ->with( 110, 'thumbnail_id', true )->andReturn( 0 );
        Functions\expect( 'get_term_meta' )
            ->with( 110, 'wtb_image_id', true )->andReturn( 0 );
        Functions\expect( 'get_posts' )->once()->andReturn( [] );

        $this->assertSame( '', WTB_Term_Image::get_srcset( $this->term( 110 ) ) );
    }
}
