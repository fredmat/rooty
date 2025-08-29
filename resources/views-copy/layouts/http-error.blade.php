<!DOCTYPE html>
<html {{ language_attributes() }}>
  <head>
    <meta charset="{{ $args['charset'] ?? 'utf-8' }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    {{-- @include('theme-editor.partials.head') --}}

    @php
      $title = ($title ?? false) ? "$title – " . app_name() : app_name();
    @endphp

    <title>{{ $title }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite([
        'resources/css/rooty/index.css',
      ])
    @endif
  </head>
  <body class="http-error{{ ($args['response'] ?? null) ? ' http-error--' . $args['response'] : '' }}">
    @yield('main-content')
  </body>
</html>