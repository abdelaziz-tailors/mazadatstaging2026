{{-- Run before stylesheets: a saved preference never waits for page scripts. --}}
<script>
(function () {
    'use strict';
    var root = document.documentElement, key = 'mazadat.theme', timer;
    var system = window.matchMedia('(prefers-color-scheme: dark)');
    function valid(value) { return ['light', 'dark', 'system'].indexOf(value) >= 0 ? value : 'system'; }
    var preference = 'system';
    try { preference = valid(localStorage.getItem(key)); } catch (error) { /* Private storage: keep an in-memory preference. */ }
    function apply(animate) {
        var theme = preference === 'system' ? (system.matches ? 'dark' : 'light') : preference;
        if (animate && document.body) {
            clearTimeout(timer);
            root.classList.add('md-theme-changing');
            timer = setTimeout(function () { root.classList.remove('md-theme-changing'); }, 260);
        }
        root.dataset.theme = theme;
        root.dataset.themePreference = preference;
        root.style.colorScheme = theme;
        window.dispatchEvent(new CustomEvent('mazadat:themechange', { detail: { theme: theme, preference: preference } }));
    }
    window.MazadatTheme = {
        get: function () { return preference; },
        set: function (value) {
            preference = valid(value);
            try { localStorage.setItem(key, preference); } catch (error) {}
            apply(true);
        }
    };
    system.addEventListener('change', function () { if (preference === 'system') apply(true); });
    window.addEventListener('storage', function (event) {
        if (event.key === key || event.key === null) { preference = valid(event.newValue); apply(true); }
    });
    apply(false);
})();
</script>
