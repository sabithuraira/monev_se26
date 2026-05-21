@extends('layouts.login')

@section('content')
<div class="login-card">
    <div class="login-card-header">
        <h2><i class="fas fa-sign-in-alt me-2"></i>Masuk Akun</h2>
        <span>Gunakan email dan password Anda</span>
    </div>
    <div class="login-card-body">
        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-3">
                <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="nama@email.com"
                    required
                    autofocus
                >
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••"
                    required
                >
            </div>
            <div class="mb-4 form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1">
                <label for="remember" class="form-check-label">Ingat saya</label>
            </div>
            <button type="submit" class="btn btn-login-submit w-100 text-white">
                <i class="fas fa-sign-in-alt me-2"></i>Masuk
            </button>
        </form>
    </div>
</div>
@endsection
