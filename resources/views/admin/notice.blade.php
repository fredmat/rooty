@php
  $allowedTypes = ['error', 'warning', 'success', 'info'];
  $type = in_array($type ?? null, $allowedTypes, true) ? $type : 'info';
  $dismissible = !empty($dismissible) ? 'is-dismissible' : '';
@endphp

<div class="notice notice-{{ $type }} {{ $dismissible }}">
  <p>{!! $message !!}</p>
</div>
