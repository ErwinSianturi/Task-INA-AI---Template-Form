@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div style="display: flex; align-items: center; justify-content: center; min-height: 70vh;">
    <div class="login-wrapper">
        <h2 class="login-title">Sign In</h2>
        
        <form action="{{ route('login') }}" method="POST">
            @csrf
            
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="input-control" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
                @error('email')
                    <div class="text-error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="input-control" required placeholder="••••••••">
                @error('password')
                    <div class="text-error">{{ $message }}</div>
                @enderror
            </div>
            
            <button type="submit" class="btn btn-primary w-full" style="margin-top: 1rem;">
                Sign In
            </button>
        </form>

        <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px dashed #E2E8F0; font-size: 0.8rem; color: var(--text-light);">
            <strong style="display: block; margin-bottom: 0.25rem; color: var(--primary);">Demo Credentials (password: password):</strong>
            <ul style="padding-left: 1rem;">
                <li>Employee: employee@example.com</li>
                <li>Manager: manager@example.com</li>
                <li>Finance: finance@example.com</li>
                <li>Admin: admin@example.com</li>
            </ul>
        </div>
    </div>
</div>
@endsection
