<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
//    use RefreshDatabase;
    /**
     * @test
     *
     * Visibility of the categories menu for simple user
     *
     */
    public function simpleUserCannotAccessCategories(){
        // create a fake user
        // factory method returns a collection if integer value is passed in
        // otherwise returns an object
        $user = User::factory()->create();

        // attempt to access the categories route
        // $user passed here needs to be a single object, not a collection
        $response = $this->actingAs($user)->get('categories');

        // check if 403 status code returned
        $response->assertStatus(403);
    }

    /**
     * @test
     *
     * Visibility of the categories menu for admin user
     *
     */
    public function adminUserCanAccessCategories(){
        $admin = User::factory()->create(['role_id' => 2]);

        // attempt to access the categories url
        $response = $this->actingAs($admin)->get('categories');

        $response->assertStatus(200);
    }

    /**
     * @test
     *
     * Visibility of the categories menu for publisher
     *
     */
    public function publisherUserCannotAccessCategories(){
        $publisher = User::factory()->create(['role_id' => 3]);

        $response = $this->actingAs($publisher)->get('categories');

        $response->assertStatus(403);
    }

    /**
     * @test
     *
     * Visibility of the 'User' column in articles table (simple user)
     *
     */
    public function userCannotSeeUserColumnInArticleTable(){
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('articles');
        $response->assertDontSee('User');
    }

    /**
     * @test
     *
     * Visibility of the 'User' column in articles table (Admin user)
     *
     */
    public function adminCanSeeUserColumnInArticleTable(){
        $admin = User::factory()->create(['role_id' => 2]);

        $response = $this->actingAs($admin)->get('articles');
        $response->assertSee('User');
    }

    /**
     * @test
     *
     * Visibility of the 'Published' checkbox in create article form (simple user)
     *
     */
    public function userCannotSeePublishedCheckbox(){
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('articles/create');
        $response->assertDontSee('Published');
    }

    /**
     * @test
     *
     * Visibility of the 'Published' checkbox in create article form (admin user)
     *
     */
    public function adminCanSeePublishedCheckbox(){
        $admin = User::factory()->create(['role_id' => 2]);

        $response = $this->actingAs($admin)->get('articles/create');
        $response->assertSee('Published');
    }

    /**
     * @test
     *
     * Visibility of the 'Published' checkbox in create article form (publisher)
     *
     */
    public function publisherCanSeePublishedCheckbox(){
        $publisher = User::factory()->create(['role_id' => 3]);

        $response = $this->actingAs($publisher)->get('articles/create');
        $response->assertSee('Published');
    }

    /**
     * @test
     *
     * Visibility of the 'Published' checkbox in edit article form (simple user)
     *
     */
    public function userCannotSeePublishedCheckboxInEditArticleForm(){
        $user = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('articles/' . $article->id . '/edit');
        $response->assertDontSee('Published');
    }

    /**
     * @test
     *
     * Visibility of the 'Published' checkbox in edit article form (admin user)
     *
     */
    public function adminCanSeePublishedCheckboxInEditArticleForm(){
        $admin = User::factory()->create(['role_id' => 2]);
        $article = Article::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->get('articles/' . $article->id . '/edit');
        $response->assertSee('Published');
    }

    /**
     * @test
     *
     * Visibility of the 'Published' checkbox in edit article form (publisher)
     *
     */
    public function publisherCanSeePublishedCheckboxInEditArticleForm(){
        $publisher = User::factory()->create(['role_id' => 3]);
        $article = Article::factory()->create(['user_id' => $publisher->id]);

        $response = $this->actingAs($publisher)->get('articles/' . $article->id . '/edit');
        $response->assertSee('Published');
    }

    /**
     * @test
     *
     * User should not be able to publish an article (either during creation or updating of an article)
     *
     */
    public function userCannotPublishArticle(){
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $articleData = ['title' => 'Title', 'full_text' => 'Full Text', 'published' => 1, 'category_id' => 1];
        // create an article via post as an authenticated user
        $response = $this->actingAs($user)->post('articles', $articleData);
        // check redirection to articles listing
        $response->assertRedirect();


        $article = Article::firstOrFail();
//        dd($article);
        // check that published_at is null as user shouldn't be able to publish post
        $this->assertNull($article->published_at);

        // update the article via put as the same authenticated user
        $response = $this->ActingAs($user)->put('articles/' . $article->id, $articleData);
        $response->assertRedirect();

        $article = Article::firstOrFail();
        $this->assertNull($article->published_at);
    }

    /**
     * @test
     *
     * Admin creates an article without publishing
     *
     */
    public function adminCanSaveAndNotPublishArticle(){
        $admin = User::factory()->create(['role_id' => 2]);
//        $admin = User::where('role_id', '=', 2)->firstOrFail();

        // omit the 'published' field
        $articleData = ['title' => 'Title', 'full_text' => 'Full Text'];
        $response = $this->actingAs($admin)->post('articles', $articleData);
        $response->assertRedirect();

        // retrieve the article that was just created
        $article = Article::where('user_id', '=', $admin->id)->latest()->first();
//        dd($article);
        // check that published_at is null
        $this->assertNull($article->published_at);

        // update article with the same data
        $response = $this->actingAs($admin)->put('articles/' . $article->id, $articleData);
        $response->assertRedirect();

        $article = Article::where('user_id', '=', $admin->id)->latest()->first();
        $this->assertNull($article->published_at);
    }

    /**
     * @test
     *
     * Publisher should be able to publish an article (either during creation or updating of an article)
     * Also allowed to unpublish an article when 'published' checkbox is empty
     *
     */
    public function publisherCanPublishAndUnpublishArticle(){
//        $publisher = User::factory()->create(['role_id' => 3]);
        $publisher = User::where('role_id', '=', 3)->firstOrFail();
        $articleData = ['title' => 'Title', 'full_text' => 'Full Text', 'published' => 1, 'category_id' => 1 ];
        $response = $this->actingAs($publisher)->post('articles', $articleData);
        $response->assertRedirect();

        $article = Article::where('user_id', '=', $publisher->id)->latest()->first();
//        dd($article);
        $this->assertNotNull($article->published_at);

        $articleData = ['title' => 'Title', 'full_text' => 'Full Text'];
        $response = $this->actingAs($publisher)->put('articles/' . $article->id, $articleData);
        $response->assertRedirect();

        $article = Article::where('user_id', '=', $publisher->id)->latest()->first();
//        dd($article);
        $this->assertNull($article->published_at);
    }

    /**
     * @test
     *
     * Admin should be able to publish an article (either during creation or updating of an article)
     * Also allowed to unpublish an article when 'published' checkbox is empty
     *
     */
    public function adminCanPublishAndUnpublishArticle(){
        $this->withoutExceptionHandling();

//        $admin = User::factory()->create(['role_id' => 2]);
        $admin = User::where('role_id', '=', '2')->firstOrFail();
//        dd($admin);
        $articleData = ['title' => 'Title', 'full_text' => 'Full Text', 'published' => 1 ];
        $response = $this->actingAs($admin)->post('articles', $articleData);
        $response->assertRedirect();

        $article = Article::where('user_id', '=', $admin->id)->latest()->first();
//        dd($article);
        $this->assertNotNull($article->published_at);

        $articleData = ['title' => 'Title', 'full_text' => ' Full Text'];
        $response = $this->actingAs($admin)->put('articles/' . $article->id, $articleData);
        $response->assertRedirect();

        $article = Article::where('user_id', '=', $admin->id)->latest()->first();
        $this->assertNull($article->published_at);
    }
}
