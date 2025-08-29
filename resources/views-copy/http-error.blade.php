@extends('layouts.http-error')

@section('main-content')

  <div class="http-error__content">

    @if (! empty($title) && ! empty($args['show_title']))
      <h1 class="http-error__title">
        {{ $title }}
      </h1>
    @endif

    @if (! empty($message))
      <div class="http-error__message">
        {!! $message !!}
      </div>
    @endif

    @if (! empty($args['link_url']) && !empty($args['link_text']))
      <div class="http-error__actions">
        <a class="button button--secondary button--sm font-semibold" href="{{ esc_url($args['link_url']) }}">
          {{ $args['link_text'] }}
        </a>
      </div>
    @endif

  </div>

@endsection
