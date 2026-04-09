<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">
                    <span>{{ TranslationHelper::translate('main') }}</span>
                </li>
                <li class="{{ Request::is(app()->getLocale() . '/admin') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard.index') }}">
                        <i class="fe fe-home"></i> <span>{{ TranslationHelper::translate('dashboard') }}</span>
                    </a>
                </li>


                @if (Auth::guard('admin')->user()->canAny(['view admins', 'view roles']))
                    <li class="submenu">
                        <a href="#"><i class="fa-solid fa-user-tie"></i> <span>
                                {{ TranslationHelper::translate('administration') }}</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            @if (Auth::guard('admin')->user()->can('view admins'))
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/admins*') ? 'active' : '' }}"
                                       href="{{ route('admin.admins.index') }}">
                                        {{ TranslationHelper::translate('admins') }}
                                    </a>
                                </li>
                            @endif

                            @if (Auth::guard('admin')->user()->can('view roles'))
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/roles*') ? 'active' : '' }}"
                                       href="{{ route('admin.roles.index') }}">
                                        {{ TranslationHelper::translate('roles') }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if (Auth::guard('admin')->user()->canAny(['view users']))


                    @if (Auth::guard('admin')->user()->can('view users'))

                        <li>
                            <a class="{{ (Request::is(app()->getLocale() . '/admin/users*') ) ? 'active' : '' }}"
                               href="{{ route('admin.users.index') }}">
                                <i class="fe fe-user"></i> <span>{{ TranslationHelper::translate('User') }}</span>
                            </a>
                        </li>


                        @endif


                        </li>
                    @endif



                    @if (Auth::guard('admin')->user()->canAny(['view partners', 'delete partner','update partner', 'add partner']))
                        <li class="submenu ">
                            <a href="#"><i class="fa-solid fa-user-tie"></i> <span>
                                {{ TranslationHelper::translate('Partners') }}</span> <span
                                    class="menu-arrow"></span></a>
                            <ul>
                                @if (Auth::guard('admin')->user()->canAny(['view partners', 'delete partner','update partner']))
                                    <li>
                                        <a class="{{ Request::is(app()->getLocale() . '/admin/partners*') ? 'active' : '' }}"
                                           href="{{ route('admin.partners.index') }}">
                                            {{ TranslationHelper::translate('View partners') }}
                                        </a>
                                    </li>
                                @endif

                                @if (Auth::guard('admin')->user()->can('add partner'))
                                    <li>
                                        <a class="{{ Request::is(app()->getLocale() . '/admin/partners/create*') ? 'active' : '' }}"
                                           href="{{ route('admin.partners.create') }}">
                                            {{ TranslationHelper::translate('add partner') }}
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if (Auth::guard('admin')->user()->canAny('view videos'))

                    <li>
                        <a class="{{ (Request::is(app()->getLocale() . '/admin/auctions*') ) ? 'active' : '' }}"
                           href="{{ route('admin.auctions.index') }}">
                            <i class="fe fe-list-task"></i>
                            <span>{{ TranslationHelper::translate('Auctions') }}</span>
                        </a>
                    </li>

                    <li>
                        <a class="{{ (Request::is(app()->getLocale() . '/admin/orders*') ) ? 'active' : '' }}"
                           href="{{ route('admin.orders.index') }}">
                            <i class="fe fe-list-task"></i>
                            <span>{{ TranslationHelper::translate('Orders') }}</span>
                        </a>
                    </li>

                    <li>
                        <a class="{{ (Request::is(app()->getLocale() . '/admin/settings*') ) ? 'active' : '' }}"
                           href="{{ route('admin.settings.edit') }}">
                            <i class="fe fe-list-task"></i>
                            <span>{{ TranslationHelper::translate('Settings') }}</span>
                        </a>
                    </li>

            @endif

                @if (Auth::guard('admin')->user()->canAny(['view packages', 'user-subscriptions.view']))
                    <li class="submenu">
                        <a href="#"><i class="fa-solid fa-box"></i> <span>
                            {{ TranslationHelper::translate('Subscriptions') }}</span> <span
                            class="menu-arrow"></span></a>
                        <ul>
                            @if (Auth::guard('admin')->user()->can('view packages'))
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/packages*') ? 'active' : '' }}"
                                       href="{{ route('admin.packages.index') }}">
                                        {{ TranslationHelper::translate('Packages') }}
                                    </a>
                                </li>
                            @endif

                            {{-- @if (Auth::guard('admin')->user()->can('view user subscriptions')) --}}
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/user-subscriptions*') ? 'active' : '' }}"
                                       href="{{ route('admin.user-subscriptions.index') }}">
                                        {{ TranslationHelper::translate('User Subscriptions') }}
                                    </a>
                                </li>
                            {{-- @endif --}}
                        </ul>
                    </li>
                @endif

                    <li>
                        <a class="{{ (Request::is(app()->getLocale() . '/admin/colors*') ) ? 'active' : '' }}"
                           href="{{ route('admin.colors.index') }}">
                            <i class="fe fe-plus"></i> <span>{{ TranslationHelper::translate('Colors') }}</span>
                        </a>
                    </li>


                                                <li>
                                                    <a class="{{ (Request::is(app()->getLocale() . '/admin/categories*') ) ? 'active' : '' }}"
                                                       href="{{ route('admin.categories.index') }}">
                                                        <i class="fe fe-list-task"></i> <span>{{ TranslationHelper::translate('Categories') }}</span>
                                                    </a>
                                                </li>






                    {{--                    @if (Auth::guard('admin')->user()->can('view vendors'))--}}


                    {{--                            <li>--}}
                    {{--                                <a class="{{ (Request::is(app()->getLocale() . '/admin/vendors*') ) ? 'active' : '' }}"--}}
                    {{--                                   href="{{ route('admin.vendors.index') }}">--}}
                    {{--                                    <i class="fe fe-user"></i> <span>{{ TranslationHelper::translate('Vendor') }}</span>--}}
                    {{--                                </a>--}}
                    {{--                            </li>--}}


                    {{--                        @endif--}}

                    {{--                        @if (Auth::guard('admin')->user()->can('view videos'))--}}


                    {{--                            <li>--}}
                    {{--                                <a class="{{ (Request::is(app()->getLocale() . '/admin/videos*') ) ? 'active' : '' }}"--}}
                    {{--                                   href="{{ route('admin.videos.index') }}">--}}
                    {{--                                    <i class="fe fe-user"></i> <span>{{ TranslationHelper::translate('Auctions') }}</span>--}}
                    {{--                                </a>--}}
                    {{--                            </li>--}}


                    {{--                        @endif--}}



                    {{--                    @if (Auth::guard('admin')->user()->canAny(['view categories']))--}}
                    {{--                                            @if (Auth::guard('admin')->user()->can('view categories'))--}}


                    {{--                                                <li>--}}
                    {{--                                                    <a class="{{ (Request::is(app()->getLocale() . '/admin/categories*') ) ? 'active' : '' }}"--}}
                    {{--                                                       href="{{ route('admin.categories.index') }}">--}}
                    {{--                                                        <i class="fe fe-list-task"></i> <span>{{ TranslationHelper::translate('Categories') }}</span>--}}
                    {{--                                                    </a>--}}
                    {{--                                                </li>--}}


                    {{--                                                @endif--}}


                    {{--                                                </li>--}}
                    {{--                                            @endif--}}




                    {{--                                                @if (Auth::guard('admin')->user()->canAny('view cities'))--}}


                    {{--                                                    <li>--}}
                    {{--                                                        <a class="{{ (Request::is(app()->getLocale() . '/admin/cities*') ) ? 'active' : '' }}"--}}
                    {{--                                                           href="{{ route('admin.cities.index') }}">--}}
                    {{--                                                            <i class="fe fe-list-task"></i> <span>{{ TranslationHelper::translate('Cities') }}</span>--}}
                    {{--                                                        </a>--}}
                    {{--                                                    </li>--}}


                    {{--                                                @endif--}}
                    {{--                                                @if (Auth::guard('admin')->user()->canAny('view colors'))--}}


                    {{--                                                    <li>--}}
                    {{--                                                        <a class="{{ (Request::is(app()->getLocale() . '/admin/colors*') ) ? 'active' : '' }}"--}}
                    {{--                                                           href="{{ route('admin.colors.index') }}">--}}
                    {{--                                                            <i class="fe fe-plus"></i> <span>{{ TranslationHelper::translate('Colors') }}</span>--}}
                    {{--                                                        </a>--}}
                    {{--                                                    </li>--}}


                    {{--                                                @endif--}}
                    <li>
                        <a class="{{ (Request::is(app()->getLocale() . '/admin/ages*') ) ? 'active' : '' }}"
                           href="{{ route('admin.ages.index') }}">
                            <i class="fe fe-plus"></i> <span>{{ TranslationHelper::translate('Ages') }}</span>
                        </a>
                    </li>
                    {{--                                                @if (Auth::guard('admin')->user()->canAny('view animal_pens'))--}}


                    {{--                                                    <li>--}}
                    {{--                                                        <a class="{{ (Request::is(app()->getLocale() . '/admin/animal-pens*') ) ? 'active' : '' }}"--}}
                    {{--                                                           href="{{ route('admin.animal-pens.index') }}">--}}
                    {{--                                                            <i class="fe fe-plus"></i> <span>{{ TranslationHelper::translate('Animal Pens') }}</span>--}}
                    {{--                                                        </a>--}}
                    {{--                                                    </li>--}}


                    {{--                                                @endif--}}


                    @if (Auth::guard('admin')->user()->canAny(['edit page']))
                        <li class="submenu">
                            <a href="#"><i class="fa-solid fa-user-tie"></i> <span>
                                {{ TranslationHelper::translate('Pages') }}</span> <span
                                    class="menu-arrow"></span></a>
                            <ul>
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/pages/1/edit*') ? 'active' : '' }}"
                                       href="{{ route('admin.pages.edit',[1]) }}">
                                        {{ TranslationHelper::translate('About Us') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/pages/2/edit*') ? 'active' : '' }}"
                                       href="{{ route('admin.pages.edit',[2]) }}">
                                        {{ TranslationHelper::translate('Terms') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="{{ Request::is(app()->getLocale() . '/admin/pages/3/edit*') ? 'active' : '' }}"
                                       href="{{ route('admin.pages.edit',[3]) }}">
                                        {{ TranslationHelper::translate('Privacy Policy') }}
                                    </a>
                                </li>

                            </ul>
                        </li>
                    @endif


            </ul>
        </div>
    </div>
</div>
