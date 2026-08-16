import './bootstrap';

// jQuery для Select2 и других компонентов
import $ from 'jquery';
window.$ = window.jQuery = $;

// Bootstrap JS приходит с CDN в layouts/app.blade.php и layouts/guest.blade.php,
// поэтому здесь его не импортируем — иначе на странице оказываются два экземпляра.

/* ---------- Theme switch -------------------------------------------------
 * The initial theme is applied inline in <head> to avoid a flash of the
 * wrong palette; this only wires up the toggle button.
 * ----------------------------------------------------------------------- */
function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.setAttribute('data-bs-theme', theme);

    try {
        localStorage.setItem('theme', theme);
    } catch (e) {
        // localStorage may be unavailable in private mode — theme still applies
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('themeToggle');

    if (toggle) {
        toggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    }

    /* ---------- Mobile navigation ---------------------------------------- */
    const burger = document.getElementById('navBurger');
    const header = document.getElementById('siteHeader');

    if (burger && header) {
        burger.addEventListener('click', () => {
            const isOpen = header.classList.toggle('is-open');
            burger.setAttribute('aria-expanded', String(isOpen));
        });
    }
});
