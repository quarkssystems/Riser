@extends('adminlte::page')

@php
    $title = 'Likes for Post: '. $post->title;
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('posts.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                @if($post->likes && $post->likes->count() > 0)
                    @foreach ($post->likes as $like)
                        <dl class="row">
                            <dt class="col-md-4">{{ __('Liked By') }}</dt>
                            <dd class="col-md-8">
                                <img class="user-image img-circle" src="{{ $like->profile_picture_url }}" alt="" style="max-width: 25px;" />
                                {{ $like->full_name ?? '' }}
                            </dd>
                        </dl>
                    @endforeach
                @else
                    {{__('No data found.')}}
                @endif
            </div>
        </div>
    </div>    
</div>
@endsection
