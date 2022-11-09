@extends('adminlte::page')

@php
    $title = 'Create Post';
@endphp

@section('title', $title)

@section('content_header')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ $title }}</h1>
        </div>
        <div class="col-md-4 text-right">
            <a href="{{route('posts.index')}}" class="btn btn-secondary"><i class="fa fa-arrow-left mr-1"></i> Back</a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form id="qsForm" method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="title" class=" col-form-label text-md-end">{{ __('Title') }}</label>
                            <input id="title" type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" autocomplete="title" autofocus>

                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div> 
                        
                        <div class="col-md-6">
                            <label for="country_id" class=" col-form-label text-md-end">{{ __('Country') }}</label>
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
                        <div class="col-md-6">
                            <label for="user_id" class=" col-form-label text-md-end">{{ __('Creator') }}</label>
                            <select id="user_id" name="user_id" class="form-control select2 @error('user_id') is-invalid @enderror">
                            </select>

                            @error('user_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="state_id" class=" col-form-label text-md-end">{{ __('State') }}</label>
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
                        <div class="col-md-6">
                            <label for="category_id" class=" col-form-label text-md-end">{{ __('Categories') }}</label>
                            <select id="category_id" name="category_id[]" class="form-control select2 @error('category_id') is-invalid @enderror" multiple>
                                @foreach ( $categoriesData as $category )
                                    <option value="{{ $category->id }}"> {{ $category->category_name }} </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <label for="language_id" class=" col-form-label text-md-end">{{ __('Languages') }}</label>
                            <select id="language_id" name="language_id[]" class="form-control select2 @error('language_id') is-invalid @enderror" multiple>
                                @foreach ( $languagesData as $language )
                                    <option value="{{ $language->id }}"> {{ $language->language_name }} </option>
                                @endforeach
                            </select>

                            @error('language_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="taluka_id" class=" col-form-label text-md-end">{{ __('Taluka') }}</label>
                            <select id="taluka_id" name="taluka_id" class="form-control select2 @error('taluka_id') is-invalid @enderror">
                            </select>

                            @error('taluka_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>            

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="hashtags" class=" col-form-label text-md-end">{{ __('Hashtags') }}</label>
                            <select id="hashtags" name="hashtags[]" class="form-control select2 @error('hashtags') is-invalid @enderror" multiple>
                                @foreach ( $hashtagsData as $hashtag )
                                    <option value="{{ $hashtag->id }}"> {{ $hashtag->hashtag_name }} </option>
                                @endforeach
                            </select>

                            @error('hashtags')
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

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="media_url" class=" col-form-label text-md-end">{{ __('Media ') }}</label>
                            <input id="media_url" type="file" class="form-control @error('media_url') is-invalid @enderror" name="media_url" value="{{ old('media_url') }}" autocomplete="media_url" autofocus>

                            @error('media_url')
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
                            <a href="{{route('posts.index')}}" class="btn btn-secondary ml-1">Cancel</a>
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
                title: {
                    required: true,
                },
                media_url: {
                    required: true,
                },
                "language_id[]": {
                    required: true,
                },
                "category_id[]": {
                    required: true,
                },
                user_id: {
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

        $(document).on('change', '#district_id', function(e) {
            var id = $(this).val();
            $('#taluka_id').empty().trigger('change');
            // console.log('id', id);
            if(id) {
                $.ajax({
                    url: "{{ url('talukas-list') }}",
                    data:{
                        'district_id': id,
                        '_token': '{{ csrf_token() }}',
                    },
                    method: 'POST',
                    cache: false,
                    success: function(response){
                        var html = '<option value="">Select Taluka</option>';
                        // console.log('response',response);
                        if(response) {
                            $.each(response, function(key, val) {
                                html += '<option value="'+val.id+'">'+val.name+'</option>';
                            });
                            $('#taluka_id').html(html);
                        }
                    }
                });
            }
        });

        $('#user_id').select2({
            ajax: {
                url: "{{ url('users-list') }}",
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params){
                    var query = {
                        'search_term': params.term,
                        'user_roles': "{{config('constant.roles.creator')}}",
                        '_token': '{{ csrf_token() }}',
                    }
                    return query
                },
                processResults : function(data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.iUserId,
                                text: item.full_name,
                            }
                        })
                    };
                },
                cache: true
            },
            placeholder: 'Search for a creator',
            minimumInputLength: 3
        });
    });
    </script>
@endsection
