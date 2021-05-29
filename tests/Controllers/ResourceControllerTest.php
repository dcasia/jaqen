<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Controllers;

use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\CustomNameLabelUriResource;
use DigitalCreative\Jaqen\Tests\Fixtures\Resources\User as UserResource;
use DigitalCreative\Jaqen\Tests\TestCase;
use DigitalCreative\Jaqen\Tests\Traits\ResourceTrait;
use Illuminate\Database\Eloquent\Model;

class ResourceControllerTest extends TestCase
{

    use ResourceTrait;

    public function test_resource_list_api(): void
    {
        $this->registerResource(UserResource::class);

        $this->resourcesApi()
             ->assertStatus(200)
             ->assertJson([
                 [
                     'name' => 'User',
                     'label' => 'Users',
                     'uriKey' => 'users',
                 ],
             ]);
    }

    public function test_resource_custom_label_name_and_uri_are_respected(): void
    {
        $this->registerResource(new class extends AbstractResource {

            public static function uriKey(): string
            {
                return 'sample-uri';
            }

            public function name(): string
            {
                return 'sample-name';
            }

            public function label(): string
            {
                return 'sample-label';
            }

            public function model(): Model
            {
                return new User();
            }

        });

        $this->resourcesApi()
             ->assertStatus(200)
             ->assertJson([
                 [
                     'name' => 'sample-name',
                     'label' => 'sample-label',
                     'uriKey' => 'sample-uri',
                 ],
             ]);
    }

}
