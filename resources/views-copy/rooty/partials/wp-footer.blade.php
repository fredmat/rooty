@php

  if (! defined('ABSPATH')) {
    die('-1');
  }

  global $hook_suffix, $pagenow;

@endphp

<script type="text/javascript">
  var ajaxurl = "<?php echo esc_url(admin_url('admin-ajax.php')); ?>";
  var pagenow = "<?php echo esc_js($pagenow ?: $hook_suffix); ?>";
</script>

@php

  do_action("admin_print_footer_scripts-{$hook_suffix}");
  // do_action('admin_print_footer_scripts');
  do_action("admin_footer-{$hook_suffix}");
  do_action('admin_footer');

@endphp

<script type="text/javascript">
  if (typeof wpOnload === 'function') {
    wpOnload();
  }
</script>
