<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Feature\Fields;

use DigitalCreative\Dashboard\Fields\FileField;
use DigitalCreative\Dashboard\Http\Controllers\DeleteController;
use DigitalCreative\Dashboard\Http\Controllers\StoreController;
use DigitalCreative\Dashboard\Http\Controllers\UpdateController;
use DigitalCreative\Dashboard\Resources\AbstractResource;
use DigitalCreative\Dashboard\Tests\Factories\UserFactory;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;
use DigitalCreative\Dashboard\Tests\Traits\RequestTrait;
use DigitalCreative\Dashboard\Tests\Traits\ResourceTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileFieldTest extends TestCase
{

    use RequestTrait;
    use ResourceTrait;

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

        $request = $this->updateRequest($resource, $user->id, [ 'name' => $user->name ]);

        (new UpdateController())->update($request);

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

        $request = $this->updateRequest($resource, $user->id, [ 'name' => null ]);

        (new UpdateController())->update($request);

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

        $request = $this->deleteRequest($resource, [ $user->id ]);
        $storage->assertMissing($user->name);

        (new DeleteController())->delete($request);

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

        $request = $this->storeRequest($resource, [ 'name' => UploadedFile::fake()->image('name') ]);

        (new StoreController())->store($request);

        return $resource;

    }

}
