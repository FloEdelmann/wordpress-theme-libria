</content>
<?php

if (libria_has_sidebar()) {
  echo '<aside id="sidebar" class="widget-area">';
  dynamic_sidebar('sidebar-all');
  dynamic_sidebar(libria_get_current_sidebar());
  echo '</aside>';
}

?>
<div style="clear:both;"></div>
<footer>
  <span id="copyright">&copy; <?php
    echo date("Y") . ' ';

    $contact_url = get_page_link_by_slug('kontakt');
    $about_url = get_page_link_by_slug('impressum');
    if ($contact_url == '#' && $about_url == '#') {
      bloginfo('name');
    }
    else if ($about_url == '#') {
      echo '<a href="' . $contact_url . '">' . get_bloginfo('name') . '</a>';
    }
    else {
      echo '<a href="' . $about_url . '">' . get_bloginfo('name') . '</a>';
    }

  ?></span><span class="sep"> &#8211; </span><span class="login"><?php
    if (is_user_logged_in()) {
      echo '<a href="' . wp_logout_url('/') . '">Logout</a>';
    }
    else {
      echo '<a href="' . wp_login_url('wp-admin/') . '">Login</a>';
    }
  ?></span>
</footer>
</div><!-- #main -->
<?php wp_footer(); ?>
</body>
</html>