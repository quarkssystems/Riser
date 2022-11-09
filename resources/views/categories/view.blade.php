@extends('adminlte::page')

@php
    $title = 'View Category';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('categories.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
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
                    <dd class="col-md-8">{{ $category->id ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('category Name') }}</dt>
                    <dd class="col-md-8">{{ $category->category_name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Image') }}</dt>
                    <dd class="col-md-8">
                        @if ($category->category_image)
                            <img src="{{ $category->category_image_url }}" width="150" alt="category" />
                        @endif
                    </dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Description') }}</dt>
                    <dd class="col-md-8">{!! $category->category_description ?? '' !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($category->status) }}</dd>
                </dl>
                
            </div>
        </div>
    </div>    
</div>
@endsection
