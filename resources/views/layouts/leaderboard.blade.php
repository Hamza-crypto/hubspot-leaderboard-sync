<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>@yield('title') - {{ env('APP_NAME') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <link class="js-stylesheet" href="{{ asset('assets/css/light.css') }}" rel="stylesheet">

    @yield('styles')
</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-behavior="sticky">


    <div class="wrapper">



        <div class="main">

            <main class="content">
                <div class="container-fluid p-0">

                    @yield('content')
                </div>
            </main>

            {{-- @include('includes.footer') --}}
        </div>

    </div>


    <script src="{{ asset('/assets/js/app.js') }}"></script>

    @yield('scripts')

</body>

</html>
