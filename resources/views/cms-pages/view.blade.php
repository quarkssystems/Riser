@extends('adminlte::page')

@php
    $title = 'View CMS Page';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('cms-pages.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
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
                    <dd class="col-md-8">{{ $cmsPage->id ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Title') }}</dt>
                    <dd class="col-md-8">{{ $cmsPage->page_title ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Slug') }}</dt>
                    <dd class="col-md-8">{{ $cmsPage->slug ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Content') }}</dt>
                    <dd class="col-md-8">{!! $cmsPage->page_content ?? '' !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($cmsPage->status) }}</dd>
                </dl>
                
            </div>
        </div>
    </div>    
</div>
@endsection