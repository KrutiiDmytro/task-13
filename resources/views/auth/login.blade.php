<x-guest-layout>
    <div class="auth-header">
        <h1>Вход</h1>
        <p class="text-muted">Войдите в свой аккаунт</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    @if (session('logout_success'))
        <div class="alert alert-success mb-4">
            {{ session('logout_success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}"
                   required 
                   autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Пароль</label>
            <input id="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   type="password" 
                   name="password"
                   required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-3">
            <div class="form-check">
                <input id="remember_me" 
                       type="checkbox" 
                       class="form-check-input" 
                       name="remember">
                <label class="form-check-label" for="remember_me">
                    Запомнить меня
                </label>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-decoration-none">
                    Забыли пароль?
                </a>
            @endif

            <button type="submit" class="btn btn-primary">
                Войти
            </button>
        </div>

        <div class="text-center mt-3">
            <span class="text-muted">Нет аккаунта?</span>
            <a href="{{ route('register') }}" class="text-decoration-none ms-1">
                Зарегистрироваться
            </a>
        </div>
    </form>
</x-guest-layout>