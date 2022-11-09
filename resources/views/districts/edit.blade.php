@extends('adminlte::page')

@php
    $title = 'Edit District';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('districts.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form id="qsForm" method="POST" action="{{ route('districts.update', $district->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="country_id" class=" col-form-label text-md-end">{{ __('Country Name') }}</label>
                            <select id="country_id" name="country_id" class="form-control select2 @error('country_id') is-invalid @enderror">
                                <option value="">Select Country</option>
                                @foreach ( $countryData as $country )
                                    <option value="{{ $country->id }}" {{ ($country->id == $district->state->country->id) ? 'selected' : '' }}> {{ $country->name }} </option>
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
                            <label for="name" class=" col-form-label text-md-end">{{ __('District Name') }}</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $district->name) }}" autocomplete="name" autofocus>

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

                                {!! getSelectOptions($data, $district->status) !!}
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
                                {{ __('Update') }}
                            </button>
                            <a href="{{route('districts.index')}}" class="btn btn-secondary ml-1">Cancel</a>
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

        setTimeout(function () {
            var country_id = "{{ $district->state->country->id }}";
            if(country_id) {
                $('#country_id').trigger('change');
            }
        }, 500);
        
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
                        if(response) {
                            $.each(response, function(key, val) {
                                html += '<option value="'+val.id+'">'+val.name+'</option>';
                            });
                            $('#state_id').html(html);

                            var state_id = "{{ $district->state_id }}";
                            if(state_id) {
                                $('#state_id').val(state_id).trigger('change');
                            }
                        }
                    }
                });
            }
        });
    });
    </script>
@endsection
