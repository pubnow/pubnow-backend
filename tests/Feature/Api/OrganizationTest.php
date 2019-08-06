<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\Category;
use App\Models\Clap;
use App\Models\Comment;
use App\Models\InviteRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationTest extends TestCase
{
    protected $admin;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::where(['username' => 'admin'])->first();
        $this->user = factory(User::class)->create();
    }
    // --- Get all
    // Test can get all Organizations -> ok
    public function test_can_get_list_organizations() {
        $organizations = factory(Organization::class, 5)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->json('GET', '/api/organizations');

        $response->assertStatus(200);

        $response->assertJsonCount(count($organizations), 'data');

        $organizations->each(function ($organization) use ($response) {
            $response->assertJsonFragment([
                'name' => $organization->name,
                'description' => $organization->description,
                'email' => $organization->email,
                'following' => false,
            ]);
        });
    }

    // --- Get one
    public function test_can_get_an_organization() {
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->json('GET', '/api/organizations/'.$organization->slug);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => $organization->name,
            'description' => $organization->description,
            'email' => $organization->email,
            'following' => false,
        ]);
    }

    // Test get an organization, logged in, following
    public function test_can_get_an_organization_logged_in_following() {
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $organization->followers()->attach($this->user);

        $response = $this->actingAs($this->user)->json('GET', '/api/organizations/'.$organization->slug);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => $organization->name,
            'description' => $organization->description,
            'email' => $organization->email,
            'following' => true,
        ]);
    }

    // --- Create
    // Test can create a organization -> 201
    public function test_can_create_an_organization_if_logged_in() {
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('POST', '/api/organizations', [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => $organization->name,
            'description' => $organization->description,
            'email' => $organization->email,
        ]);
    }

    // Test create organization, not logged in -> 401
    public function test_cannot_create_an_organization_if_not_logged_in() {
        $organization = factory(Organization::class)->make();

        $response = $this->json('POST', '/api/organizations', [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(401);
    }

    // Test create organization, logged in, missing name field -> 401
    public function test_cannot_create_an_organization_if_logged_in_but_missing_name() {
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('POST', '/api/organizations', [
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(422);
    }

    // Test create a organization, logged in, dupplicate name -> 422
    public function test_cannot_create_an_organization_if_logged_in_but_dupplicate_name() {
        $exists = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('POST', '/api/organizations', [
            'name' => $exists->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(422);
    }

    // Test create a organization, logged in, email is not in format -> 201
    public function test_cannot_create_an_organization_if_logged_in_but_email_not_right_format() {
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('POST', '/api/organizations', [
            'name' => $organization->name,
            'email' => 'abc',
            'description' => $organization->description,
        ]);

        $response->assertStatus(422);
    }

    // --- Update
    // Test update a organization, logged in, owner -> 200
    public function test_can_update_an_organization_if_logged_in() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('PUT', '/api/organizations/'.$created->slug, [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => $organization->name,
            'description' => $organization->description,
            'email' => $organization->email,
        ]);
    }

    // Test update a organization, admin -> 200
    public function test_can_update_an_organization_if_logged_in_as_admin() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->admin)->json('PUT', '/api/organizations/'.$created->slug, [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
            'active' => 1
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => $organization->name,
            'description' => $organization->description,
            'email' => $organization->email,
        ]);
    }

    // Test update a organization, owner, update active -> 403
    public function test_cannot_update_an_organization_if_logged_in_as_owner_and_update_active() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('PUT', '/api/organizations/'.$created->slug, [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
            'active' => 1
        ]);

        $response->assertStatus(403);
    }

    // Test update organization, not logged in -> 401
    public function test_cannot_update_an_organization_if_not_logged_in() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->json('PUT', '/api/organizations/'.$created->slug, [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(401);
    }


    // Test update organization, logged in, not owner -> 401
    public function test_cannot_update_an_organization_if_not_owner() {
        $user = factory(User::class)->create();
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($user)->json('PUT', '/api/organizations/'.$created->slug, [
            'name' => $organization->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(403);
    }

    // Test update organization, logged in, organization not exists -> 404
    public function test_cannot_update_an_organization_if_logged_in_but_organization_not_exists() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $slug = $created->slug;
        $created->delete();
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('PUT', '/api/organizations/'.$slug, [
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(404);
    }

    // Test update a organization, logged in, dupplicate name -> 422
    public function test_cannot_update_an_organization_if_logged_in_but_dupplicate_name() {
        $createds = factory(Organization::class, 2)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('PUT', '/api/organizations/'.$createds[0]->slug, [
            'name' => $createds[1]->name,
            'email' => $organization->email,
            'description' => $organization->description,
        ]);

        $response->assertStatus(422);
    }

    // Test update a organization, logged in, email is not in format -> 201
    public function test_cannot_update_an_organization_if_logged_in_but_email_not_right_format() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);
        $organization = factory(Organization::class)->make();

        $response = $this->actingAs($this->user)->json('PUT', '/api/organizations/'.$created->slug, [
            'name' => $organization->name,
            'email' => 'abc',
            'description' => $organization->description,
        ]);

        $response->assertStatus(422);
    }

    // --- Delete
    // Test delete organization, logged in, owner
    public function test_can_delete_organization_if_logged_in_and_admin() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->json('DELETE', '/api/organizations/'.$created->slug);

        $response->assertStatus(204);
    }

    // Test delete organization, logged in, owner
    public function test_can_delete_organization_if_logged_in_and_owner() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->json('DELETE', '/api/organizations/'.$created->slug);

        $response->assertStatus(204);
    }


    // Test delete organization, logged in, owner
    public function test_cannot_delete_organization_if_not_logged_in() {
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->json('DELETE', '/api/organizations/'.$created->slug);

        $response->assertStatus(401);
    }


    // Test delete organization, logged in, owner
    public function test_cannot_delete_organization_if_logged_in_but_not_owner() {
        $user = factory(User::class)->create();
        $created = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->actingAs($user)->json('DELETE', '/api/organizations/'.$created->slug);

        $response->assertStatus(403);
    }

    // --- Get list members
    // Test get list members, active
    public function test_can_get_list_members() {
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
            'active' => true
        ]);
        $users = factory(User::class, 5)->create();

        $users->each(function ($user) use ($organization) {
            InviteRequest::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'status' => 'pending'
            ]);
        });

        $response = $this->json('GET', 'api/organizations/'.$organization->slug.'/members');

        $response->assertStatus(200);

        $response->assertJsonCount(count($users), 'data');
    }
    // Test get list members, not active
    public function test_cannot_get_list_members_if_not_active() {
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->json('GET', 'api/organizations/'.$organization->slug.'/members');

        $response->assertStatus(422);
    }

    // --- Follow Organization
    // Test follow organization, logged in, organization exists
    public function test_user_can_follow_an_exists_organization() {
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $user->id,
            'active' => true
        ]);

        $response = $this->actingAs($this->user)->json('POST', 'api/organizations/'.$organization->slug.'/follow');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => $this->user->name,
            'username' => $this->user->username,
            'email' => $this->user->email
        ]);

        $response->assertJsonCount(1, 'data.followingOrganizations');

        $response->assertJsonStructure([
            'data' => [
                'id', 'username', 'name', 'email', 'isAdmin', 'bio', 'avatar', 'role', 'followingOrganizations'
            ]
        ]);
    }
    // Test follow organization, logged in, organization exists, not active
    public function test_user_cannot_follow_an_exists_organization_not_active() {
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $user->id
        ]);

        $response = $this->actingAs($this->user)->json('POST', 'api/organizations/'.$organization->slug.'/follow');

        $response->assertStatus(422);
    }

    // Test follow organization, not logged in, organization exists
    public function test_guest_cannot_follow_an_exists_organization() {
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $user->id,
            'active' => true
        ]);

        $response = $this->json('POST', 'api/organizations/'.$organization->slug.'/follow');

        $response->assertStatus(401);
    }

    // Test follow organization, logged in, organization not exists
    public function test_user_cannot_follow_a_not_exists_organization() {
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $user->id,
            'active' => true
        ]);
        $slug = $organization->slug;
        $organization->delete();

        $response = $this->actingAs($this->user)->json('POST', 'api/organizations/'.$slug.'/follow');

        $response->assertStatus(404);
    }

    // Test follow organization, logged in, organization exists, followed
    public function test_user_can_follow_an_followed_organization() {
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $user->id,
            'active' => true
        ]);
        $this->user->followingOrganizations()->attach($organization);

        $response = $this->actingAs($this->user)->json('POST', 'api/organizations/'.$organization->slug.'/follow');

        $response->assertStatus(422);
    }

    // --- Unfollow organization
    // Test unfollow organization, logged in, organization exists, followed
    public function test_user_can_unfollow_a_followed_organization() {
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $user->id,
            'active' => true
        ]);
        $this->user->followingOrganizations()->attach($organization);

        $response = $this->actingAs($this->user)->json('DELETE', 'api/organizations/'.$organization->slug.'/follow');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'name' => $this->user->name,
            'username' => $this->user->username,
            'email' => $this->user->email
        ]);

        $response->assertJsonCount(0, 'data.followingOrganizations');

        $response->assertJsonStructure([
            'data' => [
                'id', 'username', 'name', 'email', 'isAdmin', 'bio', 'avatar', 'role', 'followingOrganizations'
            ]
        ]);
    }

    // Test unfollow organization, logged in, organization exists, not active
    public function test_user_cannot_unfollow_a_followed_organization_not_active() {
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $user->id
        ]);
        $this->user->followingOrganizations()->attach($organization);

        $response = $this->actingAs($this->user)->json('DELETE', 'api/organizations/'.$organization->slug.'/follow');

        $response->assertStatus(422);
    }

    // Test unfollow organization, not logged in, organization exists
    public function test_guest_cannot_unfollow_an_organization() {
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $user->id,
            'active' => true
        ]);
        $this->user->followingOrganizations()->attach($organization);

        $response = $this->json('DELETE', 'api/organizations/'.$organization->slug.'/follow');


        $response->assertStatus(401);
    }

    // Test unfollow organization, logged in, organization not exists
    public function test_user_cannot_unfollow_a_not_exists_organization() {
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $user->id,
            'active' => true
        ]);
        $slug = $organization->slug;
        $organization->delete();

        $response = $this->actingAs($this->user)->json('DELETE', 'api/organizations/'.$slug.'/follow');

        $response->assertStatus(404);
    }

    // Test unfollow organization, logged in, organization exists, followed
    public function test_user_can_unfollow_a_not_followed_organization() {
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $user->id,
            'active' => true
        ]);

        $response = $this->actingAs($this->user)->json('DELETE', 'api/organizations/'.$organization->slug.'/follow');

        $response->assertStatus(422);
    }

    // --- Followers
    // Test get list followers
    public function test_can_get_list_followers() {
        $users = factory(User::class, 5)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
            'active' => true
        ]);

        $users->each(function ($user) use ($organization) {
            $organization->followers()->attach($user);
        });

        $response = $this->json('GET', 'api/organizations/'.$organization->slug.'/followers');

        $response->assertStatus(200);
        $response->assertJsonCount(count($users), 'data');

        $users->each(function ($user) use ($response) {
            $response->assertJsonFragment([
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email
            ]);
        });
    }

    // Test get list followers, organization not active
    public function test_cannot_get_list_followers_organization_not_active() {
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
        ]);

        $response = $this->json('GET', 'api/organizations/'.$organization->slug.'/followers');

        $response->assertStatus(422);
    }

    // Test get list organization articles
    public function test_can_get_list_articles() {
        $category = factory(Category::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
            'active' => true
        ]);
        $articles = factory(Article::class, 5)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'organization_id' => $organization->id,
        ]);

        $response = $this->json('GET', 'api/organizations/'.$organization->slug.'/articles');

        $response->assertStatus(200);
        $response->assertJsonCount(count($articles), 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'slug', 'title', 'excerpt', 'seen_count', 'thumbnail', 'clapped', 'bookmarked',
                    'author', 'category', 'tags', 'claps', 'publishedAt', 'createdAt', 'updatedAt'
                ]
            ]
        ]);
    }

    // --- Get statistic
    // Test get organization statistic -> ok
    public function test_can_get_organization_statistic() {
        $category = factory(Category::class)->create();
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
            'active' => true
        ]);
        $organization->members()->attach($user, [
            'id' => DB::raw('gen_random_uuid()'),
            'status' => 'accepted'
        ]);
        $articles = factory(Article::class, 5)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'organization_id' => $organization->id,
        ]);

        Clap::create([
            'user_id' => $user->id,
            'article_id' => $articles[0]->id,
            'count' => 5
        ]);

        Comment::create([
            'user_id' => $user->id,
            'article_id' => $articles[0]->id,
            'content' => 'abc'
        ]);

        $start = date('Y-m-d');
        $end_date = strtotime("1 day", strtotime($start));
        $end = date("Y-m-d", $end_date);

        $response = $this->actingAs($this->user)->json('GET', 'api/organizations/'.$organization->slug.'/statistic', [
            'start' => $start,
            'end' => $end
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => [
                'members_count' => 1,
                'articles_count' => 5,
                'featured_member' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
                'featured_article' => [
                    'id' => $articles[0]->id,
                    'title' => $articles[0]->title,
                    'slug' => $articles[0]->slug,
                ],
                'articles_by_category' => [
                    [
                        'category' => [
                            'id' => $category->id,
                        ],
                        'count' => count($articles)
                    ]
                ],
                'articles_by_day' => [
                    [
                        'date' => $start,
                        'count' => count($articles)
                    ]
                ]
            ]
        ]);
    }

    // Test get organization statistic, organization not active -> 422
    public function test_cannot_get_organization_statistic_organization_not_active() {
        $category = factory(Category::class)->create();
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id
        ]);

        $start = date('Y-m-d');
        $end_date = strtotime("1 day", strtotime($start));
        $end = date("Y-m-d", $end_date);

        $response = $this->actingAs($this->user)->json('GET', 'api/organizations/'.$organization->slug.'/statistic', [
            'start' => $start,
            'end' => $end
        ]);

        $response->assertStatus(422);
    }

    // Test get organization statistic, not logged in -> 401
    public function test_cannot_get_organization_statistic_if_not_logged_in() {
        $category = factory(Category::class)->create();
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
            'active' => true
        ]);
        $organization->members()->attach($user, [
            'id' => DB::raw('gen_random_uuid()'),
            'status' => 'accepted'
        ]);
        $articles = factory(Article::class, 5)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'organization_id' => $organization->id,
        ]);

        Clap::create([
            'user_id' => $user->id,
            'article_id' => $articles[0]->id,
            'count' => 5
        ]);

        Comment::create([
            'user_id' => $user->id,
            'article_id' => $articles[0]->id,
            'content' => 'abc'
        ]);

        $start = date('Y-m-d');
        $end_date = strtotime("1 day", strtotime($start));
        $end = date("Y-m-d", $end_date);

        $response = $this->json('GET', 'api/organizations/'.$organization->slug.'/statistic', [
            'start' => $start,
            'end' => $end
        ]);

        $response->assertStatus(401);
    }

    // Test get organization statistic, logged in, not owner -> 403
    public function test_cannot_get_organization_statistic_if_not_owner() {
        $category = factory(Category::class)->create();
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
            'active' => true
        ]);
        $organization->members()->attach($user, [
            'id' => DB::raw('gen_random_uuid()'),
            'status' => 'accepted'
        ]);
        $articles = factory(Article::class, 5)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'organization_id' => $organization->id,
        ]);

        Clap::create([
            'user_id' => $user->id,
            'article_id' => $articles[0]->id,
            'count' => 5
        ]);

        Comment::create([
            'user_id' => $user->id,
            'article_id' => $articles[0]->id,
            'content' => 'abc'
        ]);

        $start = date('Y-m-d');
        $end_date = strtotime("1 day", strtotime($start));
        $end = date("Y-m-d", $end_date);

        $response = $this->actingAs($user)->json('GET', 'api/organizations/'.$organization->slug.'/statistic', [
            'start' => $start,
            'end' => $end
        ]);

        $response->assertStatus(403);
    }

    // Test get organization statistic, missing start date -> 422
    public function test_cannot_get_organization_statistic_if_missing_start() {
        $category = factory(Category::class)->create();
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
            'active' => true
        ]);
        $organization->members()->attach($user, [
            'id' => DB::raw('gen_random_uuid()'),
            'status' => 'accepted'
        ]);
        $articles = factory(Article::class, 5)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'organization_id' => $organization->id,
        ]);

        Clap::create([
            'user_id' => $user->id,
            'article_id' => $articles[0]->id,
            'count' => 5
        ]);

        Comment::create([
            'user_id' => $user->id,
            'article_id' => $articles[0]->id,
            'content' => 'abc'
        ]);

        $start = date('Y-m-d');
        $end_date = strtotime("1 day", strtotime($start));
        $end = date("Y-m-d", $end_date);

        $response = $this->actingAs($this->user)->json('GET', 'api/organizations/'.$organization->slug.'/statistic', [
            'end' => $end
        ]);

        $response->assertStatus(422);
    }

    // Test get organization statistic, start date not a date
    public function test_cannot_get_organization_statistic_if_start_is_not_date() {
        $category = factory(Category::class)->create();
        $user = factory(User::class)->create();
        $organization = factory(Organization::class)->create([
            'owner' => $this->user->id,
            'active' => true
        ]);
        $organization->members()->attach($user, [
            'id' => DB::raw('gen_random_uuid()'),
            'status' => 'accepted'
        ]);
        $articles = factory(Article::class, 5)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'organization_id' => $organization->id,
        ]);

        Clap::create([
            'user_id' => $user->id,
            'article_id' => $articles[0]->id,
            'count' => 5
        ]);

        Comment::create([
            'user_id' => $user->id,
            'article_id' => $articles[0]->id,
            'content' => 'abc'
        ]);

        $response = $this->actingAs($this->user)->json('GET', 'api/organizations/'.$organization->slug.'/statistic', [
            'start' => 'abc',
            'end' => 'abc'
        ]);

        $response->assertStatus(422);
    }

}
