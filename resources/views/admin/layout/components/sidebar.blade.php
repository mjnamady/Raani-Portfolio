<div class="sidebar-wrapper sidebar-theme">

    <nav id="sidebar">

        <div class="navbar-nav theme-brand flex-row  text-center">
            <div class="nav-logo">
                <div class="nav-item theme-logo">
                    <a href="index.html">
                        <img src="https://designreset.com/cork/html/src/assets/img/logo2.svg" class="navbar-logo navbar-logo-light" alt="logo-light">
                        <img src="https://designreset.com/cork/html/src/assets/img/logo.svg" class="navbar-logo navbar-logo-dark" alt="logo-dark">
                    </a>
                </div>
                <div class="nav-item theme-text">
                    <a href="https://designreset.com/cork-admin/" class="nav-link"> Namadi </a>
                </div>
            </div>
            <div class="nav-item sidebar-toggle">
                <div class="btn-toggle sidebarCollapse">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-chevrons-left"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11 7l-5 5l5 5" /><path d="M17 7l-5 5l5 5" /></svg>
                </div>
            </div>
        </div>
        
        <div class="shadow-bottom"></div>

        <ul class="list-unstyled menu-categories" id="accordionExample">
            <li class="menu {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-dashboard" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 4h6v8h-6z"></path>
                            <path d="M4 16h6v4h-6z"></path>
                            <path d="M14 12h6v8h-6z"></path>
                            <path d="M14 4h6v4h-6z"></path>
                        </svg>
                        <span>Dashboard</span>
                    </div>
                </a>
            </li>

            <li class="menu">
                <a href="{{ route('admin.banner') }}" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-home" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <polyline points="5 12 3 12 12 3 21 12 19 12"></polyline>
                            <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"></path>
                            <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"></path>
                        </svg>
                        <span>Banner Section</span>
                    </div>

                </a>

            </li>

            <li class="menu {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <a href="{{ route('admin.settings.index') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-settings" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"></path>
                            <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                        </svg>
                        <span>Site Settings</span>
                    </div>
                </a>
            </li>

            <li class="menu {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
                <a href="{{ route('admin.messages.index') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-inbox" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"></path>
                            <path d="M4 13h3l3 3h4l3 -3h3"></path>
                        </svg>
                        <span>Messages</span>
                        @if ($unreadMessagesCount)
                            <span class="badge badge-danger sidebar-count" title="{{ $unreadMessagesCount }} unread {{ Str::plural('message', $unreadMessagesCount) }}">{{ $unreadMessagesCount }}</span>
                        @endif
                    </div>
                </a>
            </li>

            <li class="menu menu-heading">
                <div class="heading"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minus"><line x1="5" y1="12" x2="19" y2="12"></line></svg><span>APPLICATIONS</span></div>
            </li>

        
            <li class="menu">
                <a href="{{ route('admin.services') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-kanban" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <line x1="4" y1="4" x2="10" y2="4"></line>
                            <line x1="14" y1="4" x2="20" y2="4"></line>
                            <rect x="4" y="8" width="6" height="12" rx="2"></rect>
                            <rect x="14" y="8" width="6" height="6" rx="2"></rect>
                        </svg>
                        <span>Services Area</span>
                    </div>
                </a>
            </li>

            <li class="menu">
                <a href="{{ route('admin.counter') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chart-bar" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <rect x="3" y="12" width="6" height="8" rx="1"></rect>
                            <rect x="9" y="8" width="6" height="12" rx="1"></rect>
                            <rect x="15" y="4" width="6" height="16" rx="1"></rect>
                            <line x1="4" y1="20" x2="18" y2="20"></line>
                        </svg>
                        <span>Counter Area</span>
                    </div>
                </a>
            </li>

            <li class="menu">
                <a href="{{ route('admin.latest-services') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-list-numbers" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M11 6h9"></path>
                            <path d="M11 12h9"></path>
                            <path d="M12 18h8"></path>
                            <path d="M4 16a2 2 0 1 1 4 0c0 .591 -.5 1 -1 1.5l-3 2.5h4"></path>
                            <path d="M6 10v-6l-2 2"></path>
                        </svg>
                        <span>Latest Service Area</span>
                    </div>
                </a>
            </li>

         
            @php
                $resumeOpen = request()->routeIs('admin.education.*', 'admin.experience.*');
            @endphp
            <li class="menu {{ $resumeOpen ? 'active' : '' }}">
                <a href="#resume-menu" data-bs-toggle="collapse" aria-expanded="{{ $resumeOpen ? 'true' : 'false' }}" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-school" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M22 9l-10 -4l-10 4l10 4l10 -4v6"></path>
                            <path d="M6 10.6v5.4a6 3 0 0 0 12 0v-5.4"></path>
                        </svg>
                        <span>Education/Experience</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ $resumeOpen ? 'show' : '' }}" id="resume-menu" data-bs-parent="#accordionExample">
                    <li class="{{ request()->routeIs('admin.education.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.education.index') }}"> Education </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.experience.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.experience.index') }}"> Experience </a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                <a href="{{ route('admin.clients.index') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-building-skyscraper" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M3 21l18 0"></path>
                            <path d="M5 21v-14l8 -4v18"></path>
                            <path d="M19 21v-10l-6 -4"></path>
                            <path d="M9 9l0 .01"></path>
                            <path d="M9 12l0 .01"></path>
                            <path d="M9 15l0 .01"></path>
                            <path d="M9 18l0 .01"></path>
                        </svg>
                        <span>Supported Companies</span>
                    </div>
                </a>
            </li>

            @php
                $portfolioOpen = request()->routeIs('admin.projects.*', 'admin.project-categories.*');
            @endphp
            <li class="menu {{ $portfolioOpen ? 'active' : '' }}">
                <a href="#portfolio-menu" data-bs-toggle="collapse" aria-expanded="{{ $portfolioOpen ? 'true' : 'false' }}" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-briefcase" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M3 9a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9z"></path>
                            <path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2"></path>
                            <path d="M12 12l0 .01"></path>
                            <path d="M3 13a20 20 0 0 0 18 0"></path>
                        </svg>
                        <span>Portfolio</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ $portfolioOpen ? 'show' : '' }}" id="portfolio-menu" data-bs-parent="#accordionExample">
                    <li class="{{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.projects.index') }}"> Projects </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.project-categories.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.project-categories.index') }}"> Categories </a>
                    </li>
                </ul>
            </li>

            <li class="menu {{ request()->routeIs('admin.skills.*') ? 'active' : '' }}">
                <a href="{{ route('admin.skills.index') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-bolt" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0l8 -11"></path>
                        </svg>
                        <span>My Skill</span>
                    </div>
                </a>
            </li>

            <li class="menu {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">
                <a href="{{ route('admin.testimonials.index') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-message-2-heart" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M8 9h8"></path>
                            <path d="M8 13h5"></path>
                            <path d="M12 21l-3 -3h-3a3 3 0 0 1 -3 -3v-8a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-3l-3 3z"></path>
                        </svg>
                        <span>Testimonials</span>
                    </div>
                </a>
            </li>

            <li class="menu {{ request()->routeIs('admin.about.*') ? 'active' : '' }}">
                <a href="{{ route('admin.about.index') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user-circle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path>
                            <path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path>
                            <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855"></path>
                        </svg>
                        <span>About Page</span>
                    </div>
                </a>
            </li>

            <li class="menu {{ request()->routeIs('admin.contact.*') ? 'active' : '' }}">
                <a href="{{ route('admin.contact.index') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-address-book" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M20 6v12a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2z"></path>
                            <path d="M10 16h6"></path>
                            <path d="M13 11m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                            <path d="M4 8h3"></path>
                            <path d="M4 12h3"></path>
                            <path d="M4 16h3"></path>
                        </svg>
                        <span>Contact Page</span>
                    </div>
                </a>
            </li>

            @php
                $blogOpen = request()->routeIs('admin.posts.*', 'admin.post-categories.*', 'admin.comments.*');
            @endphp
            <li class="menu {{ $blogOpen ? 'active' : '' }}">
                <a href="#blog-menu" data-bs-toggle="collapse" aria-expanded="{{ $blogOpen ? 'true' : 'false' }}" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-news" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M16 6h3a1 1 0 0 1 1 1v11a2 2 0 0 1 -4 0v-13a1 1 0 0 0 -1 -1h-10a1 1 0 0 0 -1 1v12a3 3 0 0 0 3 3h11"></path>
                            <path d="M8 8l4 0"></path>
                            <path d="M8 12l4 0"></path>
                            <path d="M8 16l4 0"></path>
                        </svg>
                        <span>Blog</span>
                        @if ($pendingCommentsCount)
                            <span class="badge badge-danger sidebar-count" title="{{ $pendingCommentsCount }} pending {{ Str::plural('comment', $pendingCommentsCount) }}">{{ $pendingCommentsCount }}</span>
                        @endif
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled {{ $blogOpen ? 'show' : '' }}" id="blog-menu" data-bs-parent="#accordionExample">
                    <li class="{{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.posts.index') }}"> Posts </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.post-categories.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.post-categories.index') }}"> Categories </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.comments.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.comments.index') }}"> Comments
                            @if ($pendingCommentsCount)
                                <span class="badge badge-danger sidebar-count" aria-label="{{ $pendingCommentsCount }} pending">{{ $pendingCommentsCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

            
            
        </ul>
        
    </nav>

</div>