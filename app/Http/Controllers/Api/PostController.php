<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Models\PostComment;
use App\Models\MasterTaluka;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Models\MasterHashtags;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Notifications\CommentByUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class PostController extends Controller
{
    /**
    * Create Post
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 26/05/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/create-post",
    *      operationId="createPost",
    *      tags={"Post"},
    *      summary="Create Post",
    *      description="Create Post API",
    *      security={ {"bearerAuth": {} }},
    *      @OA\RequestBody(
    *      @OA\MediaType(
    *           mediaType="multipart/form-data",
    *           @OA\Schema(
    *           required={"title", "language_id", "category_id", "media_type", "media_url"},
    *           @OA\Property(property="title", type="string", example="This is my first post"),
    *           @OA\Property(property="language_id", type="string", example="1,2"),
    *           @OA\Property(property="category_id", type="string", example="1,2"),
    *           @OA\Property(property="hashtags", type="string", example="one,two"),
    *           @OA\Property(property="country_id", type="string", example="101"),
    *           @OA\Property(property="state_id", type="string", example="12"),
    *           @OA\Property(property="district_id", type="string", example="468"),
    *           @OA\Property(property="taluka_id", type="string", example="1"),
    *           @OA\Property(property="media_type", type="string", default="video", enum={"image", "video", "audio"}),
    *           @OA\Property(property="media_url", type="file"),
    *           ),
    *       ),
    *   ),
    *
    * @OA\Response(
    *    response=200,
    *    description="Success"
    *     ),
    * @OA\Response(
    *    response=401,
    *    description="Returns when user is not authenticated",
    *    @OA\JsonContent(
    *       @OA\Property(property="message", type="string", example="Not authorized"),
    *    )
    * ),
    * @OA\Response(response=400, description="Bad request"),
    * )
    */
    public function createPost(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        $fileName = null;

        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'language_id' => 'required|string',
            'category_id' => 'required|string',
            'country_id'  => 'integer',
            'state_id'    => 'integer',
            'district_id' => 'integer',
            'taluka_id'   => 'integer',
            'media_type'  => 'required|in:image,video,audio',
            'media_url'   => 'required|mimes:jpeg,png,jpg,gif,svg,mp3,mpeg,mp4,3gp',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        if (auth()->check() && !auth()->user()->hasRole('creator')) {
            $userPost = Post::where('user_id', auth()->user()->iUserId)->count();

            if($userPost >= 5){
                $response['status'] = false;
                $response['message'] = trans('messages.user_role_post_limit');
                return $response;
            }
        }

        if($request->hasFile('media_url')){

            $fileName = storeFile('post', $request->media_url);
        }

        $postData = new Post();
        $postData->user_id = auth()->user()->iUserId;
        $postData->title = $request->title;
        $postData->media_url = $fileName;
        $postData->media_type = $request->media_type;
        $postData->country_id = $request->country_id;
        $postData->state_id = $request->state_id;
        $postData->district_id = $request->district_id;

        if($request->filled('taluka_id')){
            $taluka = MasterTaluka::where('id',$request->taluka_id)->first();
            if($taluka){
                $postData->latitude = $taluka->latitude;
                $postData->longitude = $taluka->longitude;
                $postData->taluka_id = $request->taluka_id;

            }
        }

        $postData->save();

        if($request->filled('category_id')){
            $categoryIds = explode(',', $request->category_id);
            $postData->categories()->sync($categoryIds);
        }
        if($request->filled('language_id')){
            $languageIds = explode(',', $request->language_id);
            $postData->languages()->sync($languageIds);
        }

        if($request->filled('hashtags')){
            //find existing hashtags ids
            $hashtags = explode(',', $request->hashtags);
            $getHashtags = MasterHashtags::whereIn('hashtag_name', $hashtags)->get();
            $getHashtagsKeys = $getHashtags->pluck('id')->toArray();
            $getHashtagsVals = $getHashtags->pluck('hashtag_name')->toArray();
            $newHastags = array_diff($hashtags, $getHashtagsVals);
            $newHastagsIds = [];

            //insert new hashtags in database
            if(!empty($newHastags)){
                foreach ($newHastags as $key => $hashtagValue) {
                    if(filled($hashtagValue)){
                        $newHashtagData = new MasterHashtags();
                        $newHashtagData->user_id = auth()->user()->iUserId;
                        $newHashtagData->hashtag_name = $hashtagValue;
                        $newHashtagData->save();
                        $newHastagsIds[] = $newHashtagData->id;
                    }
                }
            }
            $mergeHashtags = array_merge($getHashtagsKeys, $newHastagsIds);
            $postData->hashtags()->sync($mergeHashtags);
        }

        $postData->refresh();
        $response['data'] = $postData;

        return response($response);
    }

    /**
     * Get Post List
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 01/06/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/post-list",
     *      operationId="getPostList",
     *      tags={"Post"},
     *      summary="Get Post List",
     *      description="Returns list of Post",
     *      @OA\Parameter(
     *          name="post_id",
     *          description="Post ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="category_id",
     *          description="Category ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page No. (optional) - default 1",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Per Page (optional) - default 15",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Posts
     */
    public function getPostList(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'post_id'     => 'nullable|integer',
            'category_id' => 'nullable|integer',
            'user_id'     => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $postData = Post::select(['id', 'user_id', 'title', 'media_url', 'media_type', 'video_id','status','views'])
        ->with(['categories:id,category_name', 'user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id','user.country','user.state', 'user.district', 'user.taluka']);

        if(auth('sanctum')->check()){
            //block posts will not display
            $postData = $postData->whereDoesntHave('block', function($query) {
                $query->where('user_id', auth('sanctum')->user()->iUserId);
            });

            //block user's posts will not display
            $postData = $postData->whereHas('user', function ($query) {
                $query->whereDoesntHave('block', function($q) {
                    $q->where('user_id', auth('sanctum')->user()->iUserId);
                });
            });
        }

        if(auth('sanctum')->check() && filled($request->user_id) == false){
            $postData = $postData->where('user_id', '!=', auth('sanctum')->user()->iUserId);
        }
        if(filled($request->post_id)){
            $postData = $postData->where('id', $request->post_id);
        }
        if(filled($request->category_id)){
            $postData = $postData->whereHas('categories', function ($query) use($request) {
                $query->where('id', $request->category_id);
            });
        }

        //If user_id passed all post fatched without role check
        if(filled($request->user_id)){
            $postData = $postData->where('user_id', $request->user_id);

            //block posts will not display
            if(auth('sanctum')->check()){
                $postData = $postData->whereDoesntHave('block', function($query) {
                    $query->where('user_id', auth('sanctum')->user()->iUserId);
                });
            }
        }else{
            //else fetch only creator user's post
            $postData = $postData->whereHas('user.roles', function ($query)
            {
                $query->whereIn('roles.name', [config('constant.roles.creator')]);
            });
        }
        // if(auth('sanctum')->check() && filled($request->user_id) == false){
        //     $taluka = auth('sanctum')->user()->taluka;
        //     if($taluka && $taluka->latitude && $taluka->longitude){
        //         $lat = $taluka->latitude;
        //         $lon = $taluka->longitude;
        //         $postData = $postData->nearest($lat,$lon);
        //     }
        // } else {
        //     $postData = $postData->orderBy('created_at','desc');
        // }
        // $postData = $postData->status();

        if(filled($request->user_id)){
            $postData->whereIn('status', [config('constant.status.active_value'), config('constant.status.processing_value')]);
            $postData->orderByDesc('created_at');
        }else{
            $postData->status();
            $postData->inRandomOrder();
        }

        $postData = $postData->withCount(['likes', 'comments'])
        ->simplePaginate(intval($request->per_page));

        $response['data'] = $postData ?? [];

        return response($response);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/user-post-list",
     *      operationId="getUserPostList",
     *      tags={"Post"},
     *      summary="Get User's Post List",
     *      description="Returns list of Post for User",
     *      @OA\Parameter(
     *          name="post_id",
     *          description="Post ID",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page No. (optional) - default 1",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Per Page (optional) - default 15",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Posts
     */
    public function getUserPostList(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'post_id'     => 'required|integer',
            'user_id'     => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $userPostData = Post::select(['id', 'user_id', 'title', 'media_url', 'media_type', 'video_id','status','views'])
        ->with(['categories:id,category_name', 'user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id','user.country','user.state', 'user.district', 'user.taluka']);

        if(filled($request->user_id)){
            $userPostData = $userPostData->where('user_id', $request->user_id);

            //block posts will not display
            if(auth('sanctum')->check()){
                $userPostData = $userPostData->whereDoesntHave('block', function($query) {
                    $query->where('user_id', auth('sanctum')->user()->iUserId);
                });

                //block user's posts will not display
                $userPostData = $userPostData->whereHas('user', function ($query) {
                    $query->whereDoesntHave('block', function($q) {
                        $q->where('user_id', auth('sanctum')->user()->iUserId);
                    });
                });
            }
        } else if(auth('sanctum')->check()){
            $userPostData = $userPostData->where('user_id', auth('sanctum')->user()->iUserId)
            ->whereDoesntHave('block', function($query) {
                $query->where('user_id', auth('sanctum')->user()->iUserId);
            });

            //block user's posts will not display
            $userPostData = $userPostData->whereHas('user', function ($query) {
                $query->whereDoesntHave('block', function($q) {
                    $q->where('user_id', auth('sanctum')->user()->iUserId);
                });
            });
        }

        $userPostData = $userPostData->status()
        ->withCount(['likes', 'comments'])
        //get specific record at first position
        ->orderByRaw("IF(id = $request->post_id, 0,1)")
        ->simplePaginate(intval($request->per_page));

        $response['data'] = $userPostData ?? [];

        return response($response);
    }

    /**
     * Get Post Detail
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 09/08/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/post-detail",
     *      operationId="getPostDetail",
     *      tags={"Post"},
     *      summary="Get Post Detail",
     *      description="Returns detail of Post",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="post_id",
     *          description="Post ID",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Posts
     */
    public function getPostDetail(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $postData = Post::select(['id', 'user_id', 'title', 'media_url', 'media_type', 'video_id','status','views'])
        ->with(['user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id','user.country','user.state', 'user.district', 'user.taluka'])
        ->where('id', $request->post_id)
        ->withCount(['likes', 'comments'])
        ->status()
        ->first();

        if($postData){
            $response['data'] = $postData;
        }else{
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        return response($response);
    }

    /**
     * Get Post Deteail Likes
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 16/08/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/post-detail-likes",
     *      operationId="getPostDetailLikes",
     *      tags={"Post"},
     *      summary="Get Post Detail Likes",
     *      description="Returns likes by users of Post",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="post_id",
     *          description="Post ID",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page No. (optional) - default 1",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Per Page (optional) - default 15",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Posts
     */
    public function getPostDetailLikes(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $postData = Post::where('id', $request->post_id)->status()->first();

        if($postData){
            $likesData = $postData->likes()->simplePaginate(intval($request->per_page),['iUserId', 'vFirstName', 'vLastName', 'vImage']);
        }else{
            $likesData = NULL;
        }

        if($likesData){
            $response['data'] = $likesData;
        }else{
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        return response($response);
    }

    /**
     * Get Post Deteail Comments
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 16/08/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/post-detail-comments",
     *      operationId="getPostDetailComments",
     *      tags={"Post"},
     *      summary="Get Post Detail Comments",
     *      description="Returns comments by users of Post",
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="post_id",
     *          description="Post ID",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page No. (optional) - default 1",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Per Page (optional) - default 15",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Posts
     */
    public function getPostDetailComments(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $commentsData = Post::find($request->post_id);

        if($commentsData){
            $commentsData = tap($commentsData->comments()
            ->whereNull('parent_id')
            ->with(['childrenRecursive','childrenRecursive.user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber' ,'user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber'])
            ->simplePaginate(intval($request->per_page)),function($paginatedInstance){
                return $paginatedInstance->getCollection()->transform(function ($value) {
                    return [
                        'id' => $value->id,
                        'post_id' => $value->post_id,
                        'user_id' => $value->user_id,
                        'comments' => $value->comments,
                        'parent_id' => $value->parent_id,
                        'created_at' => Carbon::createFromFormat('Y-m-d H:i:s',$value->created_at)->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s'),
                        'children_recursive' => $value->children_recursive ?$value->children_recursive : [],
                        'user' => $value->user,
                    ];
                });
            });

            $response['data'] = $commentsData;
        }else{
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        return response($response);
    }


    /**
     *  Like/Dislike
     *  Developed By : Amish Soni
     *  Developed At : 03-06-2022
     *
     *
     */
    /**
     * @OA\Post(
     * path="/api/v1/add-post-comments",
     * operationId="addPostComments",
     * tags={"Post"},
     * summary="add Post Comments",
     * description="add Post Comments",
     * security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="post_id",
     *          description="Post ID",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="comments",
     *          description="Comments",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="parent_id",
     *          description="Parent ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function addPostComments(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'post_id' => 'required:integer',
            'comments' => 'required',
            'parent_id' => 'integer',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }


        $user = auth()->user();
        $postId = $request->post_id;
        $comments = $request->comments;
        $parentId = $request->parent_id;

        $postData = Post::status()->find($postId);

        if (!$postData) {
            $response['status'] = false;
            $response['message'] = trans('messages.post_not_found');
            return $response;
        }

        try {
            $postComment = PostComment::create([
                'post_id' => $postId,
                'user_id' => $user->iUserId,
                'comments' => $comments,
                'parent_id' => $parentId,
            ]);

            $post = Post::where('id', $postId)->first();

            if($post){
                //Send notification to creator
                Notification::send($post->user, new CommentByUser($user->full_name." has commented on your video - ".$post->title, $post));

            };


            $response['data'] = $postComment;
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = trans('messages.something_went_wrong');
            return $response;
        }

        return response($response);
    }

    /**
     *  Like/Dislike
     *  Developed By : Kalpesh Joshi
     *  Developed At : 03-06-2022
     *
     *
     */
    /**
     * @OA\Post(
     * path="/api/v1/like-dislike",
     * operationId="likeDislike",
     * tags={"Post"},
     * summary="Like Dislike",
     * description="Like Dislike Post - action can be (like, dislike)",
     * security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="post_id",
     *          description="Post ID to like/dislike",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="action",
     *          description="Like or Dislike",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="like",
     *              enum={"like", "dislike"}
     *          )
     *      ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function likeDislike(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'post_id' => 'required:integer',
            'action' => 'in:like,dislike'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $user = auth()->user();
        $postId = $request->input('post_id');
        $action = $request->input('action');

        $postData = Post::status()->find($postId);

        if (!$postData) {
            $response['status'] = false;
            $response['message'] = trans('messages.post_not_found');
            return $response;
        }

        if ($action == 'like') {
            $postData->likes()->sync([$user->iUserId], false);
            $response['message'] = trans('messages.you_liked', ['title' => $postData->title]);
        } else if ($action == 'dislike') {
            $postData->likes()->detach($user->iUserId);
            $response['message'] = trans('messages.you_disliked', ['title' => $postData->title]);
        }

        return response($response);
    }

    /**
     *  Report Post
     *  Developed By : Kalpesh Joshi
     *  Developed At : 03-06-2022
     *
     *
     */
    /**
     * @OA\Post(
     * path="/api/v1/report-post",
     * operationId="reportPost",
     * tags={"Post"},
     * summary="Report Post",
     * description="Report Post - action can be (report, withdraw-report)",
     * security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="post_id",
     *          description="Post ID to Report/Withdraw-Report",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="action",
     *          description="Report or Withdraw-Report",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="report",
     *              enum={"report", "withdraw-report"}
     *          )
     *      ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function reportPost(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'post_id' => 'required:integer',
            'action' => 'in:report,withdraw-report'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $user = auth()->user();
        $postId = $request->input('post_id');
        $action = $request->input('action');

        $postData = Post::status()->find($postId);

        if (!$postData) {
            $response['status'] = false;
            $response['message'] = trans('messages.post_not_found');
            return $response;
        }

        if ($action == 'report') {
            $postData->report()->sync([$user->iUserId], false);
            $response['message'] = trans('messages.report_submitted');
        } else if ($action == 'withdraw-report') {
            $postData->report()->detach($user->iUserId);
            $response['message'] = trans('messages.report_withdrew');
        }

        return response($response);
    }

    /**
     *  Report Profile
     *  Developed By : Kalpesh Joshi
     *  Developed At : 03-06-2022
     *
     *
     */
    /**
     * @OA\Post(
     * path="/api/v1/report-profile",
     * operationId="reportProfile",
     * tags={"Post"},
     * summary="Report Profile",
     * description="Report Profile - action can be (report, withdraw-report)",
     * security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User ID to Report/Withdraw-Report",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="action",
     *          description="Report or Withdraw-Report",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="report",
     *              enum={"report", "withdraw-report"}
     *          )
     *      ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function reportProfile(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'user_id' => 'required:integer',
            'action' => 'in:report,withdraw-report'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $user = auth()->user();
        $userId = $request->input('user_id');
        $action = $request->input('action');

        $userData = User::status()->find($userId);

        if (!$userData) {
            $response['status'] = false;
            $response['message'] = trans('messages.post_not_found');
            return $response;
        }

        if ($action == 'report') {
            $userData->report()->sync([$user->iUserId], false);
            $response['message'] = trans('messages.report_submitted');
        } else if ($action == 'withdraw-report') {
            $userData->report()->detach($user->iUserId);
            $response['message'] = trans('messages.report_withdrew');
        }

        return response($response);
    }

    /**
     *  search Profile Or Video
     *  Developed By : Kalpesh Joshi
     *  Developed At : 18-07-2022
     *
     *
     */
    /**
     * @OA\Post(
     * path="/api/v1/search-profile-or-video",
     * operationId="searchProfileOrVideo",
     * tags={"Post"},
     * summary="Search Profile or Video",
     * description="Search Profile or Video - search by can be (profile, video)",
     *      @OA\Parameter(
     *          name="search_term",
     *          description="Search Term/Text",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="search_by",
     *          description="Profile or Video",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="profile",
     *              enum={"profile", "video"}
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page No. (optional) - default 1",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Per Page (optional) - default 15",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function searchProfileOrVideo(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'search_term' => 'required|min:2',
            'search_by'   => 'in:profile,video'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $searchTerm = $request->input('search_term');
        $searchBy = $request->input('search_by');

        if($searchBy == 'profile'){
            $userData = User::status();
            if(auth('sanctum')->check()){
                $user = auth('sanctum')->user();
                $userData = $userData->where('iUserId','!=',$user->iUserId)
                    ->whereDoesntHave('block', function($q) {
                        $q->where('user_id', auth('sanctum')->user()->iUserId);
                    });
            }
            $userData = $userData->whereHas('roles', function ($query)
            {
                $query->whereIn('roles.name', [config('constant.roles.creator')]);
            })
            ->where(function ($query) use ($searchTerm){
                $query->whereRaw("CONCAT(vFirstName,' ',vLastName) like ?", ["%{$searchTerm}%"]);
            })
            ->with(['country', 'state', 'district', 'taluka'])
            ->simplePaginate(intval($request->per_page));

            if ($userData->count() == 0) {
                $response['status'] = false;
                $response['message'] = trans('messages.no_data_found');
            }

            $response['data'] = $userData;
        }else if($searchBy == 'video'){
            $videoData = Post::select(['id', 'user_id', 'title', 'media_url', 'media_type', 'video_id','status','views']);

            if(auth('sanctum')->check()){
                $videoData = $videoData->whereDoesntHave('block', function($q) {
                    $q->where('user_id', auth('sanctum')->user()->iUserId);
                });
            }
            // ->with(['user:iUserId,vFirstName,vLastName,vImage,vEmail,vOccupation,vPhoneNumber,country_id,state_id,district_id,taluka_id','user.country','user.state', 'user.district', 'user.taluka'])
            $videoData = $videoData->where('title', 'like', '%'.$searchTerm.'%')
            ->status()
            ->orderBy('id','desc')
            ->simplePaginate(intval($request->per_page));

            if ($videoData->count() == 0) {
                $response['status'] = false;
                $response['message'] = trans('messages.no_data_found');
            }

            $response['data'] = $videoData;
        }



        return response($response);
    }

    /**
    * Update Post Views
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 02/09/2022
    */
   /**
    * @OA\Post(
    *      path="/api/v1/update-views",
    *      operationId="updateViews",
    *      tags={"Post"},
    *      summary="Update Post Views",
    *      description="Update Post Views",
    *      @OA\Parameter(
    *          name="id",
    *          description="Post Id",
    *          required=true,
    *          in="query",
    *          @OA\Schema(
    *              type="integer"
    *          )
    *      ),
    *
    * @OA\Response(
    *    response=200,
    *    description="Success"
    *     ),
    * @OA\Response(
    *    response=401,
    *    description="Returns when user is not authenticated",
    *    @OA\JsonContent(
    *       @OA\Property(property="message", type="string", example="Not authorized"),
    *    )
    * ),
    * @OA\Response(response=400, description="Bad request"),
    * )
    */
    public function updateViews(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $postData = Post::where('id', $request->id)->first();
        $postData->increment('views');

        if (!$postData) {
            $response['status'] = false;
            $response['message'] = trans('messages.no_data_found');
            return response($response);
        }

        $response['data'] = $postData;

        return response($response);
    }

    /**
    * Delete Post
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 13/09/2022
    */
    /**
    * @OA\Delete(
    *       path="/api/v1/delete-post",
    *       operationId="deletePost",
    *       tags={"Post"},
    *       summary="Delete Post",
    *       description="Delete Post",
    *       security={ {"bearerAuth": {} }},
    *       @OA\RequestBody(
    *       @OA\MediaType(
    *           mediaType="application/json",
    *           @OA\Schema(
    *               required={"id"},
    *               @OA\Property(
    *                   property="id",
    *                   type="integer",
    *                   example="1"
    *               ),
    *           ),
    *       ),
    *       ),
    *      @OA\Response(
    *          response=200,
    *          description="Success"
    *       ),
    *       @OA\Response(response=400, description="Bad request"),
    *     )
    *
    * Returns list of Posts
    */
    public function deletePost(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $postData = Post::where('id', $request->id)
        ->where('user_id', auth()->user()->iUserId)
        ->delete();

        if (!$postData) {
            $response['status'] = false;
            $response['message'] = trans('messages.not_authorized');
            return response($response);
        }

        return response($response);
    }

    /**
     *  Block Post
     *  Developed By : Amish Soni
     *  Developed At : 02-11-2022
     *
     *
     */
    /**
     * @OA\Post(
     * path="/api/v1/block-post",
     * operationId="blockPost",
     * tags={"Post"},
     * summary="Block Post",
     * description="Block Post - action can be (block, unblock)",
     * security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="post_id",
     *          description="Post ID to block/unblock",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="action",
     *          description="Block or Unblock",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="block",
     *              enum={"block", "unblock"}
     *          )
     *      ),
     *
     * @OA\Response(
     *    response=200,
     *    description="Success"
     *     ),
     * @OA\Response(
     *    response=401,
     *    description="Returns when user is not authenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Not authorized"),
     *    )
     * )
     * )
     */
    public function blockPost(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'post_id' => 'required:integer',
            'action' => 'in:block,unblock'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $user = auth()->user();
        $postId = $request->input('post_id');
        $action = $request->input('action');

        $postData = Post::status()->find($postId);

        if (!$postData) {
            $response['status'] = false;
            $response['message'] = trans('messages.post_not_found');
            return $response;
        }

        if ($postData->user_id == $user->iUserId) {
            $response['status'] = false;
            $response['message'] = trans('messages.post_self_block');
            return $response;
        }

        if ($action == 'block') {
            $postData->block()->sync([$user->iUserId], false);
            $response['message'] = trans('messages.block_submitted');
        } else if ($action == 'unblock') {
            $postData->block()->detach($user->iUserId);
            $response['message'] = trans('messages.unblock_submitted');
        }

        return response($response);
    }
}
