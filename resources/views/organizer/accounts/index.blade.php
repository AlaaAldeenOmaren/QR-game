@extends('layouts.organizer')

@section('title', 'Accounts | QR-Game')

@section('organizer-content')
    <header class="dashboard-heading">
        <h1 class="page-title">Accounts</h1>
        <p>Bekijk de beheerders en organisatoren van QR-Game.</p>
        <div class="form-actions">
            <a href="{{ route('organizer.accounts.create') }}" class="button">Nieuw account</a>
        </div>
    </header>

    <section class="panel" aria-label="Accounts">
        @forelse ($accounts as $listedAccount)
            <article aria-labelledby="account-{{ $listedAccount->id }}">
                <h2 id="account-{{ $listedAccount->id }}">{{ $listedAccount->name }}</h2>
                <p style="overflow-wrap: anywhere;">{{ $listedAccount->email }}</p>
                <p>
                    Rol: <strong>{{ $listedAccount->is_admin ? 'Beheerder' : 'Organisator' }}</strong>
                    @if ((string) $listedAccount->id === (string) auth()->id())
                        <span>(jij)</span>
                    @endif
                    · Spellen: <strong>{{ $listedAccount->created_games_count }}</strong>
                </p>
            </article>
            @unless ($loop->last)
                <hr>
            @endunless
        @empty
            <p>Er zijn geen accounts gevonden.</p>
        @endforelse
    </section>

    @if ($accounts->hasPages())
        <nav class="form-actions" aria-label="Pagina's met accounts">
            @if ($accounts->previousPageUrl())
                <a href="{{ $accounts->previousPageUrl() }}" class="button button-secondary" rel="prev">Vorige</a>
            @endif
            <p>Pagina {{ $accounts->currentPage() }} van {{ $accounts->lastPage() }}</p>
            @if ($accounts->nextPageUrl())
                <a href="{{ $accounts->nextPageUrl() }}" class="button button-secondary" rel="next">Volgende</a>
            @endif
        </nav>
    @endif
@endsection
