<div class="profile-sidebar">

    <div class="profile-scroll">

        <div class="profile-close mb-3">
            <span>
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
            </span>
        </div>

        <div class="profile-top">

            <div class="profile-image profile-container mb-4 mx-auto">
                <div class="profile-border"></div>
                <img src="{{ auth()->user()->profile_image_url }}" alt="{{ auth()->user()->name }}"  class="p-image">
            </div>

            <h4 class="profile-name mb-1">{{ auth()->user()->name }}</h4>
            <p class="profile-mail mb-0 text-dark:800 dark:text-dark:200">{{ auth()->user()->email }}</p>

            <div class="profile-choose-avatar mt-4">

            </div>
            
        </div>

        <div class="profile-content pt-4 pb-4 mt-4 mb-4">

            <ul class="list-unstyled mb-0">
              
                <li class="hover:bg-dark:50 dark:hover:bg-dark:600">
                    <a href="{{ route('admin.profile.settings') }}" class="text-dark:800 dark:text-dark:200 fw:500 d-flex align-items-center">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="me-2 icon icon-tabler icons-tabler-outline icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                        Profile Settings
                    </a>
                </li>
              
            </ul>
            
        </div>


    </div>

    <div class="profile-fixed">

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn bg-dark text-white   dark:bg-danger dark:text-white w-100">Logout</button>
        </form>
        
    </div>
    
</div>