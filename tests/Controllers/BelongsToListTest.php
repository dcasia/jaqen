<?php

declare(strict_types = 1);

namespace DigitalCreative\Dashboard\Tests\Controller;

use DigitalCreative\Dashboard\Tests\Fixtures\Models\Article as ArticleModel;
use DigitalCreative\Dashboard\Tests\Fixtures\Models\User as UserModel;
use DigitalCreative\Dashboard\Tests\TestCase;

//class BelongsToListTest extends TestCase
//{
//
//    public function test_can_retrieve_a_list_with_all_available_resources(): void
//    {
//
//        /**
//         * @var ArticleModel $article
//         */
//        $article = factory(ArticleModel::class)->create();
//
//        factory(UserModel::class, 5)->create();
//
//        $this->getJson("/dashboard-api/belongs-to/articles/{$article->id}/user")
//             ->assertStatus(200)
//             ->assertJsonCount(6)
//             ->assertJsonStructure([
//                 [
//                     'id',
//                     'name',
//                     'email',
//                     'gender',
//                     'password',
//                 ]
//             ]);
//
//    }
//
//
//}
