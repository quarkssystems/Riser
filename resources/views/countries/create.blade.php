@extends('adminlte::page')

@php
    $title = 'Create Country';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('countries.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form id="qsForm" method="POST" action="{{ route('countries.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class=" col-form-label text-md-end">{{ __('Country Name') }}</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" autocomplete="name" autofocus>

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

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
                            <a href="{{route('countries.index')}}" class="btn btn-secondary ml-1">Cancel</a>
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
