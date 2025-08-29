<!DOCTYPE html>
<html {{ language_attributes() }} class="no-js">
  <head>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite('resources/js/frontend/app.js')
    @endif
  </head>
  <body>

    {{-- @dump(app('wp')->conflicts) --}}
    {{-- @dump(app('acf')) --}}
    {{-- @dump(app('acf.settings')) --}}

    {{-- @dump(acf()) --}}
    {{-- @dump(asset()) --}}
    {{-- @dump(asset_acf()) --}}

    {{-- @dump(asset_acf()) --}}
    {{-- @dump(asset_acf_path()) --}}
    {{-- @dump(assets_acf_base()) --}}

    {{-- @dump(config('acf.settings')) --}}

    {{-- @dump(app('log')) --}}

    <main>
      @yield('content')
    </main>
  </body>
</html>
