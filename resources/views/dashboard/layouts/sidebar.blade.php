<div class="sidebar" id="sidebar">
    <div class="sidebar-inner">
        @include('dashboard.partials.navigation-brand')
        <nav id="sidebar-menu" class="sidebar-menu" aria-label="{{ __('dashboard-ui.navigation') }}">
            <ul>
                <li class="menu-title">
                    <span>{{ __('dashboard-ui.navigation') }}</span>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard.index') }}">
                        <i class="fa-solid fa-house"></i>
                        <span>{{ TranslationHelper::translate('dashboard') }}</span>
                    </a>
                </li>

                <li class="menu-title">
                    <span>{{ TranslationHelper::translate('platform_management') }}</span>
                </li>

                @if (Auth::guard('admin')->user()->canAny(['view admins', 'view roles']))
                    <li class="submenu">
                        <a href="#">
                            <i class="fa-solid fa-user-tie"></i>
                            <span>{{ TranslationHelper::translate('administration') }}</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (Auth::guard('admin')->user()->can('view admins'))
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/admins*') ? 'active' : '' }}"
                                        href="{{ route('admin.admins.index') }}">
                                        <i class="fa-solid fa-user-shield"></i>
                                        <span>{{ TranslationHelper::translate('admins') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if (Auth::guard('admin')->user()->can('view roles'))
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/roles*') ? 'active' : '' }}"
                                        href="{{ route('admin.roles.index') }}">
                                        <i class="fa-solid fa-key"></i>
                                        <span>{{ TranslationHelper::translate('roles') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (Auth::guard('admin')->user()->can('view users'))
                    <li>
                        <a class="{{ Request::is(app()->getLocale() . '/admin/users*') && request('user_type') === 'buyer' ? 'active' : '' }}"
                            href="{{ route('admin.users.index', ['user_type' => 'buyer']) }}">
                            <i class="fa-solid fa-user-check"></i>
                            <span>{{ __('dashboard-ui.nav_buyers') }}</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a class="{{ Request::is(app()->getLocale() . '/admin/vendors*') ? 'active' : '' }}"
                        href="{{ route('admin.vendors.index') }}">
                        <i class="fa-solid fa-store"></i>
                        <span>{{ __('dashboard-ui.nav_vendors') }}</span>
                    </a>
                </li>

                @if (Auth::guard('admin')->user()->canAny(['view partners', 'delete partner', 'update partner', 'add partner']))
                    <li class="submenu">
                        <a href="#">
                            <i class="fa-solid fa-user-tie"></i>
                            <span>{{ __('dashboard-ui.nav_partners') }}</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (Auth::guard('admin')->user()->canAny(['view partners', 'delete partner', 'update partner']))
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/partners*') ? 'active' : '' }}"
                                        href="{{ route('admin.partners.index') }}">
                                        <i class="fa-solid fa-users"></i>
                                        <span>{{ __('dashboard-ui.nav_all_partners') }}</span>
                                    </a>
                                </li>
                            @endif

                            @if (Auth::guard('admin')->user()->can('add partner'))
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/partners/create*') ? 'active' : '' }}"
                                        href="{{ route('admin.partners.create') }}">
                                        <i class="fa-solid fa-user-plus"></i>
                                        <span>{{ __('dashboard-ui.nav_add_partner') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (Auth::guard('admin')->user()->canAny('view videos'))
                    <li class="submenu">
                        <a href="#">
                            <i class="fa-solid fa-gavel"></i>
                            <span>{{ __('dashboard-ui.nav_auctions') }}</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ Request::is(app()->getLocale() . '/admin/auctions*') ? 'active' : '' }}"
                                    href="{{ route('admin.auctions.index') }}">
                                    <i class="fa-solid fa-list-ul"></i>
                                    <span>{{ __('dashboard-ui.nav_auctions') }}</span>
                                </a>
                            </li>
                            <li>
                                <a class="{{ Request::is(app()->getLocale() . '/admin/seller-submissions*') ? 'active' : '' }}"
                                    href="{{ route('admin.seller-submissions.index') }}"
                                    title="{{ __('dashboard-ui.nav_submission_hint') }}">
                                    <i class="fa-solid fa-file-signature"></i>
                                    <span>{{ __('dashboard-ui.nav_submissions') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if (Auth::guard('admin')->user()->canAny('view videos'))
                    <li class="submenu">
                        <a href="#">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span>{{ __('dashboard-ui.nav_orders') }}</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ Request::is(app()->getLocale() . '/admin/orders*') ? 'active' : '' }}"
                                    href="{{ route('admin.orders.index') }}">
                                    <i class="fa-solid fa-clipboard-list"></i>
                                    <span>{{ __('dashboard-ui.nav_orders') }}</span>
                                </a>
                            </li>

                            <li>
                                <a class="{{ Request::is(app()->getLocale() . '/admin/partner-finance/invoices*') ? 'active' : '' }}"
                                    href="{{ route('admin.partner-finance.invoices') }}">
                                    <i class="fa-solid fa-file-invoice"></i>
                                    <span>{{ __('dashboard-ui.nav_invoices') }}</span>
                                </a>
                            </li>

                            <li>
                                <a class="{{ Request::is(app()->getLocale() . '/admin/partner-finance/wallet*') ? 'active' : '' }}"
                                    href="{{ route('admin.partner-finance.wallet') }}">
                                    <i class="fa-solid fa-wallet"></i>
                                    <span>{{ __('dashboard-ui.nav_wallet') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if (\App\Support\PartnerDashboardScope::isPartner() || Auth::guard('admin')->user()->canAny(['view packages', 'user-subscriptions.view', 'view user subscriptions']))
                    <li class="submenu">
                        <a href="#">
                            <i class="fa-solid fa-box"></i>
                            <span>{{ __('dashboard-ui.nav_subscriptions') }}</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @if (Auth::guard('admin')->user()->can('view packages'))
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/packages*') ? 'active' : '' }}"
                                        href="{{ route('admin.packages.index') }}">
                                        <i class="fa-solid fa-box-open"></i>
                                        <span>{{ __('dashboard-ui.nav_packages') }}</span>
                                    </a>
                                </li>
                            @endif

                            <li>
                                <a class="{{ Request::is(app()->getLocale() . '/admin/user-subscriptions*') ? 'active' : '' }}"
                                    href="{{ route('admin.user-subscriptions.index') }}">
                                    <i class="fa-solid fa-crown"></i>
                                    <span>{{ __('dashboard-ui.nav_user_subscriptions') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <li>
                    <a class="{{ Request::is(app()->getLocale() . '/admin/item-services*') ? 'active' : '' }}"
                        href="{{ route('admin.item-services.index') }}">
                        <i class="fa-solid fa-wrench"></i>
                        <span>{{ TranslationHelper::translate('item_services') }}</span>
                    </a>
                </li>

                <li class="menu-title">
                    <span>{{ TranslationHelper::translate('content_and_categorization') }}</span>
                </li>

                @if (Auth::guard('admin')->user()->canAny(['view videos']))
                    <li>
                        <a class="{{ Request::is(app()->getLocale() . '/admin/notifications*') ? 'active' : '' }}"
                            href="{{ route('admin.notifications.index') }}">
                            <i class="fa-solid fa-bell"></i>
                            <span>{{ __('dashboard-ui.nav_notifications') }}</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a class="{{ Request::is(app()->getLocale() . '/admin/sliders*') ? 'active' : '' }}"
                        href="{{ route('admin.sliders.index') }}">
                        <i class="fa-solid fa-images"></i>
                        <span>{{ __('dashboard-ui.nav_sliders') }}</span>
                    </a>
                </li>

                <li>
                    <a class="{{ Request::is(app()->getLocale() . '/admin/ages*') ? 'active' : '' }}"
                        href="{{ route('admin.ages.index') }}">
                        <i class="fa-solid fa-cake-candles"></i>
                        <span>{{ TranslationHelper::translate('ages') }}</span>
                    </a>
                </li>

                @if (Auth::guard('admin')->user()->canAny(['edit page']))
                    <li class="submenu">
                        <a href="#">
                            <i class="fa-solid fa-file-lines"></i>
                            <span>{{ __('dashboard-ui.nav_pages') }}</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li>
                                <a class="{{ Request::is(app()->getLocale() . '/admin/pages/1/edit*') ? 'active' : '' }}"
                                    href="{{ route('admin.pages.edit', [1]) }}">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>{{ __('dashboard-ui.nav_about') }}</span>
                                </a>
                            </li>
                            <li>
                                <a class="{{ Request::is(app()->getLocale() . '/admin/pages/2/edit*') ? 'active' : '' }}"
                                    href="{{ route('admin.pages.edit', [2]) }}">
                                    <i class="fa-solid fa-file-contract"></i>
                                    <span>{{ __('dashboard-ui.nav_terms') }}</span>
                                </a>
                            </li>
                            <li>
                                <a class="{{ Request::is(app()->getLocale() . '/admin/pages/3/edit*') ? 'active' : '' }}"
                                    href="{{ route('admin.pages.edit', [3]) }}"
                                    title="{{ __('dashboard-ui.nav_privacy') }}">
                                    <i class="fa-solid fa-user-shield"></i>
                                    <span>{{ __('dashboard-ui.nav_privacy') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if (Auth::guard('admin')->user()->canAny('view videos'))
                    <li>
                        <a class="{{ Request::is(app()->getLocale() . '/admin/settings*') ? 'active' : '' }}"
                            href="{{ route('admin.settings.edit') }}">
                            <i class="fa-solid fa-gear"></i>
                            <span>{{ __('dashboard-ui.nav_settings') }}</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</div>
