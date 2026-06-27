<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">
                    <span>{{ TranslationHelper::translate('subscriber_dashboard') }}</span>
                </li>
                <li class="{{ Request::is(app()->getLocale() . '/admin') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard.index') }}">
                        <i class="fe fe-home"></i> <span>{{ TranslationHelper::translate('dashboard') }}</span>
                    </a>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin/videos/create') ? 'active' : '' }}">
                    <a href="{{ route('admin.videos.create') }}">
                        <i class="fe fe-plus-circle"></i>
                        <span>{{ TranslationHelper::translate('new Auction') }}</span>
                    </a>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin/videos') && ! request('archive') ? 'active' : '' }}">
                    <a href="{{ route('admin.videos.index') }}">
                        <i class="fe fe-video"></i>
                        <span>{{ TranslationHelper::translate('Auctions') }}</span>
                    </a>
                </li>

                {{-- <li class="{{ request('archive') ? 'active' : '' }}">
                    <a href="{{ route('admin.videos.index', ['archive' => 1]) }}">
                        <i class="fe fe-archive"></i>
                        <span>{{ TranslationHelper::translate('subscriber_dashboard_archive') }}</span>
                    </a>
                </li> --}}

                <li class="{{ Request::is(app()->getLocale() . '/admin/orders*') ? 'active' : '' }}">
                    <a href="{{ route('admin.orders.index') }}">
                        <i class="fe fe-shopping-cart"></i>
                        <span>{{ TranslationHelper::translate('subscriber_dashboard_orders_payments') }}</span>
                    </a>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin/seller-submissions*') ? 'active' : '' }}">
                    <a href="{{ route('admin.seller-submissions.index') }}">
                        <i class="fe fe-document"></i>
                        <span>{{ TranslationHelper::translate('seller_submissions') }}</span>
                    </a>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin/item-services*') ? 'active' : '' }}">
                    <a href="{{ route('admin.item-services.index') }}">
                        <i class="fe fe-tool"></i>
                        <span>{{ TranslationHelper::translate('item_services') }}</span>
                    </a>
                </li>

                {{-- <li class="menu-title">
                    <span>{{ TranslationHelper::translate('main') }}</span>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin/vendors*') ? 'active' : '' }}">
                    <a href="{{ route('admin.vendors.index') }}">
                        <i class="fe fe-users"></i> <span>{{ TranslationHelper::translate('Vendor') }}</span>
                    </a>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin/categories*') ? 'active' : '' }}">
                    <a href="{{ route('admin.categories.index') }}">
                        <i class="fe fe-list-task"></i> <span>{{ TranslationHelper::translate('Categories') }}</span>
                    </a>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin/cities*') ? 'active' : '' }}">
                    <a href="{{ route('admin.cities.index') }}">
                        <i class="fe fe-map-pin"></i> <span>{{ TranslationHelper::translate('Cities') }}</span>
                    </a>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin/colors*') ? 'active' : '' }}">
                    <a href="{{ route('admin.colors.index') }}">
                        <i class="fe fe-droplet"></i> <span>{{ TranslationHelper::translate('Colors') }}</span>
                    </a>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin/ages*') ? 'active' : '' }}">
                    <a href="{{ route('admin.ages.index') }}">
                        <i class="fe fe-calendar"></i> <span>{{ TranslationHelper::translate('Ages') }}</span>
                    </a>
                </li>

                <li class="{{ Request::is(app()->getLocale() . '/admin/animal-pens*') ? 'active' : '' }}">
                    <a href="{{ route('admin.animal-pens.index') }}">
                        <i class="fe fe-grid"></i> <span>{{ TranslationHelper::translate('Animal Pens') }}</span>
                    </a>
                </li> --}}
            </ul>
        </div>
    </div>
</div>
