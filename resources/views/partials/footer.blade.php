<footer class="site-footer">
    <div class="app-container site-footer__inner">
        <div>
            <a class="brand" href="{{ route('home') }}">
                <span class="brand__mark" aria-hidden="true"><i class="fas fa-gamepad"></i></span>
                Pixel<span class="brand__accent">Pulse</span>
            </a>
            <p style="margin: 8px 0 0;">&copy; {{ date('Y') }} PixelPulse. All rights reserved.</p>
        </div>

        <ul class="site-footer__links">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('public.search') }}">Search</a></li>
            <li><a href="{{ route('api.docs') }}">API docs</a></li>
        </ul>
    </div>
</footer>
