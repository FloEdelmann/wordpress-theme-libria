<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#FFEBB1" />
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
  <title><?php wp_title(' &#8211; ', true, 'right'); ?></title>
  <?php wp_head(); ?>
</head>
<body data-0="background-position:0px 0px;" data-100000="background-position:0px 40000px;" <?php body_class(); ?>>
<!--[if lt IE 9]><script>document.createElement("header");document.createElement("footer");document.createElement("section");document.createElement("aside");document.createElement("nav");document.createElement("article");document.createElement("time");</script><noscript><strong>Achtung!</strong>Weil Ihr Browser kein HTML5 unterst&uuml;tzt, müssen einige Elemente mit JScript simuliert werden. Ihr Browser hat aber Scripts deaktiviert. Bitte aktivieren Sie diese oder steigen Sie auf einen <a href="http://www.mozilla.org/de/firefox/">aktuellen, HTML5-fähigen Browser</a> um.</noscript><![endif]-->
<header id="panorama" data-0="top:0px;" data-100000="top:40000px;"></header>
<div id="main"<?php if (libria_has_sidebar()) echo ' class="has-sidebar"'; ?>>
<header>
  <h1><a href="<?php bloginfo('url'); ?>/"><img src="<?php echo get_template_directory_uri(); ?>/lesemaennchen.png" alt="Logo" />Förderer und Freunde der<br />Gemeindebücherei Vaterstetten e.V.</a></h1>
  <input type="checkbox" id="navigation-toggle" /><label for="navigation-toggle">Navigation</label>
  <?php
  wp_nav_menu(array(
    'theme_location' => 'header-menu',
    'container' => false,
    'depth' => 1,
    
    /* for fallback wp_page_menu: */
    'show_home' => 'Startseite',
    'sort_column' => 'menu_order'
  ));
  ?>
  <div style="clear:left"></div>
</header>
<content>