<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="{{ strtoupper($company->name) }}">
    <meta name="keyword" content="{{ strtoupper($company->name) }}">

    <link rel="icon" href="{{ asset('storage/logo/'.$company->logo) }}" type="image/png+xml">
    <title>{{ strtoupper($company->name) }} - @yield('title', 'Admin Panel')</title>

    <!-- Fonts and Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    @livewireStyles
    @yield('styles')
</head>

<body>
<div class="admin-layout">
    @include('layout.admin.navbar')
    @include('layout.admin.sidebar')

    <main class="admin-content">
        <div class="content-header">
            <h1 class="content-title">@yield('page-title', 'Dashboard')</h1>
            <div class="content-breadcrumb">
                @yield('breadcrumb')
            </div>
        </div>

        <div class="content-body">
            @yield('content')
        </div>
    </main>
</div>

@include('layout.admin.scripts')
@yield('scripts')
@livewireScripts
</body>
</html>
