@extends('rooty.layouts.editor')

@section('editor-content')

  <div
    id="app"
    data-config='@json(ajax()->clientConfig())'
  ></div>

@endsection
