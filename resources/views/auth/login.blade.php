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

    <form method="POST" action="{{ route('login') }}" autocomplete="off" id="loginForm">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   type="email" 
                   name="email" 
                   value=""
                   required 
                   autofocus 
                   autocomplete="new-email"
                   data-lpignore="true">
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
                   value=""
                   required 
                   autocomplete="new-password"
                   data-lpignore="true">
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

    <script>
        // Функция для полной очистки полей
        function clearFormFields() {
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            const form = document.getElementById('loginForm');
            
            if (emailField && passwordField) {
                // Очистить значения
                emailField.value = '';
                passwordField.value = '';
                
                // Сбросить форму
                if (form) {
                    form.reset();
                }
                
                // Очистить автозаполнение
                emailField.setAttribute('autocomplete', 'new-email');
                passwordField.setAttribute('autocomplete', 'new-password');
                
                // Добавить data-lpignore для LastPass
                emailField.setAttribute('data-lpignore', 'true');
                passwordField.setAttribute('data-lpignore', 'true');
                
                // Очистить браузерное хранилище
                try {
                    localStorage.removeItem('email');
                    localStorage.removeItem('password');
                    sessionStorage.clear();
                } catch(e) {
                    // Игнорируем ошибки
                }
            }
        }

        // Очистка при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            clearFormFields();
            
            // Дополнительная очистка через небольшую задержку
            setTimeout(clearFormFields, 50);
            setTimeout(clearFormFields, 200);
        });

        // Очистка при получении фокуса окном
        window.addEventListener('focus', clearFormFields);

        @if(session('logout_success') || session('clear_form'))
        // Если есть сессия выхода, дополнительная очистка
        setTimeout(function() {
            clearFormFields();
            
            // Принудительно скрыть автозаполнение браузера
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            
            if (emailField && passwordField) {
                emailField.blur();
                emailField.focus();
                emailField.blur();
                
                passwordField.blur();
                passwordField.focus();
                passwordField.blur();
            }
        }, 100);
        @endif
    </script>

    {{-- Мета-теги против кэширования --}}
    @if(session('logout_success') || session('clear_form'))
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="Thu, 01 Jan 1970 00:00:00 GMT">
    @endif
</x-guest-layout>