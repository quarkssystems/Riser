@extends('adminlte::page')

@php
    $title = 'View Post';
    $categories = $post->categories ? implode(', ',$post->categories->pluck('category_name')->toArray()) : '';
    $languages = $post->languages ? implode(', ',$post->languages->pluck('language_name')->toArray()) : '';
    $hashtags = $post->hashtags ? implode(', ',$post->hashtags->pluck('hashtag_name')->toArray()) : '';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-6 text-right">
            @if($current_view) 
                @php
                    $url = 'posts.index';
                    $user_id = $redirect_user_id ?? '';
                    if($current_view == 'creator_invited') {
                        $url = 'creators.invited.posts';
                    } else if($current_view == 'creator_approved') {
                        $url = 'creators.posts';
                    }
                @endphp
                <a href="{{route($url,[$user_id])}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
            @else
                <a href="{{route('posts.comments', [$post->id])}}" class="btn btn-secondary"><i class="fa fa-comments mr-1"></i>Comments</a>
                <a href="{{route('posts.likes', [$post->id])}}" class="btn btn-info"><i class="fa fa-heart mr-1"></i>Likes</a>
                <a href="{{route('posts.reports', [$post->id])}}" class="btn btn-warning"><i class="fa fa-flag mr-1"></i>Reports</a>
                <a href="{{route('posts.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-md-4">{{ __('ID') }}</dt>
                    <dd class="col-md-8">{{ $post->id ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Title') }}</dt>
                    <dd class="col-md-8">{{ $post->title ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Video') }}</dt>
                    <dd class="col-md-8">
                        @if ($post->media_url)
                        <div style="position: relative; padding-top: 56.25%;"><iframe src="https://iframe.mediadelivery.net/embed/{{$post->library_id}}/{{$post->video_id}}?autoplay=true" loading="lazy" style="border: none; position: absolute; top: 0; height: 100%; width: 100%;" allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" allowfullscreen="true"></iframe></div>
                        @endif
                    </dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Creator') }}</dt>
                    <dd class="col-md-8">{{ $post->user->full_name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Categories') }}</dt>
                    <dd class="col-md-8">{{ $categories }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Languages') }}</dt>
                    <dd class="col-md-8">{{ $languages }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Hashtags') }}</dt>
                    <dd class="col-md-8">{{ $hashtags }}</dd>
                </dl>
            </div>
        </div>
    </div>  
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-md-4">{{ __('Country') }}</dt>
                    <dd class="col-md-8">{{ $post->country->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('State') }}</dt>
                    <dd class="col-md-8">{{ $post->state->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('District') }}</dt>
                    <dd class="col-md-8">{{ $post->district->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Taluka') }}</dt>
                    <dd class="col-md-8">{{ $post->taluka->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Views') }}</dt>
                    <dd class="col-md-8">{{ $post->views ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($post->status) }}</dd>
                </dl>
                
            </div>
        </div>
    </div>    
</div>
@endsection
