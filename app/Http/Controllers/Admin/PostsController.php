<?php

namespace App\Http\Controllers\Admin;

use DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Models\MasterCategories;
use App\Models\MasterCountry;
use App\Models\MasterHashtags;
use App\Models\MasterLanguages;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Post::query();
            $data = $data->with('user:iUserId,vFirstName,vLastName')->orderByDesc('id');
            return Datatables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('status', function($row){
                    if($row->status == config('constant.status.processing_value')){
                        $colCls = 'warning';
                    } else {
                        $colCls = $row->status == config('constant.status.active_value') ? 'success' : 'danger';
                    }
                    return '<span data-id="'.$row->id.'" data-status="'.$row->status.'" role="button" class="statusBtn badge badge-'.$colCls.'">'.ucfirst($row->status).'</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('posts.show',[$row->id]).'" class="viewBtn btn btn-primary btn-xs" title="View"><i class="fa fa-eye"></i></a>';
                    // $btn .= '<a href="'.route('posts.edit',[$row->id]).'" class="ml-1 editBtn btn btn-info btn-xs" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                    $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0)" class="ml-1 deleteBtn btn btn-danger btn-xs" title="Delete"><i class="fa fa-trash"></i></a>';

                    $btn .= '<a href="'.route('posts.comments',[$row->id]).'" class="ml-1 btn btn-secondary btn-xs" title="Posts Comments"><i class="fa fa-comments"></i></a>';

                    $btn .= '<a href="'.route('posts.likes',[$row->id]).'" class="ml-1 btn btn-info btn-xs" title="Posts Likes"><i class="fa fa-heart"></i></a>';

                    $btn .= '<a href="'.route('posts.reports',[$row->id]).'" class="ml-1 btn btn-warning btn-xs" title="Report Posts"><i class="fa fa-flag"></i></a>';

                    return $btn;
                })
                ->filterColumn('full_name', function($query, $keyword) {
                    $query->whereHas('user', function($query) use ($keyword) {
                        $query->whereRaw("CONCAT(vFirstName,' ',vLastName) like ?", ["%{$keyword}%"]);
                    });
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('posts.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countryData = MasterCountry::status()->get();
        // $usersData = User::whereHas(
        //     'roles', function($q){
        //         $q->whereIn('name', [config('constant.roles.creator')]);
        //     }
        // )->status()->get();
        $categoriesData = MasterCategories::status()->get();
        $languagesData = MasterLanguages::status()->get();
        $hashtagsData = MasterHashtags::status()->get();

        return view('posts.create', compact('countryData', 'categoriesData', 'languagesData', 'hashtagsData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePostRequest $request)
    {
        $input = $request->all();

        if($request->hasFile('media_url')){   
            $fileName = storeFile('post', $request->media_url);
            $input['media_url'] = $fileName;
        }
        $postData = Post::create($input);

        if($request->filled('category_id')){
            $categoryIds = (!is_array($request->category_id)) ? explode(',', $request->category_id) : $request->category_id;
            $postData->categories()->sync($categoryIds);
        }
        if($request->filled('language_id')){
            $languageIds = (!is_array($request->language_id)) ? explode(',', $request->language_id) : $request->language_id;
            $postData->languages()->sync($languageIds);
        }

        if($request->filled('hashtags')){
            //find existing hashtags ids
            $hashtags = (!is_array($request->hashtags)) ? explode(',', $request->hashtags) : $request->hashtags;
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

        return redirect()->route('posts.index')->with('success', 'Record created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post, Request $request)
    {
        $current_view = ($request->filled('view')) ? $request->view : '';
        $redirect_user_id = ($request->filled('user_id')) ? $request->user_id : '';
        $post->load('user', 'categories','languages', 'hashtags', 'country', 'state', 'district', 'taluka');
        return view('posts.view', compact('post', 'current_view', 'redirect_user_id'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        if(fileExists($post->media_url)) {
            deleteFile($post->media_url);
        }

        $post->categories()->detach();
        $post->languages()->detach();
        $post->hashtags()->detach();
        $post->likes()->detach();
        $post->report()->detach();
        
        $post->comments()->delete();
        $post->delete();
    }

    public function changeStatus(Post $post, Request $request)
    {
        $inputData = $request->all();

        $status = ($inputData['status'] == config('constant.status.active_value')) ? config('constant.status.inactive_value') : config('constant.status.active_value');

        $post->update(['status' => $status]);

        return true;
    }

    public function getPostComments(Post $post)
    {
        $postComments = PostComment::with('childrenRecursive', 'user')
        ->where('post_id', $post->id)
        ->whereNull('parent_id')
        ->get();

        return view('posts.comments', compact('post', 'postComments'));
    }

    public function getPostLikes(Post $post)
    {
        $post->load('likes');
    
        return view('posts.likes', compact('post', ));
    }

    public function getPostReports(Post $post)
    {
        $post->load('report');
    
        return view('posts.reports', compact('post', ));
    }
}
