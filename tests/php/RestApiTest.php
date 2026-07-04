<?php

declare( strict_types=1 );

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class RestApiTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    private function makeTaxonomy( string $name, string $label, bool $public ): object {
        $tax                    = new stdClass();
        $tax->name              = $name;
        $tax->label             = $label;
        $tax->publicly_queryable = $public;
        return $tax;
    }

    // ── wtb_rest_get_taxonomies() ─────────────────────────────────────────────

    public function test_returns_only_publicly_queryable_taxonomies(): void {
        Functions\expect( 'get_object_taxonomies' )
            ->once()
            ->with( 'product', 'objects' )
            ->andReturn( [
                'product_cat'        => $this->makeTaxonomy( 'product_cat', 'Categories', true ),
                'product_visibility' => $this->makeTaxonomy( 'product_visibility', 'Visibility', false ),
                'product_tag'        => $this->makeTaxonomy( 'product_tag', 'Tags', true ),
            ] );

        $result = wtb_rest_get_taxonomies();

        $this->assertCount( 2, $result );
        $this->assertSame( 'product_cat', $result[0]['value'] );
        $this->assertSame( 'Categories',  $result[0]['label'] );
        $this->assertSame( 'product_tag', $result[1]['value'] );
        $this->assertSame( 'Tags',        $result[1]['label'] );
    }

    public function test_returns_empty_array_when_no_public_taxonomies(): void {
        Functions\expect( 'get_object_taxonomies' )
            ->once()
            ->with( 'product', 'objects' )
            ->andReturn( [
                'internal' => $this->makeTaxonomy( 'internal', 'Internal', false ),
            ] );

        $this->assertSame( [], wtb_rest_get_taxonomies() );
    }

    public function test_returns_empty_array_when_product_has_no_taxonomies(): void {
        Functions\expect( 'get_object_taxonomies' )
            ->once()
            ->with( 'product', 'objects' )
            ->andReturn( [] );

        $this->assertSame( [], wtb_rest_get_taxonomies() );
    }

    public function test_result_entries_contain_only_value_and_label_keys(): void {
        Functions\expect( 'get_object_taxonomies' )
            ->once()
            ->andReturn( [
                'product_cat' => $this->makeTaxonomy( 'product_cat', 'Categories', true ),
            ] );

        $result = wtb_rest_get_taxonomies();

        $this->assertArrayHasKey( 'value', $result[0] );
        $this->assertArrayHasKey( 'label', $result[0] );
        $this->assertCount( 2, $result[0] );
    }
}
