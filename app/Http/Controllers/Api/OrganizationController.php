<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Organization\CreateOrganization;
use App\Http\Requests\Api\Organization\FollowOrganization;
use App\Http\Requests\Api\Organization\OrganizationStatistic;
use App\Http\Requests\Api\Organization\UpdateOrganization;
use App\Http\Resources\ArticleOnlyResource;
use App\Http\Resources\InviteRequestResource;
use App\Http\Resources\OrganizationMemberResource;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\OrganizationStatisticResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserWithFollowingOrganizationsResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\InviteRequest;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'])->except(['index', 'show', 'members', 'followers', 'articles']);
        $this->authorizeResource(Organization::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return OrganizationResource::collection(Organization::all()->sortBy('created_at'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateOrganization $request)
    {
        $user = $request->user();
        $data = $request->except('active');
        $data['owner'] = $user->id;
        $data['active'] = 0;
        $data['slug'] = str_slug($data['name']) . '-' . base_convert(time(), 10, 36);
        $organization = Organization::create($data);
        $organization->followers()->attach($user);
        InviteRequest::create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'status' => 'accepted'
        ]);
        return new OrganizationResource($organization);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization)
    {
        return new OrganizationResource($organization);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOrganization $request, Organization $organization)
    {
        $user = $request->user();
        if ($request->has('active') && !$user->isAdmin()) {
            return response()->json([
                'errors' => [
                    'message' => 'Only admin can active organization',
                ]
            ], 403);
        }
        $data = $request->all();
        if ($request->has('name') && !empty($data['name'])) {
            $data['slug'] = str_slug($data['name']) . '-' . base_convert(time(), 10, 36);
        }
        $organization->update($data);
        return new OrganizationResource($organization);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();
        return response()->json(null, 204);
    }

    public function members(Request $request, Organization $organization)
    {
        if (!$organization->active) {
            return response()->json([
                'errors' => [
                    'message' => 'Organization not activated',
                ]
            ], 422);
        }
        return OrganizationMemberResource::collection($organization->members);
    }

    // Get users who followed this user
    public function followers(Organization $organization)
    {
        if (!$organization->active) {
            return response()->json([
                'errors' => [
                    'message' => 'Organization not activated',
                ]
            ], 422);
        }
        return UserResource::collection($organization->followers);
    }

    public function follow(Request $request, Organization $organization)
    {
        if (!$organization->active) {
            return response()->json([
                'errors' => [
                    'message' => 'Organization not activated',
                ]
            ], 422);
        }
        $user = $request->user();
        if ($user->followingOrganizations()->find($organization->id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Already followed this organization',
                ]
            ], 422);
        }
        $user->followingOrganizations()->attach($organization);
        return new UserWithFollowingOrganizationsResource($user);
    }

    public function unfollow(Request $request, Organization $organization)
    {
        if (!$organization->active) {
            return response()->json([
                'errors' => [
                    'message' => 'Organization not activated',
                ]
            ], 422);
        }
        $user = $request->user();
        if (!$user->followingOrganizations()->find($organization->id)) {
            return response()->json([
                'errors' => [
                    'message' => 'Has not followed this organization yet',
                ]
            ], 422);
        }
        $user->followingOrganizations()->detach($organization);
        return new UserWithFollowingOrganizationsResource($user);
    }

    public function statistic(OrganizationStatistic $request, Organization $organization)
    {
        if (!$organization->active) {
            return response()->json([
                'errors' => [
                    'message' => 'Organization not activated',
                ]
            ], 422);
        }
        $this->authorize('statistic', $organization);

        $featuredMember = $organization->members()->wherePivot('status', 'accepted')->get()->sortByDesc(function ($member) use ($organization) {
            return $member->articles->where('organization_id', $organization->id)->count();
        })->take(3);

        $featuredArticle = $organization->articles->sortByDesc(function ($article) use ($organization) {
            return $article->claps->sum('count') + $article->comments->count();
        })->take(5);

        $articlesByCategories = $organization->articles()
            ->select('category_id', DB::raw('count(*) as count'))
            ->groupBy('category_id')
            ->orderBy('count', 'DESC')
            ->get();

        $roundChartData = collect($articlesByCategories)->map(function ($articlesByCategory) {
            $category = Category::find($articlesByCategory->category_id);
            return [
                'category' => $category,
                'count' => $articlesByCategory->count
            ];
        });
        $start = date($request->input('start'));
        $end_date = strtotime("1 day", strtotime($request->input('end')));
        $end = date("Y-m-d", $end_date);
        $articlesByDay = $organization->articles()
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        return response()->json([
            'data' => [
                'members_count' => $organization->members->count(),
                'articles_count' => $organization->articles->count(),
                'featured_member' => UserResource::collection($featuredMember),
                'featured_article' => ArticleOnlyResource::collection($featuredArticle),
                'articles_by_category' => $roundChartData,
                'articles_by_day' => $articlesByDay
            ]
        ], 200);
    }

    public function articles(Request $request, Organization $organization)
    {
        if (!$organization->active) {
            return response()->json([
                'errors' => [
                    'message' => 'Organization not activated',
                ]
            ], 422);
        }
        $articles = $organization->articles()->where('organization_private', false);
        if ($request->user()) {
            $user = $request->user();
            if ($organization->members()->find($user->id)) {
                $articles = $articles->union($organization->articles()->where('organization_private', true));
            }
        }
        $articles = $articles->latest()->paginate(10);
        return ArticleOnlyResource::collection($articles);
    }

    public function featured(Request $request, Organization $organization)
    {
        if (!$organization->active) {
            return response()->json([
                'errors' => [
                    'message' => 'Organization not activated',
                ]
            ], 422);
        }
        $articles = $organization->articles()->where('organization_private', false);
        if ($request->user()) {
            $user = $request->user();
            if ($organization->members()->find($user->id)) {
                $articles = $articles->union($organization->articles()->where('organization_private', true));
            }
        }
        $articles = $articles->with('claps')->with('comments')->get()->sortByDesc(function ($article) {
            return $article->claps->sum('count') + $article->comments->count();
        })->take(5)->sortByDesc(function ($article) {
            return $article->created_at;
        });

        return ArticleOnlyResource::collection($articles);
    }

    public function popular(Request $request, Organization $organization)
    {
        if (!$organization->active) {
            return response()->json([
                'errors' => [
                    'message' => 'Organization not activated',
                ]
            ], 422);
        }
        $articles = $organization->articles()->where('organization_private', false);
        if ($request->user()) {
            $user = $request->user();
            if ($organization->members()->find($user->id)) {
                $articles = $articles->union($organization->articles()->where('organization_private', true));
            }
        }
        $articles = $articles->orderBy('seen_count', 'desc')->take(5)->get();

        return ArticleOnlyResource::collection($articles);
    }
}
