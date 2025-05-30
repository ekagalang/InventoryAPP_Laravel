<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel dengan Bootstrap</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="container mt-5">
            <h1>Halo, Bootstrap di Laravel!</h1>
            <p class="lead">Ini adalah contoh paragraf dengan styling Bootstrap.</p>
            <button class="btn btn-primary">Tombol Bootstrap</button>

            <div class="dropdown mt-3">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    Dropdown button
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li><a class="dropdown-item" href="#">Action</a></li>
                    <li><a class="dropdown-item" href="#">Another action</a></li>
                    <li><a class="dropdown-item" href="#">Something else here</a></li>
                </ul>
            </div>
        </div>
    </body>
</html>