@extends('adminlte::page')

@php
    $title = 'Create Call Package';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('call-packages.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form id="qsForm" method="POST" action="{{ route('call-packages.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class=" col-form-label text-md-end">{{ __('Name') }}</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" autocomplete="name" autofocus>

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="duration_minutes" class=" col-form-label text-md-end">{{ __('Duration (in minutes)') }}</label>
                            <input id="duration_minutes" type="text" class="form-control @error('duration_minutes') is-invalid @enderror" name="duration_minutes" value="{{ old('duration_minutes') }}" autocomplete="duration_minutes" autofocus>

                            @error('duration_minutes')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>                      
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price" class=" col-form-label text-md-end">{{ __('Price') }}</label>
                            <input id="price" type="text" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price') }}" autocomplete="price" autofocus>

                            @error('price')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="discount_percentage" class=" col-form-label text-md-end">{{ __('Discount Percentage') }}</label>
                            <input id="discount_percentage" type="text" class="form-control @error('discount_percentage') is-invalid @enderror" name="discount_percentage" value="{{ old('discount_percentage') }}" autocomplete="discount_percentage" autofocus>

                            @error('discount_percentage')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>                      
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            @php
                                $data[config('constant.status.active_value')] = config('constant.status.active_label');
                                $data[config('constant.status.inactive_value')] = config('constant.status.inactive_label');
                            @endphp
                            <label for="status" class=" col-form-label text-md-end">{{ __('Status') }}</label>
                            <select id="status" name="status" class="form-control select2 @error('status') is-invalid @enderror">

                                {!! getSelectOptions($data) !!}
                            </select>

                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>                        
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Submit') }}
                            </button>
                            <a href="{{route('call-packages.index')}}" class="btn btn-secondary ml-1">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script>
    $(document).ready(function() {
        $('#qsForm').validate({
            rules: {
                name: {
                    required: true,
                },
                duration_minutes: {
                    required: true,
                    number: true,
                },
                price: {
                    required: true,
                },
                discount_percentage: {
                    number: true,
                    min: 1,
                    max: 100,
                },
            },
            errorPlacement: function (error, element) {
                if(element.hasClass('select2') && element.next('.select2-container').length) {
                    error.insertAfter(element.next('.select2-container'));
                } else {
                    error.insertAfter(element);
                }
            }
        });
    });
    </script>
@endsection
