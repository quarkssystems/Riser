@extends('adminlte::page')

@php
    $title = 'View User';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('users.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
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
                    <dd class="col-md-8">{{ $user->vFirstName ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Email') }}</dt>
                    <dd class="col-md-8">{{ $user->vEmail ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Password') }}</dt>
                    <dd class="col-md-8">{{ $user->vPassword ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Profile Picture') }}</dt>
                    <dd class="col-md-8"><img src="{{ $user->profile_picture_url }}" alt="" class="user-image img-circle elevation-2" width="50" /></dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Gender') }}</dt>
                    <dd class="col-md-8">{{ $user->eGender ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Contact Number') }}</dt>
                    <dd class="col-md-8">{{ $user->vPhoneNumber ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('About Me') }}</dt>
                    <dd class="col-md-8">{!! $user->tAboutMe !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User Experience') }}</dt>
                    <dd class="col-md-8">{!! $user->vExperience !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Country') }}</dt>
                    <dd class="col-md-8">{{ $user->country->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('State') }}</dt>
                    <dd class="col-md-8">{{ $user->state->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('District') }}</dt>
                    <dd class="col-md-8">{{ $user->district->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Taluka') }}</dt>
                    <dd class="col-md-8">{{ $user->taluka->name ?? '' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-md-4">{{ __('Last Name') }}</dt>
                    <dd class="col-md-8">{{ $user->vLastName ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Username') }}</dt>
                    <dd class="col-md-8">{{ $user->username ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Profession') }}</dt>
                    <dd class="col-md-8">{{ $user->vOccupation ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Whatsapp Number') }}</dt>
                    <dd class="col-md-8">{{ $user->whatsapp_number ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User Skills') }}</dt>
                    <dd class="col-md-8">{!! $user->vSkill !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Business Name') }}</dt>
                    <dd class="col-md-8">{!! $user->business_name !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Facebook Link') }}</dt>
                    <dd class="col-md-8">{{ $user->facebook_link ?? '' }}</dd>
                </dl>
                    
                <dl class="row">
                    <dt class="col-md-4">{{ __('Linkedin Link') }}</dt>
                    <dd class="col-md-8">{{ $user->linkedin_link ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Youtube Link') }}</dt>
                    <dd class="col-md-8">{{ $user->youtube_link ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Twitter Link') }}</dt>
                    <dd class="col-md-8">{{ $user->twitter_link ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Instagram Link') }}</dt>
                    <dd class="col-md-8">{{ $user->instagram_link ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($user->status) }}</dd>
                </dl>
            </div>
        </div>
    </div>
    
</div>
@endsection
