@extends('layouts.app')

@section('content')
    @php
        $menuItems = [
            'dashboard' => 'Dashboard',
            'organizer.games.index' => 'Mijn spellen',
            'organizer.questions.index' => 'Vragen',
            'organizer.grading.index' => 'Nakijken',
            'organizer.results.index' => 'Resultaten',
        ];
    @endphp

    <div class="admin-shell">
        <aside class="admin-sidebar">
            <p class="eyebrow">BEHEER</p>

            <nav class="admin-menu" aria-label="Beheermenu">
                @foreach ($menuItems as $routeName => $label)
                    @php
                        $routePattern = str_replace('.index', '.*', $routeName);
                        $isActive = request()->routeIs($routePattern);
                    @endphp

                    @if (\Illuminate\Support\Facades\Route::has($routeName))
                        <a
                            href="{{ route($routeName) }}"
                            class="sidebar-link {{ $isActive ? 'is-active' : '' }}"
                            @if ($isActive)
                                aria-current="page"
                            @endif
                        >
                            {{ $label }}
                        </a>
                    @else
                        <span
                            class="sidebar-link is-disabled"
                            role="link"
                            aria-disabled="true"
                        >
                            {{ $label }}
                        </span>
                    @endif
                @endforeach
            </nav>
        </aside>

        <div class="admin-content">
            @if (session('success'))
                <div class="notice notice-success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="notice notice-error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('organizer-content')
        </div>
    </div>
@endsection
