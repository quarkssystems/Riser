@extends('adminlte::page')

@php
    $title = 'Create Taluka';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('talukas.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form id="qsForm" method="POST" action="{{ route('talukas.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="country_id" class=" col-form-label text-md-end">{{ __('Country Name') }}</label>
                            <select id="country_id" name="country_id" class="form-control select2 @error('country_id') is-invalid @enderror">
                                <option value="">Select Country</option>
                                @foreach ( $countryData as $country )
                                    <option value="{{ $country->id }}"> {{ $country->name }} </option>
                                @endforeach
                            </select>

                            @error('country_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="state_id" class=" col-form-label text-md-end">{{ __('State Name') }}</label>
                            <select id="state_id" name="state_id" class="form-control select2 @error('state_id') is-invalid @enderror">
                            </select>

                            @error('state_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="district_id" class=" col-form-label text-md-end">{{ __('District') }}</label>
                            <select id="district_id" name="district_id" class="form-control select2 @error('district_id') is-invalid @enderror">
                            </select>

                            @error('district_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="name" class=" col-form-label text-md-end">{{ __('Taluka Name') }}</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" autocomplete="name" autofocus>

                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
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
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Submit') }}
                            </button>
                            <a href="{{route('talukas.index')}}" class="btn btn-secondary ml-1">Cancel</a>
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
                country_id: {
                    required: true,
                },
                state_id: {
                    required: true,
                },
                district_id: {
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

        $(document).on('change', '#country_id', function(e) {
            var id = $(this).val();
            $('#state_id').empty().trigger('change');
            if(id) {
                $.ajax({
                    url: "{{ url('states-list') }}",
                    data:{
                        'country_id': id,
                        '_token': '{{ csrf_token() }}',
                    },
                    method: 'POST',
                    cache: false,
                    success: function(response){
                        var html = '<option value="">Select State</option>';
                        // console.log('response',response);
                        if(response) {
                            $.each(response, function(key, val) {
                                html += '<option value="'+val.id+'">'+val.name+'</option>';
                            });
                            $('#state_id').html(html);
                        }
                    }
                });
            }
        });

        $(document).on('change', '#state_id', function(e) {
            var id = $(this).val();
            // console.log('id', id);
            
            $('#district_id').empty().trigger('change');
            
            if(id) {
                $.ajax({
                    url: "{{ url('districts-list') }}",
                    data:{
                        'state_id': id,
                        '_token': '{{ csrf_token() }}',
                    },
                    method: 'POST',
                    cache: false,
                    success: function(response){
                        var html = '<option value="">Select District</option>';
                        // console.log('response',response);
                        if(response) {
                            $.each(response, function(key, val) {
                                html += '<option value="'+val.id+'">'+val.name+'</option>';
                            });
                            $('#district_id').html(html);
                        }
                    }
                });
            }
        });
    });
    </script>
@endsection
