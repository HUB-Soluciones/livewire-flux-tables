@extends('workbench::layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold tracking-tight">Tabla de Usuarios</h2>
        <p class="mt-1 text-sm text-zinc-500">
            50 usuarios de prueba · búsqueda, filtros, selección, exportaciones, acciones masivas, columnas sticky y paginación
        </p>
    </div>

    @livewire('users-table')
@endsection
