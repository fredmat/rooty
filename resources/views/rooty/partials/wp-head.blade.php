@php

  if (! defined('WP_ADMIN')) {
    require_once(public_path(('wp/wp-admin/admin.php')));
  }

  global $hook_suffix, $current_screen;

  if (empty($current_screen)) {
    set_current_screen();
  }

  // remove_action('admin_enqueue_scripts', 'wp_enqueue_emoji_styles');

  // remove_action('admin_print_scripts', 'print_emoji_detection_script');
  // // remove_action('admin_print_scripts', 'print_head_scripts', 20);
  // remove_action('admin_print_scripts-index.php', 'wp_localize_community_events');
  // remove_action('admin_print_scripts-post.php', 'wp_page_reload_on_back_button_js');
  // remove_action('admin_print_scripts-post-new.php', 'wp_page_reload_on_back_button_js');

  do_action('admin_enqueue_scripts', $hook_suffix);
  // do_action('admin_print_styles-'.$hook_suffix);
  // do_action('admin_print_styles');
  // do_action('admin_print_scripts-'.$hook_suffix);
  // do_action('admin_print_scripts');

  do_action('admin_head-'.$hook_suffix);

  remove_action('admin_head', 'wp_color_scheme_settings');
  remove_action('admin_head', 'wp_admin_canonical_url');
  remove_action('admin_head', 'wp_site_icon');
  remove_action('admin_head', 'wp_admin_viewport_meta');
  remove_action('admin_head', 'wp_admin_bar_header');

  do_action('admin_head');

@endphp
