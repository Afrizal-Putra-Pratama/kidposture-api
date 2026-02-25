<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Posturely Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="landing-page">
    <div class="login-wrapper">
        <div class="card card--shadow login-card-container">
            
            <div class="login-header">
                <div class="landing-logo">
                    <span class="landing-logo__dot"></span>
                    Posturely
                </div>
                <p class="login-subtitle">Admin Panel</p>
            </div>
            
            <div class="login-body">
                @if($errors->any())
                    <div class="alert-danger">
                        <i class="bi bi-exclamation-circle"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-container">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" 
                                   class="form-input @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus
                                   placeholder="admin@example.com">
                        </div>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-container">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" 
                                   class="form-input @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required
                                   placeholder="Enter your password">
                        </div>
                        @error('password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="check-input" id="remember" name="remember">
                        <label class="check-label" for="remember">Remember me</label>
                    </div>

                    <button type="submit" class="hero__btn hero__btn--primary hero__btn--full">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</body>
</html>