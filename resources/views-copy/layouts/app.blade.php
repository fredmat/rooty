<!DOCTYPE html>
<html {{ language_attributes() }} class="no-js">
  <head>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite([
        // 'resources/js/app.js',
        // 'resources/css/app.css',
      ])
    @endif

    {{-- {{ wp_head() }} --}}
  </head>
  <body>
    <main id="MainContent" role="main" tabindex="-1">
      @yield('main-content')
    </main>
  </body>
</html>
