<x-guest-layout>
    <div class="auth-header">
        <h1>Регистрация</h1>
        <p class="text-muted">Создайте новый аккаунт</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Имя</label>
            <input id="name" 
                   class="form-control @error('name') is-invalid @enderror" 
                   type="text" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required 
                   autofocus 
                   autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autocomplete="username">
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
                   required 
                   autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Подтвердите пароль</label>
            <input id="password_confirmation" 
                   class="form-control" 
                   type="password" 
                   name="password_confirmation" 
                   required 
                   autocomplete="new-password">
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
                Зарегистрироваться
            </button>
        </div>

        <div class="text-center mt-3">
            <span class="text-muted">Уже есть аккаунт?</span>
            <a href="{{ route('login') }}" class="text-decoration-none ms-1">
                Войти
            </a>
        </div>
    </form>
</x-guest-layout>