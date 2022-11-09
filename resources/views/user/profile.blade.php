@extends('adminlte::page')

@section('title', 'User Profile')

@section('content_header')
<h1 class="m-0 text-dark">User Profile</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Profile</h3>
            </div>
            <form action="{{ route('profile.update', auth()->id()) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="user-name">First Name</label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" id="user-first-name" placeholder="First Name" value="{{ old('first_name', $user->vFirstName)}}">
                    </div>
                    <div class="form-group">
                        <label for="user-name">Last Name</label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" id="user-last-name" placeholder="Last Name" value="{{ old('last_name', $user->vLastName)}}">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Email address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="exampleInputEmail1" placeholder="Email address" value="{{ old('email', $user->vEmail)}}" disabled>
                    </div>
                    <div class="form-group">
                        <label for="user-image">Profile Picture</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" name="profile_picture" class="custom-file-input" id="user-image">
                                <label class="custom-file-label" for="user-image">Choose file</label>
                            </div>
                            <div class="input-group-append">
                                <span class="input-group-text">Upload</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="user-password">Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="user-password" placeholder="Password">
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="confirm-password" placeholder="Confirm Password">
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
