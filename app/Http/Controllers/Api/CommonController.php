<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Banner;
use App\Models\CmsPages;
use App\Models\Feedback;
use ZEGO\ZegoErrorCodes;
use App\Models\MasterState;
use App\Models\MasterTaluka;
use Illuminate\Http\Request;
use App\Models\MasterCountry;
use ZEGO\ZegoServerAssistant;
use App\Models\MasterDistrict;
use App\Models\MasterHashtags;
use App\Models\MasterClassUser;
use App\Models\MasterLanguages;
use App\Models\MasterCategories;
use App\Http\Controllers\Controller;
use App\Models\MasterBannerCategory;
use Illuminate\Support\Facades\Validator;

class CommonController extends Controller
{
    /**
     * Get Countries List
     *
     * Developed By : Amish Soni
     * Developed On : 06/05/2022
     */
    /**
     * @OA\Get(
     *      path="/api/v1/countries-list",
     *      operationId="getCountriesList",
     *      tags={"Common Lists"},
     *      summary="Get Countries List",
     *      description="Returns list of Countries",
     *      security={ {"bearerAuth": {} }},
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
     *      @OA\Parameter(
     *          name="txt_search",
     *          description="Search Text. (optional)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Countries
     */
    public function getCountriesList(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'txt_search' => 'nullable|string||min:2',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $qry = MasterCountry::orderBy('name','asc')->status();
        
        if($request->filled('txt_search')){
            $qry->where('name', 'LIKE', $request->txt_search.'%');
        }
        
        $data = $qry->simplePaginate(intval($request->per_page));
        
        $response['data'] = $data ?? [];

        return response($response);
    }

    /**
     * Get States List
     *
     * Developed By : Amish Soni
     * Developed On : 06/05/2022
     */
    /**
     * @OA\Post(
     * path="/api/v1/states-list",
     * operationId="getStatesList",
     * tags={"Common Lists"},
     * summary="Get States List",
     * description="Returns list of States",
     * security={ {"bearerAuth": {} }},
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="country_id",
     *          description="Id of Country",
     *          required=true,
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
     *      @OA\Parameter(
     *          name="txt_search",
     *          description="Search Text. (optional)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of States
     */
    public function getStatesList(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'country_id' => 'required|integer',
            'txt_search' => 'nullable|string||min:2',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $qry = MasterState::where('country_id', $request->country_id)->orderBy('name','asc')->status();

        if($request->filled('txt_search')){
            $qry->where('name', 'LIKE', $request->txt_search.'%');
        }

        $data = $qry->simplePaginate(intval($request->per_page));
        
        $response['data'] = $data ?? [];

        return response($response);
    }

    /**
     * Get Districts List
     *
     * Developed By : Amish Soni
     * Developed On : 06/05/2022
     */
    /**
     * @OA\Post(
     * path="/api/v1/districts-list",
     * operationId="getDistrictsList",
     * tags={"Common Lists"},
     * summary="Get Districts List",
     * description="Returns list of Districts",
     * security={ {"bearerAuth": {} }},
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="state_id",
     *          description="Id of State",
     *          required=true,
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
     *      @OA\Parameter(
     *          name="txt_search",
     *          description="Search Text. (optional)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Districts
     */
    public function getDistrictsList(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'state_id' => 'required|integer',
            'txt_search' => 'nullable|string||min:2',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $qry = MasterDistrict::where('state_id', $request->state_id)->orderBy('name','asc')->status();

        if($request->filled('txt_search')){
            $qry->where('name', 'LIKE', $request->txt_search.'%');
        }

        $data = $qry->simplePaginate(intval($request->per_page));

        $response['data'] = $data ?? [];

        return response($response);
    }

    /**
     * Get Talukas List
     *
     * Developed By : Amish Soni
     * Developed On : 06/05/2022
     */
    /**
     * @OA\Post(
     * path="/api/v1/talukas-list",
     * operationId="getTalukasList",
     * tags={"Common Lists"},
     * summary="Get Talukas List",
     * description="Returns list of Talukas",
     * security={ {"bearerAuth": {} }},
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="district_id",
     *          description="Id of District",
     *          required=true,
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
     *      @OA\Parameter(
     *          name="txt_search",
     *          description="Search Text. (optional)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Talukas
     */
    public function getTalukasList(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'district_id' => 'required|integer',
            'txt_search' => 'nullable|string||min:2',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $qry = MasterTaluka::where('district_id', $request->district_id)->orderBy('name','asc')->status();
        if($request->filled('txt_search')){
            $qry->where('name', 'LIKE', $request->txt_search.'%');
        }

        $data = $qry->simplePaginate(intval($request->per_page));

        $response['data'] = $data ?? [];

        return response($response);
    }

    /**
     * Search Location
     *
     * Developed By : Kalpesh Joshi
     * Developed On : 18/07/2022
     */
    /**
     * @OA\Post(
     * path="/api/v1/search-location",
     * operationId="searchLocation",
     * tags={"Common Lists"},
     * summary="Search location",
     * description="Returns list of location based on coutry, state, district or taluka",
     * security={ {"bearerAuth": {} }},
     *      security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="txt_search",
     *          description="Search Text",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
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
     * Returns list of Talukas
     */
    public function searchLocation(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'txt_search' => 'required|string||min:2',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }
        
        $searchTerm = $request->txt_search;

        $qry = MasterCountry::orderBy('name','asc')
        ->where('name', 'LIKE', $searchTerm.'%')
        ->orWhereHas('state', function($q) use ($searchTerm){
            $q->where('name','like','%'. $searchTerm . '%')
            ->status()
            ->orWhereHas('district', function($q) use ($searchTerm){
                $q->where('name','like','%'. $searchTerm . '%')
                ->status();
            })
            ->with('district', function($q) use ($searchTerm){
                $q->where('name','like','%'. $searchTerm . '%')
                ->status()
                ->orWhereHas('district.taluka', function($q) use ($searchTerm){
                    $q->where('name','like','%'. $searchTerm . '%')
                    ->status();
                })
                ->with('district.taluka', function($q) use ($searchTerm){
                    $q->where('name','like','%'. $searchTerm . '%')
                    ->status();
                });
            });
        })
        ->with('state', function($q) use ($searchTerm){
            $q->where('name','like','%'. $searchTerm . '%')
            ->status()
            ->orWhereHas('district', function($q) use ($searchTerm){
                $q->where('name','like','%'. $searchTerm . '%')
                ->status();
            })
            ->with('district', function($q) use ($searchTerm){
                $q->where('name','like','%'. $searchTerm . '%')
                ->status()
                ->orWhereHas('taluka', function($q) use ($searchTerm){
                    $q->where('name','like','%'. $searchTerm . '%')
                    ->status();
                })
                ->with('taluka', function($q) use ($searchTerm){
                    $q->where('name','like','%'. $searchTerm . '%')
                    ->status();
                });
            });

        })
        ->status();

        $data = $qry->simplePaginate(intval($request->per_page));

        $response['data'] = $data ?? [];

        return response($response);
    }

    /**
    * Get CMS Page by Slug
    *
    * Developed By : Amish Soni
    * Developed On : 30/05/2022
    */
    /**
     * @OA\Get(
     * path="/api/v1/cms-page/{slug}",
     * operationId="getCmsPage",
     * tags={"Common Lists"},
     * summary="Get Page By Slug",
     * description="Returns Page By Slug",
     * security={ {"bearerAuth": {} }},
     *  @OA\Parameter(
     *          name="slug",
     *          description="Slug",
     *          required=true,
     *          example="privacy-policy",
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *  @OA\Response(
     *      response=200,
     *      description="Success"
     *  ),
     * )
     *
     * Returns details of CMS Page by slug
     */
    public function getCmsPage($slug)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $data = CmsPages::where('slug', $slug)->status()->first();
        $response['data'] = $data ?? [];

        return response($response);
    }

    /**
     * Get Languages List
     *
     * Developed By : Harshil Rajpura
     * Developed On : 19/05/2022
     */
     /**
     * @OA\Get(
     *      path="/api/v1/languages-list",
     *      operationId="getLanguagesList",
     *      tags={"Common Lists"},
     *      summary="Get Languages List",
     *      description="Returns list of Languages",
     *      security={ {"bearerAuth": {} }},
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
     *      @OA\Parameter(
     *          name="txt_search",
     *          description="Search Text. (optional)",
     *          required=false,
     *          example="en",
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *     )
     *
     * Returns list of Languages
     */
     public function getLanguagesList(Request $request)
     {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'txt_search' => 'nullable|string||min:2',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $qry = MasterLanguages::orderBy('language_name','asc');

        if($request->filled('txt_search')){
            $qry->where('language_name', 'LIKE', $request->txt_search.'%');
        }

        $data = $qry->status()->simplePaginate(intval($request->per_page));
        $response['data'] = $data ?? [];

        return response($response);
     }

    /**
    * Get Categories List
    *
    * Developed By : Harshil Rajpura
    * Developed On : 19/05/2022
    */
    /**
    * @OA\Get(
    *      path="/api/v1/categories-list",
    *      operationId="getCategoriesList",
    *      tags={"Common Lists"},
    *      summary="Get Categories List",
    *      description="Returns list of Categories",
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
    *      @OA\Parameter(
    *          name="txt_search",
    *          description="Search Text. (optional)",
    *          required=false,
    *          example="abc",
    *          in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Success"
    *       ),
    *       @OA\Response(response=400, description="Bad request"),
    *     )
    *
    * Returns list of Categories
    */
    public function getCategoriesList(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'txt_search' => 'nullable|string||min:2',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $masterCatData = MasterCategories::orderBy('category_name','asc');

        if($request->filled('txt_search')){
            $masterCatData->where('category_name', 'LIKE', $request->txt_search.'%');
        }

        //Updated to load all data at a time without pagination
        $request->per_page = $request->per_page ?? 1000;
        $data = $masterCatData->status()->simplePaginate(intval($request->per_page));
        $response['data'] = $data ?? [];

        return response($response);
    }

    /**
     * Get Hashtags List
    *
    * Developed By : Harshil Rajpura
    * Developed On : 19/05/2022
    */
    /**
     * @OA\Get(
     *      path="/api/v1/hashtags-list",
    *      operationId="getHashtagsList",
    *      tags={"Common Lists"},
    *      summary="Get Hashtags List",
    *      description="Returns list of Hashtags",
    *      security={ {"bearerAuth": {} }},
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
    *      @OA\Parameter(
    *          name="txt_search",
    *          description="Search Text. (optional)",
    *          required=false,
    *          example="xyz",
    *          in="query",
    *          @OA\Schema(
    *              type="string"
    *          )
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="Success"
    *       ),
    *       @OA\Response(response=400, description="Bad request"),
    *     )
    *
    * Returns list of Hashtags
    */
    public function getHashtagsList(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'txt_search' => 'nullable|string||min:2',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $qry = MasterHashtags::orderBy('hashtag_name','asc');

        if($request->filled('txt_search')){
            $qry->where('hashtag_name', 'LIKE', $request->txt_search.'%');
        }

        $data = $qry->status()->simplePaginate(intval($request->per_page));
        $response['data'] = $data ?? [];

        return response($response);
    }

    /**
    * Get Banners
    *
    * Developed By : Kalpesh Joshi
    * Developed On : 08/07/2022
    */
    /**
     * @OA\Get(
     * path="/api/v1/banners/{slug}",
     * operationId="getBanners",
     * tags={"Common Lists"},
     * summary="Get Banners",
     * description="Returns Banners",
     *      @OA\Parameter(
     *          name="slug",
     *          description="Slug",
     *          required=false,
     *          example="search-screen",
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *  @OA\Response(
     *      response=200,
     *      description="Success"
     *  ),
     * )
     *
     * Returns details of Banners by banner slug or return all
     */
    public function getBanners($slug = NULL)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        
        $data = MasterBannerCategory::status()->with('banners');
        
        if($slug){
            $data->where('slug', 'LIKE', $slug.'%');
        }
        $data = $data->get();
        
        $data->count() > 0 ? $response['data'] = $data : $response['status'] = false;

        return response($response);
    }

    /**
     *  Report Post
     *  Developed By : Kalpesh Joshi
     *  Developed At : 08-07-2022
     *
     *
     */
    /**
     * @OA\Post(
     * path="/api/v1/send-feedback",
     * operationId="sendFeedback",
     * tags={"Common Lists"},
     * summary="Send Feedback",
     * description="Send Feedback with star rating and comment",
     * security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="rating",
     *          description="Start Rating",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="feedback",
     *          description="Feedback from user",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
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
    public function sendFeedback(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'rating'   => 'required:numeric',
            'feedback' => 'required|string'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        try {
            $user = auth()->user();

            $data = [
                'feedback' => $request->feedback,
                'rating'   => $request->rating,
                'user_id'  => $user->iUserId,
            ];
            

            $feedback = Feedback::create($data);

            $response['data'] = $feedback;
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = trans('messages.something_went_wrong');
            return $response;
        }

        return response($response);
    }

    /**
     *  Meeting Generate Identity Token
     *  Developed By : Amish Soni
     *  Developed At : 24-09-2022
     *
     *
     */
     /**
     * @OA\Post(
     * path="/api/v1/generate-meeting-identity-token",
     * operationId="generateMeetingIdentityToken",
     * tags={"Common Lists"},
     * summary="generate Meeting Identity Token",
     * description="generate Meeting Identity Token",
     * security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User Name",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
    *          name="module_name",
    *          required=false,
    *          in="query",
    *          @OA\Schema(
    *              type="string",
    *              enum={"master_class", "call_booking"}
    *          )
    *      ),
    *       @OA\Parameter(
     *          name="module_id",
     *          description="Module Id",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
    *       @OA\Parameter(
     *          name="id_of_user",
     *          description="Id Of User",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
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
    public function generateMeetingIdentityToken(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'user_id'   => 'required',
            'module_name'  => 'in:master_class,call_booking',
            'module_id'  => 'required_with:module_name',
            'id_of_user'  => 'required_with:module_name',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $appId = config('constant.zego_app_id');
        $serverSecret = config('constant.zego_app_secret');
        $userId = $request->user_id;
        $payload = '';

        try {
            $token = ZegoServerAssistant::generateToken04($appId, $userId, $serverSecret, 3600, $payload);

            if( $token->code != ZegoErrorCodes::success ){
                $response['status'] = false;
                $response['message'] = $token->message;
                return $response;   
            }

            $response['data'] = $token;

            //change user status to attended when user join to Master Class
            if($request->filled('module_name')) {
                if($request->module_name == 'master_class') {
                    MasterClassUser::where('user_id', $request->id_of_user)->where('master_class_id', $request->module_id)->status(config('constant.status.booked_value'))->update(['status' => config('constant.status.attended_value')]);
                }
            }
            
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = trans('messages.something_went_wrong');
            return $response;
        }

        return response($response);
    }

    /**
     *  Meeting Generate Identity Token
     *  Developed By : Amish Soni
     *  Developed At : 24-09-2022
     *
     *
     */
     /**
     * @OA\Post(
     * path="/api/v1/generate-meeting-privileges-token",
     * operationId="generateMeetingPrivilegesToken",
     * tags={"Common Lists"},
     * summary="generate Meeting Privileges Token",
     * description="generate Meeting Privileges Token",
     * security={ {"bearerAuth": {} }},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User Id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="room_id",
     *          description="Room Id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="stream_id_list",
     *          description="Stream Id List, if multiple use comma seperated",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
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
    public function generateMeetingPrivilegesToken(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $PrivilegeKeyLogin   = 1; 
        $PrivilegeKeyPublish = 2; 
        $PrivilegeEnable     = 1; 
        $PrivilegeDisable    = 0; 

        $validator = Validator::make($request->all(), [
            'user_id'   => 'required',
            'room_id'   => 'required',
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            $response['message'] = $validator->errors()->first();

            return response($response, 200);
        }

        $appId = config('constant.zego_app_id');
        $serverSecret = config('constant.zego_app_secret');
        $userId = $request->user_id;
        $roomId = $request->room_id;
        $stream_id_list = array();

        if($request->filled('stream_id_list')) {
            $stream_id_list = explode(',',$request->stream_id_list);
        }

        $rtcRoomPayLoad = [
            'room_id' => $roomId, 
            'privilege' => [    
                'PrivilegeKeyLogin' => $PrivilegeEnable,
                'PrivilegeKeyPublish' => $PrivilegeDisable,
            ],
            'stream_id_list' => $stream_id_list
        ];
        
        $payload = json_encode($rtcRoomPayLoad);

        try {
            $token = ZegoServerAssistant::generateToken04($appId, $userId, $serverSecret, 3600, $payload);

            if( $token->code != ZegoErrorCodes::success ){
                $response['status'] = false;
                $response['message'] = $token->message;
                return $response;   
            }

            $response['data'] = $token;
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = trans('messages.something_went_wrong');
            return $response;
        }

        return response($response);
    }
}
