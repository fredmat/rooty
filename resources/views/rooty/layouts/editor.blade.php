<!DOCTYPE html>
<html {{ language_attributes() }}>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    {{-- <title>@hook('apply:rooty/editor/title', 'Theme Editor')</title> --}}

    @includeIf('rooty.partials.favicon')

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite('resources/js/rooty/editor/main.js')
    @endif
  </head>
  <body>
    @yield('content')
  </body>
</html>
