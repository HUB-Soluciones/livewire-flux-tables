<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livewire Flux Tables — Demo</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="{{ url('/flux-demo.css') }}">
    <style>
        /* Sólo para este demo (sin build de Vite): el navegador ignora los
           @source/@theme de flux.css (at-rules de Tailwind) y ui-table-scroll-area
           no trae una regla de display propia. En una app real esto lo cubre
           `@import 'flux'` en app.css. */
        :root { --color-accent: #27272a; --color-accent-content: #27272a; --color-accent-foreground: #fff; }
        :root.dark { --color-accent: #fff; --color-accent-content: #fff; --color-accent-foreground: #27272a; }
        ui-table-scroll-area { display: block; }
    </style>
    @fluxAppearance
    @livewireStyles
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased">

    <header class="border-b border-zinc-200 bg-white px-6 py-4">
        <div class="mx-auto max-w-6xl flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">Livewire Flux Tables</h1>
                <p class="text-xs text-zinc-500">Demo interactivo del paquete</p>
            </div>
            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600">
                hubsoluciones/livewire-flux-tables
            </span>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-8">
        @yield('content')
    </main>

    @livewireScripts
    @fluxScripts
</body>
</html>
