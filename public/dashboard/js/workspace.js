/* Progressive presentation and accessibility only. Existing page handlers own
   submissions, AJAX actions, DataTables configuration, and validation rules. */
(function () {
    'use strict';

    var root = document.body;
    if (!root.matches('.md-workspace, .md-auth')) return;
    var labelsNode = document.getElementById('workspace-ui-labels');
    var labels = labelsNode ? JSON.parse(labelsNode.textContent) : {};
    var $ = window.jQuery;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if ($ && reducedMotion.matches) $.fx.off = true;
    reducedMotion.addEventListener('change', function (event) { if ($) $.fx.off = event.matches; });
    function themeControls() {
        if (!window.MazadatTheme) return;
        var preference = window.MazadatTheme.get();
        document.querySelectorAll('[data-theme-choice]').forEach(function (button) {
            button.setAttribute('aria-checked', String(button.dataset.themeChoice === preference));
        });
        document.querySelectorAll('.md-theme-trigger').forEach(function (button) {
            button.setAttribute('aria-label', labels.theme + ': ' + labels['theme_' + preference]);
            button.title = labels.theme + ': ' + labels['theme_' + preference];
        });
    }
    document.querySelectorAll('[data-theme-choice]').forEach(function (button) {
        button.addEventListener('click', function () {
            window.MazadatTheme.set(button.dataset.themeChoice);
            button.closest('.md-theme-control').querySelector('.md-theme-trigger').focus();
        });
    });
    var chartFrame;
    window.addEventListener('mazadat:themechange', function () {
        themeControls();
        cancelAnimationFrame(chartFrame);
        chartFrame = requestAnimationFrame(function () {
            var tokens = getComputedStyle(document.documentElement);
            var text = tokens.getPropertyValue('--md-text-muted').trim();
            (window.mdDashboardCharts || []).forEach(function (chart) {
                chart.updateOptions({
                    theme: { mode: document.documentElement.dataset.theme },
                    chart: { foreColor: text, background: 'transparent' },
                    grid: { borderColor: tokens.getPropertyValue('--md-border').trim() },
                    xaxis: { labels: { style: { colors: text } } },
                    yaxis: { labels: { style: { colors: text } } },
                    stroke: chart.w.config.chart.type === 'donut' ? { colors: [tokens.getPropertyValue('--md-surface').trim()] } : {},
                }, false, false, false);
            });
        });
    });
    themeControls();

    function enhance(scope) {
        scope.querySelectorAll('.table-responsive, [class*="-table-scroll"]').forEach(function (region) {
            if (!region.hasAttribute('tabindex')) region.tabIndex = 0;
            if (!region.hasAttribute('role')) region.setAttribute('role', 'region');
            if (!region.hasAttribute('aria-label')) region.setAttribute('aria-label', labels.tableRegion);
        });
        scope.querySelectorAll('a[title], button[title]').forEach(function (control) {
            if (!control.textContent.trim() && !control.hasAttribute('aria-label')) {
                control.setAttribute('aria-label', control.title);
            }
        });
        scope.querySelectorAll('.md-icon-btn[data-bs-toggle="dropdown"]').forEach(function (control) {
            if (!control.hasAttribute('aria-label')) control.setAttribute('aria-label', labels.moreActions);
        });
        scope.querySelectorAll('.btn-close').forEach(function (control) {
            if (!control.hasAttribute('aria-label') || control.getAttribute('aria-label') === 'Close') control.setAttribute('aria-label', labels.closeDialog);
        });
        scope.querySelectorAll('.breadcrumb .active').forEach(function (crumb) { crumb.setAttribute('aria-current', 'page'); });
        scope.querySelectorAll('.dataTables_paginate .page-link').forEach(function (link) {
            var item = link.parentElement;
            if (item.classList.contains('previous')) link.setAttribute('aria-label', labels.previousPage);
            if (item.classList.contains('next')) link.setAttribute('aria-label', labels.nextPage);
            if (item.classList.contains('disabled')) {
                link.setAttribute('aria-disabled', 'true');
                link.tabIndex = -1;
            } else {
                link.removeAttribute('aria-disabled');
                if (link.tabIndex < 0) link.tabIndex = 0;
            }
            if (item.classList.contains('active')) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
        scope.querySelectorAll('.page-header .page-title:not(h1)').forEach(function (title) {
            title.setAttribute('role', 'heading');
            title.setAttribute('aria-level', '1');
        });
        scope.querySelectorAll('.modal').forEach(function (modal) {
            if (modal.dataset.mdLabelled) return;
            var title = modal.querySelector('.modal-title');
            if (title) {
                title.id = 'md-dialog-title-' + (++dialogId);
                modal.setAttribute('aria-labelledby', title.id);
            }
            modal.dataset.mdLabelled = 'true';
        });
        scope.querySelectorAll('input.form-control, select.form-control, select.form-select, textarea.form-control').forEach(function (input) {
            if (input.labels && input.labels.length) return;
            if (input.hasAttribute('aria-label') || input.hasAttribute('aria-labelledby')) return;
            var group = input.closest('.form-group');
            var label = group && group.querySelector('label');
            if (label && !label.htmlFor && group.querySelectorAll('input:not([type=hidden]),select,textarea').length === 1) {
                if (!input.id) input.id = 'md-field-' + (++fieldId);
                label.htmlFor = input.id;
            } else if (input.placeholder) {
                input.setAttribute('aria-label', input.placeholder);
            }
        });
        // A fixed Popper strategy keeps row action menus outside scroll clipping.
        scope.querySelectorAll('.table [data-bs-toggle="dropdown"]').forEach(function (control) {
            if (!control.hasAttribute('data-bs-display')) control.setAttribute('data-bs-boundary', 'viewport');
            if (window.bootstrap && !bootstrap.Dropdown.getInstance(control)) {
                new bootstrap.Dropdown(control, {
                    popperConfig: function (config) { return Object.assign({}, config, { strategy: 'fixed' }); }
                });
            }
        });
    }
    var fieldId = 0, dialogId = 0;
    enhance(document);

    // Enhance only inserted fragments, including AJAX rows and Livewire updates.
    var content = document.getElementById('main-content');
    if (content) {
        new MutationObserver(function (records) {
            var scopes = new Set();
            records.forEach(function (record) {
                record.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1 && node.namespaceURI !== 'http://www.w3.org/2000/svg') scopes.add(node.parentElement || node);
                });
            });
            scopes.forEach(enhance);
        }).observe(content, { childList: true, subtree: true });
    }

    // Use the server's field keys to connect the summary to existing inputs,
    // including Laravel's nested names (for example name.ar -> name[ar]).
    document.querySelectorAll('[data-error-field]').forEach(function (link, index) {
        var parts = link.dataset.errorField.split('.');
        var name = parts.shift() + parts.map(function (part) { return '[' + part + ']'; }).join('');
        var input = Array.from(document.querySelectorAll('input,select,textarea')).find(function (item) { return item.name === name; });
        if (!input) return;
        input.setAttribute('aria-invalid', 'true');
        if (!input.id) input.id = 'md-invalid-' + index;
        link.href = '#' + input.id;
        link.id = 'md-error-' + index;
        input.setAttribute('aria-describedby', ((input.getAttribute('aria-describedby') || '') + ' ' + link.id).trim());
        link.addEventListener('click', function (event) {
            event.preventDefault();
            if ($ && input.classList.contains('select2-hidden-accessible')) $(input).select2('open');
            else input.focus();
            input.scrollIntoView({ block: 'center', behavior: reducedMotion.matches ? 'auto' : 'smooth' });
        });
    });

    if ($) {
        $(document).on('init.dt draw.dt', function (event, settings) {
            if (!settings || !settings.nTable) return;
            enhance(settings.nTable.closest('.dataTables_wrapper') || document);
        });
        $(document).on('processing.dt', function (event, settings, busy) {
            if (!settings || !settings.nTable) return;
            settings.nTable.setAttribute('aria-busy', busy ? 'true' : 'false');
            var wrapper = settings.nTable.closest('.dataTables_wrapper');
            if (wrapper) wrapper.querySelectorAll('.dataTables_processing').forEach(function (notice) {
                notice.setAttribute('role', 'status');
                notice.setAttribute('aria-live', 'polite');
            });
        });
        $(document).on('error.dt', function (event, settings) {
            var wrapper = settings && settings.nTable && settings.nTable.closest('.dataTables_wrapper');
            if (!wrapper || wrapper.querySelector('.md-table-error')) return;
            var notice = document.createElement('div');
            notice.className = 'alert alert-danger md-table-error';
            notice.setAttribute('role', 'alert');
            notice.textContent = labels.tableError;
            wrapper.prepend(notice);
        });
        $(document).on('xhr.dt', function (event, settings, json) {
            var wrapper = settings && settings.nTable && settings.nTable.closest('.dataTables_wrapper');
            if (json && wrapper) wrapper.querySelectorAll('.md-table-error').forEach(function (notice) { notice.remove(); });
        });
    }

    var header = document.querySelector('.md-workspace .header');
    var searchButton = document.querySelector('.md-mobile-search');
    var search = document.querySelector('.md-header-search input');
    function setSearch(open, restoreFocus) {
        if (!header || !searchButton) return;
        header.classList.toggle('md-search-open', open);
        searchButton.setAttribute('aria-expanded', String(open));
        if (open) search.focus();
        else if (restoreFocus) searchButton.focus();
    }
    if (searchButton) searchButton.addEventListener('click', function () { setSearch(!header.classList.contains('md-search-open')); });
    document.addEventListener('click', function (event) {
        if (header && !header.contains(event.target)) setSearch(false);
    });

    var sidebar = document.getElementById('sidebar');
    var wrapper = document.querySelector('.main-wrapper');
    var mobileButton = document.getElementById('mobile_btn');
    var desktopButton = document.getElementById('toggle_btn');
    var mobile = window.matchMedia('(max-width: 991px)');
    var main = document.querySelector('.page-wrapper');
    var wasOpen = false;
    function mobileOpen() { return !!(wrapper && wrapper.classList.contains('slide-nav') && mobile.matches); }
    function closeNavigation() {
        if (mobileOpen() && mobileButton) mobileButton.click();
    }
    function navigationState() {
        if (!sidebar) return;
        var open = mobileOpen();
        sidebar.inert = mobile.matches && !open;
        if (main) main.inert = open;
        if (mobileButton) mobileButton.setAttribute('aria-expanded', String(open));
        if (desktopButton) desktopButton.setAttribute('aria-expanded', String(!root.classList.contains('mini-sidebar')));
        if (open) {
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
            sidebar.setAttribute('aria-label', document.querySelector('#sidebar-menu').getAttribute('aria-label'));
            if (!wasOpen) sidebar.querySelector('.md-navigation-close').focus();
        } else {
            sidebar.removeAttribute('role');
            sidebar.removeAttribute('aria-modal');
            sidebar.removeAttribute('aria-label');
            if (wasOpen && mobile.matches) mobileButton.focus();
        }
        wasOpen = open;
        sidebar.querySelectorAll('.submenu > a').forEach(function (toggle, index) {
            var list = toggle.nextElementSibling;
            toggle.setAttribute('role', 'button');
            toggle.setAttribute('aria-expanded', String(toggle.classList.contains('subdrop')));
            if (list) {
                if (!list.id) list.id = 'md-navigation-group-' + index;
                toggle.setAttribute('aria-controls', list.id);
            }
        });
        sidebar.querySelectorAll('a.active, li.active > a').forEach(function (link) {
            if (!link.parentElement.classList.contains('submenu')) link.setAttribute('aria-current', 'page');
        });
        sidebar.querySelectorAll('#sidebar-menu a').forEach(function (link) {
            if (!link.hasAttribute('aria-label')) link.setAttribute('aria-label', link.textContent.trim());
            if (!link.title) link.title = link.textContent.trim();
        });
    }
    if (sidebar) {
        navigationState();
        new MutationObserver(navigationState).observe(wrapper, { attributes: true, attributeFilter: ['class'] });
        new MutationObserver(navigationState).observe(root, { attributes: true, attributeFilter: ['class'] });
        sidebar.querySelector('.md-navigation-close').addEventListener('click', closeNavigation);
        sidebar.addEventListener('click', function () { navigationState(); });
        sidebar.addEventListener('keydown', function (event) {
            if (event.key === ' ' && event.target.matches('.submenu > a')) { event.preventDefault(); event.target.click(); }
        });
        sidebar.addEventListener('focusin', function () {
            if (root.classList.contains('mini-sidebar')) root.classList.add('expand-menu');
        });
        sidebar.addEventListener('focusout', function (event) {
            if (!sidebar.contains(event.relatedTarget)) root.classList.remove('expand-menu');
        });
        mobile.addEventListener('change', function () {
            if (!mobile.matches) {
                wrapper.classList.remove('slide-nav');
                document.documentElement.classList.remove('menu-opened');
                document.querySelectorAll('.sidebar-overlay').forEach(function (overlay) { overlay.classList.remove('opened'); });
            }
            navigationState();
        });
    }
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeNavigation();
            if (header && header.classList.contains('md-search-open')) setSearch(false, true);
        }
        if (mobileOpen() && event.key === 'Tab') {
            var controls = Array.from(sidebar.querySelectorAll('a[href],button,[tabindex="0"]')).filter(function (item) { return item.getClientRects().length > 0; });
            var first = controls[0], last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k' && search && !mobileOpen()) {
            event.preventDefault();
            if (window.innerWidth < 768) setSearch(true);
            else search.focus();
        }
    });

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.dataset.passwordToggle);
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(show));
            button.setAttribute('aria-label', show ? labels.passwordHide : labels.passwordShow);
            button.querySelector('i').className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        });
    });
})();
