@extends('adminlte::page')

@php
    $title = 'Edit Creator';
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
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form id="qsForm" method="POST" action="{{ route('creators.update', $creator->iUserId) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class=" col-form-label text-md-end">{{ __('First Name') }}</label>
                            <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="{{ old('first_name', $creator->vFirstName) }}" autocomplete="first_name" autofocus>

                            @error('first_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="last_name" class=" col-form-label text-md-end">{{ __('Last Name') }}</label>
                            <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name', $creator->vLastName) }}" autocomplete="last_name" autofocus>

                            @error('last_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class=" col-form-label text-md-end">{{ __('Email') }}</label>
                            <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $creator->vEmail) }}" autocomplete="email" autofocus readonly>

                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="username" class=" col-form-label text-md-end">{{ __('Username') }}</label>
                            <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username', $creator->username) }}" autocomplete="username" autofocus>

                            @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class=" col-form-label text-md-end">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class=" col-form-label text-md-end">{{ __('Confirm Password') }}</label>
                            <input id="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" value="">

                            @error('password_confirmation')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="profile_picture" class=" col-form-label text-md-end">{{ __('Profile Picture') }}</label>
                            <input id="profile_picture" type="file" class="form-control @error('profile_picture') is-invalid @enderror" name="profile_picture" value="" autocomplete="profile_picture" autofocus>

                            @error('profile_picture')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <img src="{{ $creator->profile_picture_url }}" width="50" alt="profile" class="mt-4 user-image img-circle elevation-2" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            @php
                                $dataGen[config('constant.gender.male')] = config('constant.gender.male');
                                $dataGen[config('constant.gender.female')] = config('constant.gender.female');
                                $dataGen[config('constant.gender.other')] = config('constant.gender.other');
                            @endphp
                            <label for="gender" class=" col-form-label text-md-end">{{ __('Gender') }}</label>
                            <select id="gender" name="gender" class="form-control select2 @error('gender') is-invalid @enderror">
                                <option value="">Select Gender</option>
                                {!! getSelectOptions($dataGen, $creator->eGender) !!}
                            </select>

                            @error('gender')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="profession" class=" col-form-label text-md-end">{{ __('Profession') }}</label>
                            <input id="profession" type="text" class="form-control @error('profession') is-invalid @enderror" name="profession" value="{{ old('profession', $creator->vOccupation) }}" autocomplete="profession" autofocus>

                            @error('profession')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="contact_number" class=" col-form-label text-md-end">{{ __('Contact Number') }}</label>
                            <input id="contact_number" type="text" class="form-control @error('contact_number') is-invalid @enderror" name="contact_number" value="{{ old('contact_number', $creator->vPhoneNumber) }}" autocomplete="contact_number" autofocus>

                            @error('contact_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_number" class=" col-form-label text-md-end">{{ __('Whatsapp Number') }}</label>
                            <input id="whatsapp_number" type="text" class="form-control @error('whatsapp_number') is-invalid @enderror" name="whatsapp_number" value="{{ old('whatsapp_number', $creator->whatsapp_number) }}" autocomplete="whatsapp_number" autofocus>

                            @error('whatsapp_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="about_me" class=" col-form-label text-md-end">{{ __('About Me') }}</label>
                            <textarea id="about_me" class="form-control @error('about_me') is-invalid @enderror" name="about_me">{{ old('about_me', $creator->tAboutMe) }}</textarea>

                            @error('about_me')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="user_skills" class=" col-form-label text-md-end">{{ __('User Skills') }}</label>
                            <textarea id="user_skills" class="form-control @error('user_skills') is-invalid @enderror" name="user_skills">{{ old('user_skills', $creator->vSkill) }}</textarea>

                            @error('user_skills')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="user_experience" class=" col-form-label text-md-end">{{ __('User Experience') }}</label>
                            <textarea id="user_experience" class="form-control @error('user_experience') is-invalid @enderror" name="user_experience">{{ old('user_experience', $creator->vExperience) }}</textarea>

                            @error('user_experience')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="business_name" class=" col-form-label text-md-end">{{ __('Business Name') }}</label>
                            <textarea id="business_name" class="form-control @error('business_name') is-invalid @enderror" name="business_name">{{ old('business_name', $creator->business_name) }}</textarea>

                            @error('business_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="country_id" class=" col-form-label text-md-end">{{ __('Country') }}</label>
                            <select id="country_id" name="country_id" class="form-control select2 @error('country_id') is-invalid @enderror">
                                <option value="">Select Country</option>
                                @foreach ( $countryData as $country )
                                    <option value="{{ $country->id }}" {{ ($country->id == $creator->country_id) ? 'selected' : '' }}> {{ $country->name }} </option>
                                @endforeach
                            </select>

                            @error('country_id')
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
                            <label for="district_id" class=" col-form-label text-md-end">{{ __('District') }}</label>
                            <select id="district_id" name="district_id" class="form-control select2 @error('district_id') is-invalid @enderror">
                            </select>

                            @error('district_id')
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
                            <label for="facebook_link" class=" col-form-label text-md-end">{{ __('Facebook Link') }}</label>
                            <input id="facebook_link" type="text" class="form-control @error('facebook_link') is-invalid @enderror" name="facebook_link" value="{{ old('facebook_link', $creator->facebook_link) }}" autocomplete="facebook_link" autofocus>

                            @error('facebook_link')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="twitter_link" class=" col-form-label text-md-end">{{ __('Twitter Link') }}</label>
                            <input id="twitter_link" type="text" class="form-control @error('twitter_link') is-invalid @enderror" name="twitter_link" value="{{ old('twitter_link', $creator->twitter_link) }}" autocomplete="twitter_link" autofocus>

                            @error('twitter_link')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="linkedin_link" class=" col-form-label text-md-end">{{ __('Linkedin Link') }}</label>
                            <input id="linkedin_link" type="text" class="form-control @error('linkedin_link') is-invalid @enderror" name="linkedin_link" value="{{ old('linkedin_link', $creator->linkedin_link) }}" autocomplete="linkedin_link" autofocus>

                            @error('linkedin_link')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="instagram_link" class=" col-form-label text-md-end">{{ __('Instagram Link') }}</label>
                            <input id="instagram_link" type="text" class="form-control @error('instagram_link') is-invalid @enderror" name="instagram_link" value="{{ old('instagram_link', $creator->instagram_link) }}" autocomplete="instagram_link" autofocus>

                            @error('instagram_link')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="youtube_link" class=" col-form-label text-md-end">{{ __('Youtube Link') }}</label>
                            <input id="youtube_link" type="text" class="form-control @error('youtube_link') is-invalid @enderror" name="youtube_link" value="{{ old('youtube_link', $creator->youtube_link) }}" autocomplete="youtube_link" autofocus>

                            @error('youtube_link')
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

                                {!! getSelectOptions($data, $creator->status) !!}
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
                                {{ __('Update') }}
                            </button>
                            <a href="{{route('creators.index')}}" class="btn btn-secondary ml-1">Cancel</a>
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
            var country_id = "{{ $creator->country_id }}";
            if(country_id) {
                $('#country_id').trigger('change');
            }
        }, 500);
        
        $('#qsForm').validate({
            rules: {
                first_name: {
                    required: true,
                },
                last_name: {
                    required: true,
                },
                email: {
                    required: function(element){
                        return $("#contact_number").val() == '';
                    },
                    email: true
                },
                password: {
                    minlength: 8,
                },
                password_confirmation: {
                    equalTo: '#password'
                },
                contact_number: {
                    required: function(element){
                        return $("#email").val() == '';
                    },
                    number: true,
                    minlength: 8,
                    maxlength: 13
                },
                whatsapp_number: {
                    number: true,
                    minlength: 8,
                    maxlength: 13
                },
                facebook_link: {
                    url: true
                },
                twitter_link: {
                    url: true
                },
                linkedin_link: {
                    url: true
                },
                instagram_link: {
                    url: true
                },
                youtube_link: {
                    url: true
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

                            var state_id = "{{ $creator->state_id }}";
                            if(state_id) {
                                $('#state_id').val(state_id).trigger('change');
                            }
                        }
                    }
                });
            }
        });

        $(document).on('change', '#state_id', function(e) {
            var id = $(this).val();
            
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
                        if(response) {
                            $.each(response, function(key, val) {
                                html += '<option value="'+val.id+'">'+val.name+'</option>';
                            });
                            $('#district_id').html(html);

                            var district_id = "{{ $creator->district_id }}";
                            if(district_id) {
                                $('#district_id').val(district_id).trigger('change');
                            }
                        }
                    }
                });
            }
        });

        $(document).on('change', '#district_id', function(e) {
            var id = $(this).val();
            $('#taluka_id').empty().trigger('change');
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
                        if(response) {
                            $.each(response, function(key, val) {
                                html += '<option value="'+val.id+'">'+val.name+'</option>';
                            });
                            $('#taluka_id').html(html);

                            var taluka_id = "{{ $creator->taluka_id }}";
                            if(taluka_id) {
                                $('#taluka_id').val(taluka_id);
                            }
                        }
                    }
                });
            }
        });
    });
    </script>
@endsection
