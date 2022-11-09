@extends('adminlte::page')

@php
    $title = 'View Payment Transaction';
    $module_type = '';
    $module_name = '';
    if($paymentTransaction->master_class_id) {
        $module_type = 'Master Class';
        $module_name = $paymentTransaction->masterClasses->title;
    } else if($paymentTransaction->call_booking_id) {
        $module_type = 'Call Booking';
        $call_bookings = $paymentTransaction->callBookings ?? '';
        $module_name = $call_bookings ? $call_bookings->callPackage->name : '';
    }
                    
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-5">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-7 text-right">
            <a href="{{route('payment-transactions.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
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
                    <dd class="col-md-8">{{ $paymentTransaction->id ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('User') }}</dt>
                    <dd class="col-md-8">{{ $paymentTransaction->user->full_name ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Payment Gateway') }}</dt>
                    <dd class="col-md-8">{{ $paymentTransaction->payment_gateway ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Transaction Id') }}</dt>
                    <dd class="col-md-8">{{ $paymentTransaction->transaction_id ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Module Type') }}</dt>
                    <dd class="col-md-8">{{ $module_type }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Module Name') }}</dt>
                    <dd class="col-md-8">{{ $module_name ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Sub Total') }}</dt>
                    <dd class="col-md-8">{{ config('constant.rupee_symbol').$paymentTransaction->sub_total ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Tax') }}</dt>
                    <dd class="col-md-8">{{ $paymentTransaction->tax ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Discount Amount') }}</dt>
                    <dd class="col-md-8">{{ config('constant.rupee_symbol').$paymentTransaction->discount_amount ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Discount Code') }}</dt>
                    <dd class="col-md-8">{{ $paymentTransaction->discount_code ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Total') }}</dt>
                    <dd class="col-md-8">{{ config('constant.rupee_symbol').$paymentTransaction->total ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Payment Type') }}</dt>
                    <dd class="col-md-8">{{ $paymentTransaction->payment_type ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Affiliate User') }}</dt>
                    <dd class="col-md-8">{{ $paymentTransaction->affiliateUser->full_name ?? '' }}</dd>
                </dl>
                
                <dl class="row">
                    <dt class="col-md-4">{{ __('Payment Settled') }}</dt>
                    <dd class="col-md-8">{{ $paymentTransaction->payment_settled ?? '' }}</dd>
                </dl>

                <dl class="row">
                    <dt class="col-md-4">{{ __('Status') }}</dt>
                    <dd class="col-md-8">{{ ucfirst($paymentTransaction->status) }}</dd>
                </dl>
               
            </div>
        </div>
    </div>   
</div>
@endsection
