'<div class="sidebar" id="sidebar">
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










                            <li>
                                <a class="{{ (Request::is(app()->getLocale() . '/admin/vendors*') ) ? 'active' : '' }}"
                                   href="{{ route('admin.vendors.index') }}">
                                    <i class="fe fe-user"></i> <span>{{ TranslationHelper::translate('Vendor') }}</span>
                                </a>
                            </li>





                            <li>
                                <a class="{{ (Request::is(app()->getLocale() . '/admin/videos*') ) ? 'active' : '' }}"
                                   href="{{ route('admin.videos.index') }}">
                                    <i class="fe fe-user"></i> <span>{{ TranslationHelper::translate('Auctions') }}</span>
                                </a>
                            </li>






                                                {{-- <li>
                                                    <a class="{{ (Request::is(app()->getLocale() . '/admin/categories*') ) ? 'active' : '' }}"
                                                       href="{{ route('admin.categories.index') }}">
                                                        <i class="fe fe-list-task"></i> <span>{{ TranslationHelper::translate('Categories') }}</span>
                                                    </a>
                                                </li> --}}


                                                                            <li>
                                                                                <a class="{{ (Request::is(app()->getLocale() . '/admin/cities*') ) ? 'active' : '' }}"
                                                                                   href="{{ route('admin.cities.index') }}">
                                                                                    <i class="fe fe-list-task"></i> <span>{{ TranslationHelper::translate('Cities') }}</span>
                                                                                </a>
                                                                            </li>










                                                    {{-- <li>
                                                        <a class="{{ (Request::is(app()->getLocale() . '/admin/colors*') ) ? 'active' : '' }}"
                                                           href="{{ route('admin.colors.index') }}">
                                                            <i class="fe fe-plus"></i> <span>{{ TranslationHelper::translate('Colors') }}</span>
                                                        </a>
                                                    </li> --}}




                                                    <li>
                                                        <a class="{{ (Request::is(app()->getLocale() . '/admin/ages*') ) ? 'active' : '' }}"
                                                           href="{{ route('admin.ages.index') }}">
                                                            <i class="fe fe-plus"></i> <span>{{ TranslationHelper::translate('Ages') }}</span>
                                                        </a>
                                                    </li>




                                                    <li>
                                                        <a class="{{ (Request::is(app()->getLocale() . '/admin/animal-pens*') ) ? 'active' : '' }}"
                                                           href="{{ route('admin.animal-pens.index') }}">
                                                            <i class="fe fe-plus"></i> <span>{{ TranslationHelper::translate('Animal Pens') }}</span>
                                                        </a>
                                                    </li>








            </ul>
        </div>
    </div>
</div>
