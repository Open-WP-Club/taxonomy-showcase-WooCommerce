<?php

declare( strict_types=1 );

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class SaveImageFieldTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
        $_POST = [];

        // Provide simple pure implementations for WP helper functions used
        // inside wtb_save_image_field() that we don't need to assert on.
        Functions\when( 'sanitize_key' )->returnArg();
        Functions\when( 'absint' )->alias( static fn( $v ) => abs( (int) $v ) );
    }

    protected function tearDown(): void {
        $_POST = [];
        Monkey\tearDown();
        parent::tearDown();
    }

    // ── Early-exit cases ──────────────────────────────────────────────────────

    public function test_does_nothing_when_nonce_is_absent(): void {
        $_POST = [];

        Functions\expect( 'wp_verify_nonce' )->never();
        Functions\expect( 'update_term_meta' )->never();
        Functions\expect( 'delete_term_meta' )->never();

        wtb_save_image_field( 1 );
        $this->addToAssertionCount( 1 ); // Brain Monkey verifies the never() calls
    }

    public function test_does_nothing_when_nonce_is_invalid(): void {
        $_POST = [ 'wtb_term_image_nonce' => 'bad_nonce' ];

        Functions\expect( 'wp_verify_nonce' )
            ->once()->with( 'bad_nonce', 'wtb_term_image_save' )->andReturn( false );
        Functions\expect( 'update_term_meta' )->never();
        Functions\expect( 'delete_term_meta' )->never();

        wtb_save_image_field( 2 );
        $this->addToAssertionCount( 1 );
    }

    // ── Save / delete ─────────────────────────────────────────────────────────

    public function test_saves_image_meta_when_image_id_is_positive(): void {
        $_POST = [
            'wtb_term_image_nonce' => 'valid_nonce',
            'wtb_image_id'         => '42',
        ];

        Functions\expect( 'wp_verify_nonce' )
            ->once()->with( 'valid_nonce', 'wtb_term_image_save' )->andReturn( true );
        Functions\expect( 'update_term_meta' )
            ->once()->with( 5, 'wtb_image_id', 42 );

        wtb_save_image_field( 5 );
        $this->addToAssertionCount( 1 ); // Brain Monkey verifies call counts in tearDown
    }

    public function test_deletes_image_meta_when_image_id_is_zero(): void {
        $_POST = [
            'wtb_term_image_nonce' => 'valid_nonce',
            'wtb_image_id'         => '0',
        ];

        Functions\expect( 'wp_verify_nonce' )
            ->once()->with( 'valid_nonce', 'wtb_term_image_save' )->andReturn( true );
        Functions\expect( 'delete_term_meta' )
            ->once()->with( 5, 'wtb_image_id' );

        wtb_save_image_field( 5 );
        $this->addToAssertionCount( 1 );
    }

    public function test_deletes_image_meta_when_image_id_is_absent_from_post(): void {
        $_POST = [ 'wtb_term_image_nonce' => 'valid_nonce' ];

        Functions\expect( 'wp_verify_nonce' )
            ->once()->andReturn( true );
        Functions\expect( 'delete_term_meta' )
            ->once()->with( 7, 'wtb_image_id' );

        wtb_save_image_field( 7 );
        $this->addToAssertionCount( 1 );
    }
}
