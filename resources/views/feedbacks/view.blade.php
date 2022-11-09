@extends('adminlte::page')

@php
    $title = 'View Feedback';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('feedbacks.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
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
                    <dd class="col-md-8">{{ $feedback->id ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Rating') }}</dt>
                    <dd class="col-md-8">{{ $feedback->rating ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Feedback') }}</dt>
                    <dd class="col-md-8">{{ $feedback->feedback ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User') }}</dt>
                    <dd class="col-md-8">{{ $feedback->user->full_name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($feedback->status) }}</dd>
                </dl>
                
            </div>
        </div>
    </div>    
</div>
@endsection
