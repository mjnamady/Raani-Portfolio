@extends('admin.layout.admin_dashboard_temp')
@section('content')


<div class="middle-content container-xxl p-0">

    <!--  BEGIN BREADCRUMBS  -->
    <div class="secondary-nav">
        <div class="breadcrumbs-container" data-page-heading="Analytics">
            <header class="header navbar navbar-expand-sm">
                <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </a>
                <div class="d-flex breadcrumb-content">
                    <div class="page-header">

                        <div class="page-title">
                        </div>
        
                        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Users</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Profile</li>
                            </ol>
                        </nav>
        
                    </div>
                </div>
                <ul class="navbar-nav flex-row ms-auto breadcrumb-action-dropdown">
                    <li class="nav-item more-dropdown">
                        <div class="dropdown  custom-dropdown-icon">
                            <a class="dropdown-toggle btn" href="#" role="button" id="customDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span>Print</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down custom-dropdown-arrow"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </a>
        
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="customDropdown">

                                <a class="dropdown-item" data-value="Print" data-icon="<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;24&quot; height=&quot;24&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; stroke-width=&quot;2&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; class=&quot;feather feather-printer&quot;><polyline points=&quot;6 9 6 2 18 2 18 9&quot;></polyline><path d=&quot;M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2&quot;></path><rect x=&quot;6&quot; y=&quot;14&quot; width=&quot;12&quot; height=&quot;8&quot;></rect></svg>" href="javascript:void(0);">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-printer"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg> Print
                                </a>

                                <a class="dropdown-item" data-value="Download" data-icon="<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;24&quot; height=&quot;24&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; stroke-width=&quot;2&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; class=&quot;feather feather-download&quot;><path d=&quot;M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4&quot;></path><polyline points=&quot;7 10 12 15 17 10&quot;></polyline><line x1=&quot;12&quot; y1=&quot;15&quot; x2=&quot;12&quot; y2=&quot;3&quot;></line></svg>" href="javascript:void(0);">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-download"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg> Download
                                </a>

                                <a class="dropdown-item" data-value="Share" data-icon="<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;24&quot; height=&quot;24&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; stroke-width=&quot;2&quot; stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; class=&quot;feather feather-share-2&quot;><circle cx=&quot;18&quot; cy=&quot;5&quot; r=&quot;3&quot;></circle><circle cx=&quot;6&quot; cy=&quot;12&quot; r=&quot;3&quot;></circle><circle cx=&quot;18&quot; cy=&quot;19&quot; r=&quot;3&quot;></circle><line x1=&quot;8.59&quot; y1=&quot;13.51&quot; x2=&quot;15.42&quot; y2=&quot;17.49&quot;></line><line x1=&quot;15.41&quot; y1=&quot;6.51&quot; x2=&quot;8.59&quot; y2=&quot;10.49&quot;></line></svg>" href="javascript:void(0);">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-share"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M8.7 10.7l6.6 -3.4" /><path d="M8.7 13.3l6.6 3.4" /></svg> Share
                                </a>
                                
                            </div>
        
                        </div>
                    </li>
                </ul>
            </header>
        </div>
    </div>
    <!--  END BREADCRUMBS  -->

    <div class="row layout-top-spacing">                        
        <div class="col-xl-12 col-lg-12 col-md-12">

            <div class="profile-header mb-5">

                <div class="profile-backdrop-content"></div>

                <div class="profile-container">
                    <div class="profile">
                        <img alt="avatar" src="{{ asset('backend/src/assets/img/profile/profile-11.png') }}" class="rounded-circle">
                        <div class="media-body">
                            <h4>Shaun Park</h4>
                            <p>CTO</p>
                        </div>
                    </div>
                </div>

                <div class="profile-info">


                    <div class="row justify-content-between">

                        <div class="col-xl-5 col-lg-12 col-md-12 col-12"></div>

                        <div class="col-xl-7 col-lg-12 col-md-12 col-12">

                            <div class="profile-menu">

                                <ul class="nav nav-tabs justify-content-md-end justify-content-center" id="nav-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="nav-home-tab" data-bs-toggle="pill" data-bs-target="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2a5 5 0 1 1 -5 5l.005 -.217a5 5 0 0 1 4.995 -4.783z" /><path d="M14 14a5 5 0 0 1 5 5v1a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-1a5 5 0 0 1 5 -5h4z" /></svg>
                                            <span>Profile</span>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="nav-profile-tab" data-bs-toggle="pill" data-bs-target="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-heart"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6.979 3.074a6 6 0 0 1 4.988 1.425l.037 .033l.034 -.03a6 6 0 0 1 4.733 -1.44l.246 .036a6 6 0 0 1 3.364 10.008l-.18 .185l-.048 .041l-7.45 7.379a1 1 0 0 1 -1.313 .082l-.094 -.082l-7.493 -7.422a6 6 0 0 1 3.176 -10.215z" /></svg>
                                            <span>Followers</span>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="nav-contact-tab" data-bs-toggle="pill" data-bs-target="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-users"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                            <span>Friends</span>
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="nav-disabled-tab" data-bs-toggle="pill" data-bs-target="#nav-disabled" role="tab" aria-controls="nav-disabled" aria-selected="false">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-photo"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.813 11.612c.457 -.38 .918 -.38 1.386 .011l.108 .098l4.986 4.986l.094 .083a1 1 0 0 0 1.403 -1.403l-.083 -.094l-1.292 -1.293l.292 -.293l.106 -.095c.457 -.38 .918 -.38 1.386 .011l.108 .098l4.674 4.675a4 4 0 0 1 -3.775 3.599l-.206 .005h-12a4 4 0 0 1 -3.98 -3.603l6.687 -6.69l.106 -.095zm9.187 -9.612a4 4 0 0 1 3.995 3.8l.005 .2v9.585l-3.293 -3.292l-.15 -.137c-1.256 -1.095 -2.85 -1.097 -4.096 -.017l-.154 .14l-.307 .306l-2.293 -2.292l-.15 -.137c-1.256 -1.095 -2.85 -1.097 -4.096 -.017l-.154 .14l-5.307 5.306v-9.585a4 4 0 0 1 3.8 -3.995l.2 -.005h12zm-2.99 5l-.127 .007a1 1 0 0 0 0 1.986l.117 .007l.127 -.007a1 1 0 0 0 0 -1.986l-.117 -.007z" /></svg>
                                            <span>Gallery</span>
                                        </a>
                                    </li>
                                </ul>
                                
                            </div>

                        </div>                                        

                    </div>
                    
                    
                    
                    
                </div>

            </div>
            
        </div>

        <div class="col-xl-12 col-lg-12 col-md-12">

            <div class="profile-content-area">

                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                        <div class="row">
                    
                            <div class="col-xxl-3 col-xl-4 col-lg-12 col-md-12 col-sm-12 col-12 mb-4">
    
                                <div class="w-stats">

                                    <div class="w-divider"></div>
                                    
                                    <div class="row">
    
                                        <div class="col-xl-6 col-6">

                                            <div class="follower">
                                                <h4>2,226</h4>
                                                <p>Followers</p>
                                            </div>
                                            
                                        </div>
    
                                        <div class="col-xl-6 col-6">

                                            <div class="following">
                                                <h4>8,213</h4>
                                                <p>Following</p>
                                            </div>
                                            
                                        </div>
                                        
                                    </div>
                                    
                                </div>
    
                                <div class="w-about">
    
                                    <h5>About</h5>
    
                                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est dolor eaque, vitae in veniam dolorem qui et.</p>
    
                                    <div class="about-list-item mt-3">
                                                
                                        <ul class="list-group">
    
                                            <li class="list-group-item">
                                                <p>
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-map-pin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18.364 4.636a9 9 0 0 1 .203 12.519l-.203 .21l-4.243 4.242a3 3 0 0 1 -4.097 .135l-.144 -.135l-4.244 -4.243a9 9 0 0 1 12.728 -12.728zm-6.364 3.364a3 3 0 1 0 0 6a3 3 0 0 0 0 -6z" /></svg>
                                                    Lives in <span>USA</span>
                                                </p>
                                            </li>
    
                                            <li class="list-group-item">
                                                <p>
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-mail"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 7.535v9.465a3 3 0 0 1 -2.824 2.995l-.176 .005h-14a3 3 0 0 1 -2.995 -2.824l-.005 -.176v-9.465l9.445 6.297l.116 .066a1 1 0 0 0 .878 0l.116 -.066l9.445 -6.297z" /><path d="M19 4c1.08 0 2.027 .57 2.555 1.427l-9.555 6.37l-9.555 -6.37a2.999 2.999 0 0 1 2.354 -1.42l.201 -.007h14z" /></svg>
                                                    shaun@mail.com
                                                </p>
                                            </li>
    
                                            <li class="list-group-item">
                                                <p>
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-briefcase"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M22 13.478v4.522a3 3 0 0 1 -3 3h-14a3 3 0 0 1 -3 -3v-4.522l.553 .277a20.999 20.999 0 0 0 18.897 -.002l.55 -.275zm-8 -11.478a3 3 0 0 1 3 3v1h2a3 3 0 0 1 3 3v2.242l-1.447 .724a19.002 19.002 0 0 1 -16.726 .186l-.647 -.32l-1.18 -.59v-2.242a3 3 0 0 1 3 -3h2v-1a3 3 0 0 1 3 -3h4zm-2 8a1 1 0 0 0 -1 1a1 1 0 1 0 2 .01c0 -.562 -.448 -1.01 -1 -1.01zm2 -6h-4a1 1 0 0 0 -1 1v1h6v-1a1 1 0 0 0 -1 -1z" /></svg>
                                                    CEO at <span>Lindgren Inc</span>
                                                </p>
                                            </li>
    
                                            <li class="list-group-item">
                                                <p>
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-archive"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M2 3m0 2a2 2 0 0 1 2 -2h16a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-16a2 2 0 0 1 -2 -2z" /><path d="M19 9c.513 0 .936 .463 .993 1.06l.007 .14v7.2c0 1.917 -1.249 3.484 -2.824 3.594l-.176 .006h-10c-1.598 0 -2.904 -1.499 -2.995 -3.388l-.005 -.212v-7.2c0 -.663 .448 -1.2 1 -1.2h14zm-5 2h-4l-.117 .007a1 1 0 0 0 0 1.986l.117 .007h4l.117 -.007a1 1 0 0 0 0 -1.986l-.117 -.007z" /></svg>
                                                    Studied at <span>University of Wyoming</span>
                                                </p>
                                            </li>
                                            
                                        </ul>
                                        
                                    </div>
                                    
                                </div>

                                <div class="w-social">
    
                                    <h5>Social</h5>
    
                                    <div class="about-list-item">
                                                
                                        <ul class="list-group">
    
                                            <li class="list-group-item social-facebook">
                                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-meta"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 10.174c1.766 -2.784 3.315 -4.174 4.648 -4.174c2 0 3.263 2.213 4 5.217c.704 2.869 .5 6.783 -2 6.783c-1.114 0 -2.648 -1.565 -4.148 -3.652a27.627 27.627 0 0 1 -2.5 -4.174z" /><path d="M12 10.174c-1.766 -2.784 -3.315 -4.174 -4.648 -4.174c-2 0 -3.263 2.213 -4 5.217c-.704 2.869 -.5 6.783 2 6.783c1.114 0 2.648 -1.565 4.148 -3.652c1 -1.391 1.833 -2.783 2.5 -4.174z" /></svg>
                                                <a href="javascript:void(0);">facebook.com/<span>shaunpark</span></a>
                                            </li>
    
                                            <li class="list-group-item social-instagram">
                                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" /><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M16.5 7.5l0 .01" /></svg>
                                                <a href="javascript:void(0);">@shaunpark</a>
                                            </li>
    
                                            <li class="list-group-item social-linkedin">
                                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-linkedin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 11l0 5" /><path d="M8 8l0 .01" /><path d="M12 16l0 -5" /><path d="M16 16v-3a2 2 0 0 0 -4 0" /></svg>
                                                <a href="javascript:void(0);">Shaun Park</a>
                                            </li>
    
                                            <li class="list-group-item social-xtweeter">
                                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4l11.733 16h4.267l-11.733 -16z" /><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" /></svg>
                                                <a href="javascript:void(0);">@shaun_park</a>
                                            </li>

                                            <li class="list-group-item social-github">
                                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-github"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 5v-3.5c0 -1 .1 -1.4 -.5 -2c2.8 -.3 5.5 -1.4 5.5 -6a4.6 4.6 0 0 0 -1.3 -3.2a4.2 4.2 0 0 0 -.1 -3.2s-1.1 -.3 -3.5 1.3a12.3 12.3 0 0 0 -6.2 0c-2.4 -1.6 -3.5 -1.3 -3.5 -1.3a4.2 4.2 0 0 0 -.1 3.2a4.6 4.6 0 0 0 -1.3 3.2c0 4.6 2.7 5.7 5.5 6c-.6 .6 -.6 1.2 -.5 2v3.5" /></svg>
                                                <a href="javascript:void(0);">ParkShaun</a>
                                            </li>
                                            
                                        </ul>
                                        
                                    </div>
                                    
                                </div>                                            
    
                            </div>
    
                            <div class="col-xxl-9 col-xl-8 col-lg-12 col-md-12 col-sm-12 col-12 mb-4">
    
                                <div class="post-input">
    
                                    <div class="form-group mb-4">
                                        <textarea class="form-control" id="exampleFormControlTextarea1" rows="5" placeholder="Share what you are thinking here..."></textarea>
                                    </div>
    
                                    <div class="justify-content-between align-items-center">

                                        <div class="row">
                                            
                                            <div class="col-md-8 col-sm-8 col-12">
                                                <div class="post-action">
                                                    <a href="javascript:void(0);">
                                                        <span class="badge badge-light-primary me-2 mb-1">
                                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-photo"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                                                            Image/Video
                                                        </span>
                                                    </a>

                                                    <a href="javascript:void(0);">
                                                        <span class="badge badge-light-danger mb-1">
                                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-video"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z" /><path d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /></svg>
                                                            Streaming
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="col-md-4 col-sm-4 col-12">
                                                <div class="post-action-button text-end">
                                                    <button class="btn btn-gradient-primary">Post</button>
                                                </div>
                                            </div>
                                            
                                        </div>

                                    </div>
    
                                </div>
                    
                                <div class="posted-posts">
    
                                    <div class="post">
    
                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-4.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">Shaun Park</h4>
                                                <p class="media-text">29 July 2026</p>
                                            </div>
                                        </div>
    
                                        <p class="post-description">There's something magical about watching the sun dip below the horizon, painting the sky in hues of pink, orange, and gold. Every sunset feels like a unique masterpiece, and today’s was no exception. Grateful for the opportunity to witness nature’s beauty while traveling. Here’s to more sunsets, more memories, and endless adventures. ✨</p>

                                        <p class="post-hashtags">
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-primary">#SunsetLover</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-success">#TravelDiaries</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-danger">#GoldenHour</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-secondary">#NatureAtItsBest</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-info">#Wanderlust</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-warning">#GoldenHour</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-dark">#NatureAtItsBest</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-danger">#Wanderlust</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-success">#TravelGram</a>
                                        </p>
    
                                        <img src="../../../../images.pexels.com/photos/462023/pexels-photo-462023.jpg" alt="image-1" class="full-width img-fluid mt-4 mb-5">
    
                                        <div class="d-flex justify-content-between align-items-center mb-4">

                                            <div class="post_users-actions d-flex align-items-center">

                                                <div class="post-like d-flex me-4">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-heart"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /></svg>
                                                    <span>20</span>
                                                </div>

                                                <div class="avatar--group">
                                                    <div class="avatar avatar-sm translateX-axis">
                                                        <img alt="avatar" src="../src/assets/img/profile/profile-26.png" class="rounded-circle" />
                                                    </div>
                                                    <div class="avatar avatar-sm translateX-axis">
                                                        <img alt="avatar" src="../src/assets/img/profile/profile-36.png" class="rounded-circle" />
                                                    </div>
                                                    <div class="avatar avatar-sm translateX-axis">
                                                        <img alt="avatar" src="../src/assets/img/profile/profile-24.png" class="rounded-circle" />
                                                    </div>
                                                    <div class="avatar avatar-sm translateX-axis">
                                                        <span class="avatar-title rounded-circle bg-secondary:200 dark:bg-secondary:500 text-secondary:600 dark:text-secondary:100">AG</span>
                                                    </div>
                                                </div>
                                            </div>
                                                                
                                            <div class="d-flex">
                                                
                                                <div class="me-2">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-hipchat">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M17.802 17.292s.077 -.055 .2 -.149c1.843 -1.425 3 -3.49 3 -5.789c0 -4.286 -4.03 -7.764 -9 -7.764c-4.97 0 -9 3.478 -9 7.764c0 4.288 4.03 7.646 9 7.646c.424 0 1.12 -.028 2.088 -.084c1.262 .82 3.104 1.493 4.716 1.493c.499 0 .734 -.41 .414 -.828c-.486 -.596 -1.156 -1.551 -1.416 -2.29z" />
                                                        <path d="M7.5 13.5c2.5 2.5 6.5 2.5 9 0" />
                                                    </svg>
                                                </div>
    
                                                <div>
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-share"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M8.7 10.7l6.6 -3.4" /><path d="M8.7 13.3l6.6 3.4" /></svg>
                                                </div>
                                                
                                            </div>
                                            
                                        </div>
    
                                        <div class="comments">

                                            <ul class="list-unstyled">
                                                <li class="media">
                                                    <img src="../src/assets/img/profile/profile-18.png" alt="pic1">
                                                    <div class="media-body">
                                                        <h4 class="media-heading">Laurie Fox</h4>
                                                        <p class="media-text">Wow, that sunset looks absolutely stunning! Makes me want to pack my bags and head there right now.</p>
                                                    </div>
                                                </li>
                                                <li class="media">
                                                    <img src="../src/assets/img/profile/profile-26.png" alt="pic1">
                                                    <div class="media-body">
                                                        <h4 class="media-heading">James Taylor</h4>
                                                        <p class="media-text">This place looks like a dream! The colors are just so vibrant and beautiful.</p>
                                                    </div>
                                                </li>
                                                <li class="media">
                                                    <img src="../src/assets/img/profile/profile-29.png" alt="pic1">
                                                    <div class="media-body">
                                                        <h4 class="media-heading">Donna Rogers</h4>
                                                        <p class="media-text">The way you captured the scenery is breathtaking. I can almost feel the fresh air through the screen!</p>
                                                    </div>
                                                </li>
                                            </ul>                    
    
                                            <div class="post-comment-form">
                                                <div class="media justify-content-between align-items-center">
                                                    <img src="../src/assets/img/profile/profile-11.png" alt="pic1">
                                                    <div class="media-body">
                                                        <div class="post-comment-input">
                                                            <input type="text" class="form-control" placeholder="Write a comment...">
                                                            <div class="post-comment-input-action">

                                                                <div class="me-2">
                                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-photo-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l4 4" /><path d="M14 14l1 -1c.67 -.644 1.45 -.824 2.182 -.54" /><path d="M16 19h6" /><path d="M19 16v6" /></svg>
                                                                </div>

                                                                <div class="">
                                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-mood-smile"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 10l.01 0" /><path d="M15 10l.01 0" /><path d="M9.5 15a3.5 3.5 0 0 0 5 0" /></svg>
                                                                </div>
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>                    
                                        
                                    </div>

    
                                    <div class="post">
    
                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-4.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">Shaun Park</h4>
                                                <p class="media-text">29 July 2026</p>
                                            </div>
                                        </div>
    
                                        <p class="post-description">Stumbled upon this breathtaking lake with a cascading waterfall during my travels, and it felt like stepping into a postcard. The sound of the water, the fresh mountain air, and the serene surroundings made this spot pure magic. Grateful for moments like these that remind me of the beauty and power of nature. 🌿</p>
                                        <p class="post-hashtags">
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-primary">#WaterfallWonder</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-success">#LakesideBliss</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-danger">#NatureLover</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-secondary">#TravelAdventures</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-info">#HiddenGems</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-warning">#Wanderlust</a>
                                            <a href="javascript:void(0);" class="mb-1 badge badge-light-dark">#TravelGram</a>
                                        </p>
    
                                        <img src="../../../../images.pexels.com/photos/949194/pexels-photo-949194.jpg" alt="image-1" class="full-width img-fluid mt-4 mb-5">
    
                                        <div class="d-flex justify-content-between align-items-center mb-4">

                                            <div class="post_users-actions d-flex align-items-center">

                                                <div class="post-like d-flex me-4">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-heart"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" /></svg>
                                                    <span>20</span>
                                                </div>

                                                <div class="avatar--group">
                                                    <div class="avatar avatar-sm translateX-axis">
                                                        <img alt="avatar" src="../src/assets/img/profile/profile-26.png" class="rounded-circle" />
                                                    </div>
                                                    <div class="avatar avatar-sm translateX-axis">
                                                        <img alt="avatar" src="../src/assets/img/profile/profile-36.png" class="rounded-circle" />
                                                    </div>
                                                    <div class="avatar avatar-sm translateX-axis">
                                                        <img alt="avatar" src="../src/assets/img/profile/profile-24.png" class="rounded-circle" />
                                                    </div>
                                                    <div class="avatar avatar-sm translateX-axis">
                                                        <span class="avatar-title rounded-circle bg-secondary:200 dark:bg-secondary:500 text-secondary:600 dark:text-secondary:100">AG</span>
                                                    </div>
                                                </div>
                                            </div>
                                                                
                                            <div class="d-flex">
                                                
                                                <div class="me-2">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-hipchat">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M17.802 17.292s.077 -.055 .2 -.149c1.843 -1.425 3 -3.49 3 -5.789c0 -4.286 -4.03 -7.764 -9 -7.764c-4.97 0 -9 3.478 -9 7.764c0 4.288 4.03 7.646 9 7.646c.424 0 1.12 -.028 2.088 -.084c1.262 .82 3.104 1.493 4.716 1.493c.499 0 .734 -.41 .414 -.828c-.486 -.596 -1.156 -1.551 -1.416 -2.29z" />
                                                        <path d="M7.5 13.5c2.5 2.5 6.5 2.5 9 0" />
                                                    </svg>
                                                </div>
    
                                                <div>
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-share"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M18 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M8.7 10.7l6.6 -3.4" /><path d="M8.7 13.3l6.6 3.4" /></svg>
                                                </div>
                                                
                                            </div>
                                            
                                        </div>
    
                                        <div class="comments">
    
                                            
                                            <ul class="list-unstyled">
                                                <li class="media">
                                                    <img src="../src/assets/img/profile/profile-18.png" alt="pic1">
                                                    <div class="media-body">
                                                        <h4 class="media-heading">Carol Mack</h4>
                                                        <p class="media-text">I've always wanted to visit this place! Any tips for a first-time traveler?</p>
                                                    </div>
                                                </li>
                                                <li class="media">
                                                    <img src="../src/assets/img/profile/profile-26.png" alt="pic1">
                                                    <div class="media-body">
                                                        <h4 class="media-heading">Andy King</h4>
                                                        <p class="media-text">This view is absolutely stunning. I can't believe places like this exist!</p>
                                                    </div>
                                                </li>
                                                <li class="media">
                                                    <img src="../src/assets/img/profile/profile-29.png" alt="pic1">
                                                    <div class="media-body">
                                                        <h4 class="media-heading">Vincent Carpenter</h4>
                                                        <p class="media-text">Such a picturesque location! How was the local food there?</p>
                                                    </div>
                                                </li>
                                            </ul>
    
    
                                            <div class="post-comment-form">
                                                <div class="media justify-content-between align-items-center">
                                                    <img src="../src/assets/img/profile/profile-11.png" alt="pic1">
                                                    <div class="media-body">
                                                        <div class="post-comment-input">
                                                            <input type="text" class="form-control" placeholder="Write a comment...">
                                                            <div class="post-comment-input-action">

                                                                <div class="me-2">
                                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-photo-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M12.5 21h-6.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v6.5" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l4 4" /><path d="M14 14l1 -1c.67 -.644 1.45 -.824 2.182 -.54" /><path d="M16 19h6" /><path d="M19 16v6" /></svg>
                                                                </div>

                                                                <div class="">
                                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-mood-smile"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 10l.01 0" /><path d="M15 10l.01 0" /><path d="M9.5 15a3.5 3.5 0 0 0 5 0" /></svg>
                                                                </div>
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
    
                                        
                                    </div>
                                    
                                </div>
                                
                            </div>

                        </div>
                    </div>

                    <div class="tab-pane fade profile-followers-tab" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                        
                        <div class="profile-follower-title mb-5">
                            <h3>Followers</h3>
                        </div>

                        <div class="row">

                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">

                                <div class="profile-follower mb-3">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-14.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">Devin Cole</h4>
                                                <p class="media-text">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                    </svg>
                                                    <span>Mexico</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="profile-follow-action">
                                            <button class="btn btn-gradient-info btn-sm">Follow</button>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">

                                <div class="profile-follower mb-3">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-7.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">Nina Flores</h4>
                                                <p class="media-text">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                    </svg>
                                                    <span>United States</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="profile-follow-action">
                                            <button class="btn btn-gradient-info btn-sm">Follow</button>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">

                                <div class="profile-follower mb-3">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-9.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">Ian Munoz</h4>
                                                <p class="media-text">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                    </svg>
                                                    <span>Australia</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="profile-follow-action">
                                            <button class="btn btn-gradient-info btn-sm">Follow</button>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">

                                <div class="profile-follower mb-3">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-20.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">Andy King</h4>
                                                <p class="media-text">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                    </svg>
                                                    <span>Egypt</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="profile-follow-action">
                                            <button class="btn btn-gradient-info btn-sm">Follow</button>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">

                                <div class="profile-follower mb-3">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-22.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">Nia Hillyer</h4>
                                                <p class="media-text">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                    </svg>
                                                    <span>Greece</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="profile-follow-action">
                                            <button class="btn btn-gradient-info btn-sm">Follow</button>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">

                                <div class="profile-follower mb-3">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-32.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">James Taylor</h4>
                                                <p class="media-text">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                    </svg>
                                                    <span>Argentina</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="profile-follow-action">
                                            <button class="btn btn-gradient-info btn-sm">Follow</button>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">

                                <div class="profile-follower mb-3">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-34.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">Grace Roberts</h4>
                                                <p class="media-text">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                    </svg>
                                                    <span>Argentina</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="profile-follow-action">
                                            <button class="btn btn-gradient-info btn-sm">Follow</button>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">

                                <div class="profile-follower mb-3">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-33.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">Alexander Gray</h4>
                                                <p class="media-text">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                    </svg>
                                                    <span>Brazil</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="profile-follow-action">
                                            <button class="btn btn-gradient-info btn-sm">Follow</button>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">

                                <div class="profile-follower mb-3">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <div class="media justify-content-between align-items-center">
                                            <img src="../src/assets/img/profile/profile-28.png" alt="pic1">
                                            <div class="media-body">
                                                <h4 class="media-heading">Laurie Fox</h4>
                                                <p class="media-text">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                    </svg>
                                                    <span>Norway</span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="profile-follow-action">
                                            <button class="btn btn-gradient-info btn-sm">Follow</button>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            
                        </div>
                        
                    </div>

                    <div class="tab-pane fade profile-friends-tab" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab" tabindex="0">
                        
                        <div class="profile-friends-title mb-5">
                            <h3>Friends</h3>
                        </div>

                        <div class="row">

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-friends">

                                    <div class="pf-dropdown d-flex justify-content-end">
                                        <div class="dropdown d-inline-block">
                                            <a class="dropdown-toggle" href="#" role="button" id="elementDrodpown3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                            </a>
                                
                                            <div class="dropdown-menu left" aria-labelledby="elementDrodpown3" style="will-change: transform; position: absolute; transform: translate3d(-141px, 19px, 0px); top: 0px; left: 0px;">
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                    <span class="ms-1">Delete</span>
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil-minus">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                        <path d="M13.5 6.5l4 4" />
                                                        <path d="M16 19h6" />
                                                    </svg>
                                                    <span class="ms-1">Edit</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="pf-profile mt-2">
                                        <img src="../src/assets/img/profile/profile-8.png" alt="pic1">
                                        <div class="pf-info">
                                            <h4 class="media-heading">Vincent Carpenter</h4>
                                            <p class="media-text">Australia</p>
                                        </div>
                                    </div>

                                    <div class="social d-flex justify-content-center">
                                        
                                        <div class="pf-social pf-social-facebook me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-meta"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 10.174c1.766 -2.784 3.315 -4.174 4.648 -4.174c2 0 3.263 2.213 4 5.217c.704 2.869 .5 6.783 -2 6.783c-1.114 0 -2.648 -1.565 -4.148 -3.652a27.627 27.627 0 0 1 -2.5 -4.174z" /><path d="M12 10.174c-1.766 -2.784 -3.315 -4.174 -4.648 -4.174c-2 0 -3.263 2.213 -4 5.217c-.704 2.869 -.5 6.783 2 6.783c1.114 0 2.648 -1.565 4.148 -3.652c1 -1.391 1.833 -2.783 2.5 -4.174z" /></svg>
                                        </div>
                                        
                                        <div class="pf-social pf-social-instagram me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" /><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M16.5 7.5l0 .01" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-linkedin me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-linkedin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 11l0 5" /><path d="M8 8l0 .01" /><path d="M12 16l0 -5" /><path d="M16 16v-3a2 2 0 0 0 -4 0" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-xtweeter">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4l11.733 16h4.267l-11.733 -16z" /><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" /></svg>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-friends">

                                    <div class="pf-dropdown d-flex justify-content-end">
                                        <div class="dropdown d-inline-block">
                                            <a class="dropdown-toggle" href="#" role="button" id="elementDrodpown3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                            </a>
                                
                                            <div class="dropdown-menu left" aria-labelledby="elementDrodpown3" style="will-change: transform; position: absolute; transform: translate3d(-141px, 19px, 0px); top: 0px; left: 0px;">
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                    <span class="ms-1">Delete</span>
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil-minus">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                        <path d="M13.5 6.5l4 4" />
                                                        <path d="M16 19h6" />
                                                    </svg>
                                                    <span class="ms-1">Edit</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="pf-profile mt-2">
                                        <img src="../src/assets/img/profile/profile-24.png" alt="pic1">
                                        <div class="pf-info">
                                            <h4 class="media-heading">James Taylor</h4>
                                            <p class="media-text">Mexico</p>
                                        </div>
                                    </div>

                                    <div class="social d-flex justify-content-center">
                                        
                                        <div class="pf-social pf-social-facebook me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-meta"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 10.174c1.766 -2.784 3.315 -4.174 4.648 -4.174c2 0 3.263 2.213 4 5.217c.704 2.869 .5 6.783 -2 6.783c-1.114 0 -2.648 -1.565 -4.148 -3.652a27.627 27.627 0 0 1 -2.5 -4.174z" /><path d="M12 10.174c-1.766 -2.784 -3.315 -4.174 -4.648 -4.174c-2 0 -3.263 2.213 -4 5.217c-.704 2.869 -.5 6.783 2 6.783c1.114 0 2.648 -1.565 4.148 -3.652c1 -1.391 1.833 -2.783 2.5 -4.174z" /></svg>
                                        </div>
                                        
                                        <div class="pf-social pf-social-instagram me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" /><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M16.5 7.5l0 .01" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-linkedin me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-linkedin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 11l0 5" /><path d="M8 8l0 .01" /><path d="M12 16l0 -5" /><path d="M16 16v-3a2 2 0 0 0 -4 0" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-xtweeter">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4l11.733 16h4.267l-11.733 -16z" /><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" /></svg>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-friends">

                                    <div class="pf-dropdown d-flex justify-content-end">
                                        <div class="dropdown d-inline-block">
                                            <a class="dropdown-toggle" href="#" role="button" id="elementDrodpown3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                            </a>
                                
                                            <div class="dropdown-menu left" aria-labelledby="elementDrodpown3" style="will-change: transform; position: absolute; transform: translate3d(-141px, 19px, 0px); top: 0px; left: 0px;">
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                    <span class="ms-1">Delete</span>
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil-minus">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                        <path d="M13.5 6.5l4 4" />
                                                        <path d="M16 19h6" />
                                                    </svg>
                                                    <span class="ms-1">Edit</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="pf-profile mt-2">
                                        <img src="../src/assets/img/profile/profile-23.png" alt="pic1">
                                        <div class="pf-info">
                                            <h4 class="media-heading">Laurie Fox</h4>
                                            <p class="media-text">Argentina</p>
                                        </div>
                                    </div>

                                    <div class="social d-flex justify-content-center">
                                        
                                        <div class="pf-social pf-social-facebook me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-meta"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 10.174c1.766 -2.784 3.315 -4.174 4.648 -4.174c2 0 3.263 2.213 4 5.217c.704 2.869 .5 6.783 -2 6.783c-1.114 0 -2.648 -1.565 -4.148 -3.652a27.627 27.627 0 0 1 -2.5 -4.174z" /><path d="M12 10.174c-1.766 -2.784 -3.315 -4.174 -4.648 -4.174c-2 0 -3.263 2.213 -4 5.217c-.704 2.869 -.5 6.783 2 6.783c1.114 0 2.648 -1.565 4.148 -3.652c1 -1.391 1.833 -2.783 2.5 -4.174z" /></svg>
                                        </div>
                                        
                                        <div class="pf-social pf-social-instagram me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" /><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M16.5 7.5l0 .01" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-linkedin me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-linkedin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 11l0 5" /><path d="M8 8l0 .01" /><path d="M12 16l0 -5" /><path d="M16 16v-3a2 2 0 0 0 -4 0" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-xtweeter">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4l11.733 16h4.267l-11.733 -16z" /><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" /></svg>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-friends">

                                    <div class="pf-dropdown d-flex justify-content-end">
                                        <div class="dropdown d-inline-block">
                                            <a class="dropdown-toggle" href="#" role="button" id="elementDrodpown3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                            </a>
                                
                                            <div class="dropdown-menu left" aria-labelledby="elementDrodpown3" style="will-change: transform; position: absolute; transform: translate3d(-141px, 19px, 0px); top: 0px; left: 0px;">
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                    <span class="ms-1">Delete</span>
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil-minus">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                        <path d="M13.5 6.5l4 4" />
                                                        <path d="M16 19h6" />
                                                    </svg>
                                                    <span class="ms-1">Edit</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="pf-profile mt-2">
                                        <img src="../src/assets/img/profile/profile-15.png" alt="pic1">
                                        <div class="pf-info">
                                            <h4 class="media-heading">Andy King</h4>
                                            <p class="media-text">
                                                United States
                                            </p>
                                        </div>
                                    </div>

                                    <div class="social d-flex justify-content-center">
                                        
                                        <div class="pf-social pf-social-facebook me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-meta"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 10.174c1.766 -2.784 3.315 -4.174 4.648 -4.174c2 0 3.263 2.213 4 5.217c.704 2.869 .5 6.783 -2 6.783c-1.114 0 -2.648 -1.565 -4.148 -3.652a27.627 27.627 0 0 1 -2.5 -4.174z" /><path d="M12 10.174c-1.766 -2.784 -3.315 -4.174 -4.648 -4.174c-2 0 -3.263 2.213 -4 5.217c-.704 2.869 -.5 6.783 2 6.783c1.114 0 2.648 -1.565 4.148 -3.652c1 -1.391 1.833 -2.783 2.5 -4.174z" /></svg>
                                        </div>
                                        
                                        <div class="pf-social pf-social-instagram me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" /><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M16.5 7.5l0 .01" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-linkedin me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-linkedin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 11l0 5" /><path d="M8 8l0 .01" /><path d="M12 16l0 -5" /><path d="M16 16v-3a2 2 0 0 0 -4 0" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-xtweeter">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4l11.733 16h4.267l-11.733 -16z" /><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" /></svg>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>











                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-friends">

                                    <div class="pf-dropdown d-flex justify-content-end">
                                        <div class="dropdown d-inline-block">
                                            <a class="dropdown-toggle" href="#" role="button" id="elementDrodpown3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                            </a>
                                
                                            <div class="dropdown-menu left" aria-labelledby="elementDrodpown3" style="will-change: transform; position: absolute; transform: translate3d(-141px, 19px, 0px); top: 0px; left: 0px;">
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                    <span class="ms-1">Delete</span>
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil-minus">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                        <path d="M13.5 6.5l4 4" />
                                                        <path d="M16 19h6" />
                                                    </svg>
                                                    <span class="ms-1">Edit</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="pf-profile mt-2">
                                        <img src="../src/assets/img/profile/profile-33.png" alt="pic1">
                                        <div class="pf-info">
                                            <h4 class="media-heading">Carol Mack</h4>
                                            <p class="media-text">Balvania</p>
                                        </div>
                                    </div>

                                    <div class="social d-flex justify-content-center">
                                        
                                        <div class="pf-social pf-social-facebook me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-meta"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 10.174c1.766 -2.784 3.315 -4.174 4.648 -4.174c2 0 3.263 2.213 4 5.217c.704 2.869 .5 6.783 -2 6.783c-1.114 0 -2.648 -1.565 -4.148 -3.652a27.627 27.627 0 0 1 -2.5 -4.174z" /><path d="M12 10.174c-1.766 -2.784 -3.315 -4.174 -4.648 -4.174c-2 0 -3.263 2.213 -4 5.217c-.704 2.869 -.5 6.783 2 6.783c1.114 0 2.648 -1.565 4.148 -3.652c1 -1.391 1.833 -2.783 2.5 -4.174z" /></svg>
                                        </div>
                                        
                                        <div class="pf-social pf-social-instagram me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" /><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M16.5 7.5l0 .01" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-linkedin me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-linkedin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 11l0 5" /><path d="M8 8l0 .01" /><path d="M12 16l0 -5" /><path d="M16 16v-3a2 2 0 0 0 -4 0" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-xtweeter">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4l11.733 16h4.267l-11.733 -16z" /><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" /></svg>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-friends">

                                    <div class="pf-dropdown d-flex justify-content-end">
                                        <div class="dropdown d-inline-block">
                                            <a class="dropdown-toggle" href="#" role="button" id="elementDrodpown3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                            </a>
                                
                                            <div class="dropdown-menu left" aria-labelledby="elementDrodpown3" style="will-change: transform; position: absolute; transform: translate3d(-141px, 19px, 0px); top: 0px; left: 0px;">
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                    <span class="ms-1">Delete</span>
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil-minus">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                        <path d="M13.5 6.5l4 4" />
                                                        <path d="M16 19h6" />
                                                    </svg>
                                                    <span class="ms-1">Edit</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="pf-profile mt-2">
                                        <img src="../src/assets/img/profile/profile-9.png" alt="pic1">
                                        <div class="pf-info">
                                            <h4 class="media-heading">Devin Cole</h4>
                                            <p class="media-text">Solancia</p>
                                        </div>
                                    </div>

                                    <div class="social d-flex justify-content-center">
                                        
                                        <div class="pf-social pf-social-facebook me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-meta"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 10.174c1.766 -2.784 3.315 -4.174 4.648 -4.174c2 0 3.263 2.213 4 5.217c.704 2.869 .5 6.783 -2 6.783c-1.114 0 -2.648 -1.565 -4.148 -3.652a27.627 27.627 0 0 1 -2.5 -4.174z" /><path d="M12 10.174c-1.766 -2.784 -3.315 -4.174 -4.648 -4.174c-2 0 -3.263 2.213 -4 5.217c-.704 2.869 -.5 6.783 2 6.783c1.114 0 2.648 -1.565 4.148 -3.652c1 -1.391 1.833 -2.783 2.5 -4.174z" /></svg>
                                        </div>
                                        
                                        <div class="pf-social pf-social-instagram me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" /><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M16.5 7.5l0 .01" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-linkedin me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-linkedin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 11l0 5" /><path d="M8 8l0 .01" /><path d="M12 16l0 -5" /><path d="M16 16v-3a2 2 0 0 0 -4 0" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-xtweeter">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4l11.733 16h4.267l-11.733 -16z" /><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" /></svg>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-friends">

                                    <div class="pf-dropdown d-flex justify-content-end">
                                        <div class="dropdown d-inline-block">
                                            <a class="dropdown-toggle" href="#" role="button" id="elementDrodpown3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                            </a>
                                
                                            <div class="dropdown-menu left" aria-labelledby="elementDrodpown3" style="will-change: transform; position: absolute; transform: translate3d(-141px, 19px, 0px); top: 0px; left: 0px;">
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                    <span class="ms-1">Delete</span>
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil-minus">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                        <path d="M13.5 6.5l4 4" />
                                                        <path d="M16 19h6" />
                                                    </svg>
                                                    <span class="ms-1">Edit</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="pf-profile mt-2">
                                        <img src="../src/assets/img/profile/profile-22.png" alt="pic1">
                                        <div class="pf-info">
                                            <h4 class="media-heading">Felicia Terry</h4>
                                            <p class="media-text">Canada</p>
                                        </div>
                                    </div>

                                    <div class="social d-flex justify-content-center">
                                        
                                        <div class="pf-social pf-social-facebook me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-meta"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 10.174c1.766 -2.784 3.315 -4.174 4.648 -4.174c2 0 3.263 2.213 4 5.217c.704 2.869 .5 6.783 -2 6.783c-1.114 0 -2.648 -1.565 -4.148 -3.652a27.627 27.627 0 0 1 -2.5 -4.174z" /><path d="M12 10.174c-1.766 -2.784 -3.315 -4.174 -4.648 -4.174c-2 0 -3.263 2.213 -4 5.217c-.704 2.869 -.5 6.783 2 6.783c1.114 0 2.648 -1.565 4.148 -3.652c1 -1.391 1.833 -2.783 2.5 -4.174z" /></svg>
                                        </div>
                                        
                                        <div class="pf-social pf-social-instagram me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" /><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M16.5 7.5l0 .01" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-linkedin me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-linkedin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 11l0 5" /><path d="M8 8l0 .01" /><path d="M12 16l0 -5" /><path d="M16 16v-3a2 2 0 0 0 -4 0" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-xtweeter">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4l11.733 16h4.267l-11.733 -16z" /><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" /></svg>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-friends">

                                    <div class="pf-dropdown d-flex justify-content-end">
                                        <div class="dropdown d-inline-block">
                                            <a class="dropdown-toggle" href="#" role="button" id="elementDrodpown3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                            </a>
                                
                                            <div class="dropdown-menu left" aria-labelledby="elementDrodpown3" style="will-change: transform; position: absolute; transform: translate3d(-141px, 19px, 0px); top: 0px; left: 0px;">
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                    <span class="ms-1">Delete</span>
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);">
                                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil-minus">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                                                        <path d="M13.5 6.5l4 4" />
                                                        <path d="M16 19h6" />
                                                    </svg>
                                                    <span class="ms-1">Edit</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="pf-profile mt-2">
                                        <img src="../src/assets/img/profile/profile-28.png" alt="pic1">
                                        <div class="pf-info">
                                            <h4 class="media-heading">Laurie Fox</h4>
                                            <p class="media-text">Brazil</p>
                                        </div>
                                    </div>

                                    <div class="social d-flex justify-content-center">
                                        
                                        <div class="pf-social pf-social-facebook me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-meta"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 10.174c1.766 -2.784 3.315 -4.174 4.648 -4.174c2 0 3.263 2.213 4 5.217c.704 2.869 .5 6.783 -2 6.783c-1.114 0 -2.648 -1.565 -4.148 -3.652a27.627 27.627 0 0 1 -2.5 -4.174z" /><path d="M12 10.174c-1.766 -2.784 -3.315 -4.174 -4.648 -4.174c-2 0 -3.263 2.213 -4 5.217c-.704 2.869 -.5 6.783 2 6.783c1.114 0 2.648 -1.565 4.148 -3.652c1 -1.391 1.833 -2.783 2.5 -4.174z" /></svg>
                                        </div>
                                        
                                        <div class="pf-social pf-social-instagram me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-instagram"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 4a4 4 0 0 1 4 -4h8a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" /><path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M16.5 7.5l0 .01" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-linkedin me-2">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-linkedin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M8 11l0 5" /><path d="M8 8l0 .01" /><path d="M12 16l0 -5" /><path d="M16 16v-3a2 2 0 0 0 -4 0" /></svg>
                                        </div>

                                        <div class="pf-social pf-social-xtweeter">
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-brand-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4l11.733 16h4.267l-11.733 -16z" /><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" /></svg>
                                        </div>
                                        
                                    </div>

                                </div>
                                
                            </div>                                            
                            
                        </div>
                    </div>

                    <div class="tab-pane fade profile-gallery-tab" id="nav-disabled" role="tabpanel" aria-labelledby="nav-disabled-tab" tabindex="0">

                        <div class="profile-gallery-title mb-5">
                            <h3>Gallery</h3>
                        </div>
                        
                        <div class="row">    

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-gallery" style="background-image: url('../src/assets/img/apps/users/abstract-2.jpg');">
                                    
                                    <div class="pg-overflow"></div>

                                    <div class="gallery-img-overlay">
                                        <h5 class="gallery-title text-truncate">The Future of AI: How Artificial Intelligence is Reshaping Our World</h5>
                                        <p class="pg-meta mb-0"><small>16 Jan</small></p>
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-gallery" style="background-image: url('../src/assets/img/apps/users/abstract-4.jpg');">
                                    
                                    <div class="pg-overflow"></div>

                                    <div class="gallery-img-overlay">
                                        <h5 class="gallery-title text-truncate">Understanding Climate Change: The Science Behind Global Warming</h5>
                                        <p class="pg-meta mb-0"><small>19 Mar</small></p>
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-gallery" style="background-image: url('../src/assets/img/apps/users/abstract-3.jpg');">
                                    
                                    <div class="pg-overflow"></div>

                                    <div class="gallery-img-overlay">
                                        <h5 class="gallery-title text-truncate">The Role of Space Exploration in Advancing Science and Technology</h5>
                                        <p class="pg-meta mb-0"><small>26 Apr</small></p>
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-gallery" style="background-image: url('../src/assets/img/apps/users/abstract-1.jpg');">
                                    
                                    <div class="pg-overflow"></div>

                                    <div class="gallery-img-overlay">
                                        <h5 class="gallery-title text-truncate">Cryptocurrencies and the Global Economy: Disruption or Evolution?</h5>
                                        <p class="pg-meta mb-0"><small>14 Jul</small></p>
                                    </div>

                                </div>
                                
                            </div>



                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-gallery" style="background-image: url('../src/assets/img/apps/users/abstract-6.jpg');">
                                    
                                    <div class="pg-overflow"></div>

                                    <div class="gallery-img-overlay">
                                        <h5 class="gallery-title text-truncate">The Science of Vaccines: How They Work and Why They Matter</h5>
                                        <p class="pg-meta mb-0"><small>22 Aug</small></p>
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-gallery" style="background-image: url('../src/assets/img/apps/users/abstract-7.jpg');">
                                    
                                    <div class="pg-overflow"></div>

                                    <div class="gallery-img-overlay">
                                        <h5 class="gallery-title text-truncate">Cybersecurity in the Digital Age: Protecting Your Data in a Connected World</h5>
                                        <p class="pg-meta mb-0"><small>17 Oct</small></p>
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-gallery" style="background-image: url('../src/assets/img/apps/users/abstract-8.jpg');">
                                    
                                    <div class="pg-overflow"></div>

                                    <div class="gallery-img-overlay">
                                        <h5 class="gallery-title text-truncate">The New Space Race: Private Companies Leading the Charge to the Stars</h5>
                                        <p class="pg-meta mb-0"><small>08 Nov</small></p>
                                    </div>

                                </div>
                                
                            </div>

                            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-4">

                                <div class="profile-gallery" style="background-image: url('../src/assets/img/apps/users/abstract-5.jpg');">
                                    
                                    <div class="pg-overflow"></div>

                                    <div class="gallery-img-overlay">
                                        <h5 class="gallery-title text-truncate">Economic Recovery: Strategies for Building Resilience in a Post-Crisis World</h5>
                                        <p class="pg-meta mb-0"><small>19 Dec</small></p>
                                    </div>

                                </div>
                                
                            </div>
                            
                        </div>
                        
                    </div>
                </div>
                
            </div>
            
            
            
            
        </div>

        
    </div>

</div>



@endsection