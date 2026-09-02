@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('page_title', 'Pengaturan Profil Pengguna')
@section('page_subtitle', 'Kelola informasi identitas akun, kontak, dan keamanan kata sandi Anda')

@section('content')
<div style="max-width:850px;margin:0 auto;display:flex;flex-direction:column;gap:20px;">
    <div class="card" style="padding:24px;">
        <div style="max-width:600px;">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <div class="card" style="padding:24px;">
        <div style="max-width:600px;">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <div class="card" style="padding:24px;">
        <div style="max-width:600px;">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
