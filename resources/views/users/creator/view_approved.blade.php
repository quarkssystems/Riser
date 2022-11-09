@extends('adminlte::page')

@php
    $title = 'View Creator';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('creators.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
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
                    <dd class="col-md-8">{{ $creator->vFirstName ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Email') }}</dt>
                    <dd class="col-md-8">{{ $creator->vEmail ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Password') }}</dt>
                    <dd class="col-md-8">{{ $creator->vPassword ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Profile Picture') }}</dt>
                    <dd class="col-md-8"><img src="{{ $creator->profile_picture_url }}" alt="" class="user-image img-circle elevation-2" width="50" /></dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Gender') }}</dt>
                    <dd class="col-md-8">{{ $creator->eGender ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Contact Number') }}</dt>
                    <dd class="col-md-8">{{ $creator->vPhoneNumber ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('About Me') }}</dt>
                    <dd class="col-md-8">{!! $creator->tAboutMe !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User Experience') }}</dt>
                    <dd class="col-md-8">{!! $creator->vExperience !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Country') }}</dt>
                    <dd class="col-md-8">{{ $creator->country->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('State') }}</dt>
                    <dd class="col-md-8">{{ $creator->state->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('District') }}</dt>
                    <dd class="col-md-8">{{ $creator->district->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Taluka') }}</dt>
                    <dd class="col-md-8">{{ $creator->taluka->name ?? '' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-md-4">{{ __('Last Name') }}</dt>
                    <dd class="col-md-8">{{ $creator->vLastName ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Username') }}</dt>
                    <dd class="col-md-8">{{ $creator->username ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Profession') }}</dt>
                    <dd class="col-md-8">{{ $creator->vOccupation ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Whatsapp Number') }}</dt>
                    <dd class="col-md-8">{{ $creator->whatsapp_number ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User Skills') }}</dt>
                    <dd class="col-md-8">{!! $creator->vSkill !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Business Name') }}</dt>
                    <dd class="col-md-8">{!! $creator->business_name !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Facebook Link') }}</dt>
                    <dd class="col-md-8">{{ $creator->facebook_link ?? '' }}</dd>
                </dl>
                    
                <dl class="row">
                    <dt class="col-md-4">{{ __('Linkedin Link') }}</dt>
                    <dd class="col-md-8">{{ $creator->linkedin_link ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Youtube Link') }}</dt>
                    <dd class="col-md-8">{{ $creator->youtube_link ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Twitter Link') }}</dt>
                    <dd class="col-md-8">{{ $creator->twitter_link ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Instagram Link') }}</dt>
                    <dd class="col-md-8">{{ $creator->instagram_link ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($creator->status) }}</dd>
                </dl>
            </div>
        </div>
    </div>
    
</div>
@endsection
