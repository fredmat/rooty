@php
$fields = [];
if (isset($_GET['theme_preview'])) {
  $path = storage_path('app/private/theme-editor/previews/theme-editor.json');

  if (file_exists($path)) {
    $json = json_decode(file_get_contents($path), true);

    if (is_array($json['fields'] ?? null)) {
      $fields = $json['fields'];
    }
  }
}
@endphp

@extends('layouts.app')

@section('main-content')

  {{-- [main — preview] --}}

  {{-- <h3>Frontend</h2>
  <div>
    @dump($fields)
  </div> --}}

@endsection
