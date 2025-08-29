<!DOCTYPE html>
<html {{ language_attributes() }}>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@hook('apply:rooty/editor/title', 'Theme Editor')</title>
    @includeIf('rooty.partials.favicon')

    {{-- 1) Laisser les modules enqueuer leurs assets dans NOTRE registry --}}
    @hook('fire', 'rooty/editor/enqueue_assets', $editor ?? null, app('wp.assets'))
    {{-- (Compat facultative) --}}
    @hook('fire', 'rooty/editor/enqueue_scripts', $editor ?? null, app('wp.assets'))

    {{-- 2) Imprimer ce qui a été enqueued pour le <head> (styles + scripts head) --}}
    @php app(\App\Services\WordPress\Assets::class)->printHead(); @endphp

    {{-- 3) Extension point "late" AVANT Vite --}}
    @hook('fire', 'rooty/editor/print_head_pre', $editor ?? null)

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite('resources/js/rooty/editor/main.js')
    @endif

    @hook('fire', 'rooty/editor/print_head_post', $editor ?? null)
  </head>
  <body @class(app('wp.body_classes')->compute('rooty/editor/body_classes'))>
    @yield('editor-content')
    {{-- @hookUnless(false, 'fire', 'rooty/editor/before_footer') --}}
    {{-- @includeIf('rooty.partials.wp-footer') --}}
    {{-- @hookUnless(false, 'fire', 'rooty/editor/after_footer') --}}
  </body>
</html>
