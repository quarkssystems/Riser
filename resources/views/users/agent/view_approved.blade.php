@extends('adminlte::page')

@php
    $title = 'View Agent';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('agents.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
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
                    <dd class="col-md-8">{{ $agent->vFirstName ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Email') }}</dt>
                    <dd class="col-md-8">{{ $agent->vEmail ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Password') }}</dt>
                    <dd class="col-md-8">{{ $agent->vPassword ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Profile Picture') }}</dt>
                    <dd class="col-md-8"><img src="{{ $agent->profile_picture_url }}" alt="" class="user-image img-circle elevation-2" width="50" /></dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Gender') }}</dt>
                    <dd class="col-md-8">{{ $agent->eGender ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Contact Number') }}</dt>
                    <dd class="col-md-8">{{ $agent->vPhoneNumber ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('About Me') }}</dt>
                    <dd class="col-md-8">{!! $agent->tAboutMe !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User Experience') }}</dt>
                    <dd class="col-md-8">{!! $agent->vExperience !!}</dd>
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
                    <dd class="col-md-8">{{ $agent->vLastName ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Username') }}</dt>
                    <dd class="col-md-8">{{ $agent->username ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Profession') }}</dt>
                    <dd class="col-md-8">{{ $agent->vOccupation ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Whatsapp Number') }}</dt>
                    <dd class="col-md-8">{{ $agent->whatsapp_number ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User Skills') }}</dt>
                    <dd class="col-md-8">{!! $agent->vSkill !!}</dd>
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
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($agent->status) }}</dd>
                </dl>
            </div>
        </div>
    </div>
    
</div>
@endsection
