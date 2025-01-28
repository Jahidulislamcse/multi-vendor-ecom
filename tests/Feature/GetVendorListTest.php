<?php

namespace Tests\Feature;

use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetVendorListTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_get_list_of_paginated_vendors(): void
    {
        Vendor::factory()->count(50)->create();

        $response = $this->getJson(route('vendors.index'));
        $response->assertJson([
            'meta' => [
                'current_page' => 1,
            ],
        ]);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'address',
                    'description',
                    'profile_picture',
                    'cover_picture',
                ],
            ],
        ]);
    }

    public function test_can_get_data_from_page_2(): void
    {
        Vendor::factory()->count(50)->create();

        $response = $this->getJson(route('vendors.index', [
            'page' => 2,
        ]));

        $response->assertJson([
            'meta' => [
                'current_page' => 2,
            ],
        ]);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'address',
                    'description',
                    'profile_picture',
                    'cover_picture',
                ],
            ],
        ]);
    }
}
