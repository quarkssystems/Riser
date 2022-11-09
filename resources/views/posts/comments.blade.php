@extends('adminlte::page')

@php
    $title = 'Comments for Post: '. $post->title;
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
                @if($postComments && $postComments->count() > 0)
                    @include('posts.recursive', ['comments' => $postComments, 'level' => 0])
                @else
                    {{__('No data found.')}}
                @endif
                
            </div>
        </div>
    </div>    
</div>
@endsection
