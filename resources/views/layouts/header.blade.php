<!-- ======= Header ======= -->
<header id="header" class="fixed-top d-flex {{ Route::is('home') ?  'header-transparent' : 'header-scrolled' }}">
    <div class="container d-flex justify-content-between">
        <div id="logo" style="margin-top:10px;">
            <a class="navbar-brand" href="{{ route('home') }}">
                <h4 class="text-center">
                    <img src="{{url("img/logo.png")}}" height="50" width="50">
                    <span style="color: white; font-style: italic;">Buda Metro</span>
                </h4>
            </a>
        </div>
        @if (Route::is('home'))
            <nav id="navbar" class="navbar">
                <ul>
                    <li><a class="nav-link scrollto active" href="#hero">Home</a></li>
                    <li><a class="nav-link scrollto" href="#guide">Guía</a></li>
                    <li><a class="nav-link scrollto" href="#uploadNetwork">Carga de Red</a></li>
                    <li><a class="nav-link scrollto" href="#networksList">Redes de Metro</a></li>
                </ul>
                <i class="bi bi-list mobile-nav-toggle"></i>
            </nav><!-- .navbar -->
        @endif
    </div>
</header><!-- End Header -->
