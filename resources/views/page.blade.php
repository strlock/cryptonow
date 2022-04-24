<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&family=Poppins:wght@900&display=swap" rel="stylesheet">
        <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css" />
        <title>@yield('title')</title>
    </head>
    <body>
        @yield('content')
        <script type="text/javascript" src="{{asset('js/app.js')}}"></script>
        <script src="https://kit.fontawesome.com/8748648954.js" crossorigin="anonymous"></script>
        <script type="text/javascript" src="https://unpkg.com/@popperjs/core@2"></script>
        @yield('customjs')
    </body>
</html>
