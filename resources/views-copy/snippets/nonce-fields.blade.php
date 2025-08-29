@php
  @if (empty($action) || empty($nonce)) {
    return;
  }
@endphp

<input type="hidden" name="action" value="{{ esc_attr($action) }}">
<input type="hidden" name="nonce" value="{{ esc_attr($nonce) }}">
