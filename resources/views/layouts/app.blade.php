<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'QR-Game')</title>

    <link rel="stylesheet" href="{{ asset('css/qr-game.css') }}">
</head>

<body>
    <a class="skip-link" href="#main-content">
        Naar de inhoud
    </a>

    <header class="site-header">
        <nav class="container navigation" aria-label="Hoofdnavigatie">
            <a class="brand" href="{{ route('home') }}">
                QR<span>-Game</span>
            </a>

            <div class="nav-actions">
                <a href="{{ route('home') }}#spelregels">
                    Spelregels
                </a>

                @auth
                    <a href="{{ route('dashboard') }}">
                        Dashboard
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="nav-form">
                        @csrf

                        <button type="submit" class="link-button">
                            Uitloggen
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}">
                        Organisator
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <main id="main-content" class="container">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>QR-Game · Ontdek, speel en leer.</p>
        </div>
    </footer>
</body>

</html>
