<?php

declare(strict_types = 1);

namespace DigitalCreative\Jaqen\Tests\Feature\Fields;

use DigitalCreative\Jaqen\Services\Fields\FileField;
use DigitalCreative\Jaqen\Services\ResourceManager\AbstractResource;
use DigitalCreative\Jaqen\Tests\Factories\UserFactory;
use DigitalCreative\Jaqen\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Jaqen\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileFieldTest extends TestCase
{

    public function test_file_field_works(): void
    {

        $storage = Storage::fake('public');

        $this->createSampleResource();

        /**
         * @var UserModel $user
         */
        $user = UserModel::first();

        $this->assertStringEndsWith('.bin', $user->name);

        $storage->assertExists($user->name);

    }

    public function test_file_is_not_removed_not_replaced(): void
    {

        Storage::fake('public');

        $resource = $this->createSampleResource();

        /**
         * @var UserModel $user
         */
        $user = UserModel::first();

        $this->updateResponse($resource, $user->id, [ 'name' => $user->name ]);

        $this->assertStringEndsWith('.bin', $user->fresh()->name);

    }

    public function test_file_is_removed_if_null_is_sent(): void
    {

        $storage = Storage::fake('public');

        $resource = $this->createSampleResource();

        /**
         * @var UserModel $user
         */
        $user = UserModel::first();

        /**
         * Ensure file exists
         */
        $this->assertStringEndsWith('.bin', $user->name);
        $storage->assertExists($user->name);

        $this->updateResponse($resource, $user->id, [ 'name' => null ]);

        /**
         * Ensure file got removed
         */
        $storage->assertMissing($user->name);
        $this->assertNull($user->fresh()->name);

    }

    public function test_files_are_pruned_on_resource_delete(): void
    {

        $storage = Storage::fake('public');

        $user = UserFactory::new()->create([
            'name' => UploadedFile::fake()->image('name')->store('images'),
        ]);

        /**
         * @todo test when the pruneFile is false
         */
        $resource = $this->makeResource()
                         ->addDefaultFields(
                             FileField::make('Name')->pruneFile(true)
                         );

        $storage->assertMissing($user->name);

        $this->deleteResponse($resource, [ $user->id ]);

        $storage->assertMissing($user->name);
        $this->assertNull($user->fresh());

    }

    private function createSampleResource(): AbstractResource
    {

        $resource = $this->makeResource()
                         ->addDefaultFields(
                             FileField::make('Name')
                                      ->rulesForCreate([ 'file' ])
                                      ->pruneFile()
                         );

        $this->storeResponse($resource, [ 'name' => UploadedFile::fake()->image('name') ]);

        return $resource;

    }

}
