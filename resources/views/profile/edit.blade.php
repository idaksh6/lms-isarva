@extends('layouts.lms')

@section('title', 'Profile')
@section('page_title', 'Profile')

@section('content')
<div class="lms-page-content space-y-6">
    <div class="lms-card">
        @include('profile.partials.update-profile-information-form')
    </div>

    <div class="lms-card">
        @include('profile.partials.update-password-form')
    </div>

    <div class="lms-card">
        @include('profile.partials.delete-user-form')
    </div>
</div>
@endsection
