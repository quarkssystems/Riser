@extends('adminlte::page')

@php
    $title = 'View Call Booking';
    $start_date = $callBooking->booking_date ?? null;
    $start_time = $callBooking->start_time ?? null;
    $end_time = $callBooking->end_time ?? null;
    $dateTime = $start_date.' '.$start_time.' - '.$end_time;

@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{route('call-bookings.transactions', [$callBooking->id])}}" class="btn btn-warning"><i class="fa fa-receipt mr-1"></i>Transactions</a>
            <a href="{{route('call-bookings.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
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
                    <dd class="col-md-8">{{ $callBooking->id ?? '' }}</dd>
                </dl>
                
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Call Package') }}</dt>
                    <dd class="col-md-8">{{ $callBooking->callPackage->name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User') }}</dt>
                    <dd class="col-md-8">{{ $callBooking->user->full_name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Creator') }}</dt>
                    <dd class="col-md-8">{{ $callBooking->creator->full_name ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Booking Date') }}</dt>
                    <dd class="col-md-8">{{ $dateTime ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Booking Message') }}</dt>
                    <dd class="col-md-8">{!! $callBooking->booking_message ?? '' !!}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Booking Amount') }}</dt>
                    <dd class="col-md-8">{{ $callBooking->booking_amount ?? '-' }}</dd>
                </dl>
              
                <dl class="row">
                    <dt class="col-md-4">{{ __('Meeting Link') }}</dt>
                    <dd class="col-md-8">{{ $callBooking->meeting_link ?? '-' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Payment Settled') }}</dt>
                    <dd class="col-md-8">{{ $callBooking->payment_settled ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($callBooking->status) }}</dd>
                </dl>
               
            </div>
        </div>
    </div>   
</div>
@endsection
