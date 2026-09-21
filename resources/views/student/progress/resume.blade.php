@extends('layouts.app')

@section('content')
    <div class="player-shell">
        <section class="panel">
            <p class="eyebrow">{{ $game->name }}</p>

            <h1 class="page-title">Bekijk je voortgang</h1>

            <p>
                Vul het studentnummer in waarmee je aan dit spel
                hebt meegedaan.
            </p>

            @if ($errors->any())
                <div class="notice notice-error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('student.progress.resume', [
                    'game' => $game,
                ]) }}"
            >
                @csrf

                <div class="form-field">
                    <label for="student_number">Studentnummer</label>

                    <input
                        id="student_number"
                        name="student_number"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]+"
                        maxlength="20"
                        value="{{ old('student_number') }}"
                        required
                    >
                </div>

                <button type="submit" class="button button-wide">
                    Bekijk mijn voortgang
                </button>
            </form>

            <div class="form-actions">
                <a
                    href="{{ route('home') }}"
                    class="button button-secondary"
                >
                    Terug naar home
                </a>
            </div>
        </section>
    </div>
@endsection
