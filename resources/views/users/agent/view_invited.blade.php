@extends('adminlte::page')

@php
    $title = 'View Invited Agent';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('agents.invited')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-md-4">{{ __('First Name') }}</dt>
                    <dd class="col-md-8">{{ $agent->first_name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Email') }}</dt>
                    <dd class="col-md-8">{{ $agent->email ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Profile Picture') }}</dt>
                    <dd class="col-md-8"><img src="{{ $agent->profile_picture_url }}" alt="" class="user-image img-circle elevation-2" width="50" /></dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Gender') }}</dt>
                    <dd class="col-md-8">{{ $agent->gender ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Contact Number') }}</dt>
                    <dd class="col-md-8">{{ $agent->contact_number ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('About Me') }}</dt>
                    <dd class="col-md-8">{!! $agent->about_me !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User Experience') }}</dt>
                    <dd class="col-md-8">{!! $agent->user_experience !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Country') }}</dt>
                    <dd class="col-md-8">{{ $agent->country->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('State') }}</dt>
                    <dd class="col-md-8">{{ $agent->state->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('District') }}</dt>
                    <dd class="col-md-8">{{ $agent->district->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Taluka') }}</dt>
                    <dd class="col-md-8">{{ $agent->taluka->name ?? '' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-md-4">{{ __('Last Name') }}</dt>
                    <dd class="col-md-8">{{ $agent->last_name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Username') }}</dt>
                    <dd class="col-md-8">{{ $agent->username ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Profession') }}</dt>
                    <dd class="col-md-8">{{ $agent->profession ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Whatsapp Number') }}</dt>
                    <dd class="col-md-8">{{ $agent->whatsapp_number ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User Skills') }}</dt>
                    <dd class="col-md-8">{!! $agent->user_skills !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Business Name') }}</dt>
                    <dd class="col-md-8">{!! $agent->business_name !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Facebook Link') }}</dt>
                    <dd class="col-md-8">{{ $agent->facebook_link ?? '' }}</dd>
                </dl>
                    
                <dl class="row">
                    <dt class="col-md-4">{{ __('Linkedin Link') }}</dt>
                    <dd class="col-md-8">{{ $agent->linkedin_link ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Youtube Link') }}</dt>
                    <dd class="col-md-8">{{ $agent->youtube_link ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Twitter Link') }}</dt>
                    <dd class="col-md-8">{{ $agent->twitter_link ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Instagram Link') }}</dt>
                    <dd class="col-md-8">{{ $agent->instagram_link ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($agent->user_status) }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User Note') }}</dt>
                    <dd class="col-md-8">{!! $agent->user_note !!}</dd>
                </dl>
            </div>
        </div>
    </div>
    
</div>
@endsection
