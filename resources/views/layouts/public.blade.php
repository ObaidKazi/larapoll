<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Polls' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @vite(['resources/js/poll.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-light bg-white border-bottom">
        <div class="container" style="max-width: 720px;">
            <a class="navbar-brand fw-bold" href="{{ route('polls.index') }}">Polls</a>
        </div>
    </nav>

    <main class="container py-5" style="max-width: 720px;">
        @yield('content')
    </main>

    <footer class="text-center text-muted py-4 small">
        Real-time polling
    </footer>

    @stack('scripts')
</body>
</html>