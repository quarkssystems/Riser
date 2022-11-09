@extends('adminlte::page')

@php
    $title = 'View Banner';
    $categories = $banner->bannerCategories->pluck('name')->toArray();
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('banners.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
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
                    <dd class="col-md-8">{{ $banner->id ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Banner Name') }}</dt>
                    <dd class="col-md-8">{{ $banner->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Banner Image') }}</dt>
                    <dd class="col-md-8">
                        @if ($banner->banner_image)
                            <img src="{{ $banner->banner_image_url }}" width="150" alt="banner" />
                        @endif
                    </dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Categories') }}</dt>
                    <dd class="col-md-8">{{ $categories ? implode(', ', $categories) : '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Description') }}</dt>
                    <dd class="col-md-8">{!! $banner->description ?? '' !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($banner->status) }}</dd>
                </dl>
                
            </div>
        </div>
    </div>    
</div>
@endsection
