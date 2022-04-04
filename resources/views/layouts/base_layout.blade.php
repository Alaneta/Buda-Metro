<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        @include('includes.head_basics')
        @yield('css_custom_files')
        @yield('css_custom_style')
        <title>Buda Metro</title>
    </head>
    <body>
        @include('layouts.header')
        <section class="main">
            @yield('content')
        </section>
        @yield('footer')
    </body>
    @include('includes.js_basic_files')
    @yield('js_custom_files')
    {{--    @include('includes.js_basic_code')--}}
    @stack('scripts')
</html>
