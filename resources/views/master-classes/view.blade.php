@extends('adminlte::page')

@php
    $title = 'View Master Class';
    $categories = $masterClass->categories ? implode(', ',$masterClass->categories->pluck('category_name')->toArray()) : '';
    $start_date = $masterClass->start_date ?? null;
    $start_time = $masterClass->start_time ?? null;
    $end_time = $masterClass->end_time ?? null;
    $dateTime = $start_date.' '.$start_time.' - '.$end_time;

@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-5">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-7 text-right">
            <a href="{{route('master-classes.users', [$masterClass->id])}}" class="btn btn-secondary"><i class="fa fa-users mr-1"></i>Users</a>
            <a href="{{route('master-classes.promoters', [$masterClass->id])}}" class="btn btn-info"><i class="fa fa-users mr-1"></i>Promoters</a>
            <a href="{{route('master-classes.affilitors', [$masterClass->id])}}" class="btn btn-dark"><i class="fa fa-users mr-1"></i>Affilitors</a>
            <a href="{{route('master-classes.transactions', [$masterClass->id])}}" class="btn btn-warning"><i class="fa fa-receipt mr-1"></i>Transactions</a>
            <a href="{{route('master-classes.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-md-4">{{ __('ID') }}</dt>
                    <dd class="col-md-8">{{ $masterClass->id ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Title') }}</dt>
                    <dd class="col-md-8">{{ $masterClass->title ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Banner Image') }}</dt>
                    <dd class="col-md-8">
                        @if ($masterClass->banner_image)
                            <img src="{{ $masterClass->banner_image }}" width="150" alt="banner" />
                        @endif
                    </dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Date Time') }}</dt>
                    <dd class="col-md-8">{{ $dateTime ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Creator') }}</dt>
                    <dd class="col-md-8">
                        @php
                            $userName = $masterClass->user->full_name ?? '';
                            $phone = $masterClass->user->vPhoneNumber ? '<br /><small>'.$masterClass->user->vPhoneNumber.'</small>' : '';
                        @endphp
                        {!! $userName.$phone !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Meeting Link') }}</dt>
                    <dd class="col-md-8">{{ $masterClass->meeting_link ?? '-' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Payment Settled') }}</dt>
                    <dd class="col-md-8">{{ $masterClass->payment_settled ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Categories') }}</dt>
                    <dd class="col-md-8">{{ $categories }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($masterClass->status) }}</dd>
                </dl>
               
            </div>
        </div>
    </div>   
</div>
@endsection
