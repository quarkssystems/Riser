@extends('errors::illustrated-layout')

@section('title', __('Forbidden'))
@section('code', '403')



@section('image')

<div style="background-image: url('{{ asset(config('adminlte.logo_img_xl')) }}');" class="absolute pin bg-no-repeat md:bg-left lg:bg-center">
</div>

@endsection

@section('message', __($exception->getMessage() ?: 'Forbidden'))
