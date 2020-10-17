<?php

// Creating the widget 
class libria_subpages extends WP_Widget {
  function __construct() {
    parent::__construct('libria_subpages', 'Navigation', array(
      'description' => 'Widget, das die Unterseiten der gerade angezeigten Seite auflistet, sofern vorhanden.'
    ));
  }

  public function widget($args, $instance) {
    global $post;
    
    $title = apply_filters('widget_title', (isset($instance['title'])) ? $instance['title'] : '');
    
    if (!is_page()) {
      echo $args['before_widget'];
      echo $args['before_title'] . $title . ' (nicht angezeigt)' . $args['after_title'];
      echo '<p>weil das keine Seite ist</p>';
      echo $args['after_widget'];
      return;
    }
    
    $children = wp_list_pages(array(
      'title_li' => '',
      'echo' => 0,
      'depth' => 2,
      'child_of' => ($post->post_parent != 0) ? $post->post_parent : $post->ID
    ));
    
    if (!$children) {
      echo $args['before_widget'];
      echo $args['before_title'] . $title . ' (nicht angezeigt)' . $args['after_title'];
      echo '<p>weil die Seite keine Unterseiten hat</p>';
      echo $args['after_widget'];
      return;
    }
    
    $siteId = ($post->post_parent != 0) ? $post->post_parent : $post->ID;
    $siteTitle = get_page($siteId)->post_title;
    
    $class = 'page_item page-item-' . $siteId;
    if ($siteId != $post->ID) {
      $siteTitle = '&laquo; ' . $siteTitle;
      $class .= ' current_page_ancestor current_page_parent';
    }
    else {
      $class .= ' current_page_item';
    }
    
    echo $args['before_widget'];
    if (!empty($title))
      echo $args['before_title'] . $title . $args['after_title'];

    echo '<ul><li class="' . $class . '">' . 
           '<a href="' . get_page_link($siteId) . '">' . $siteTitle . '</a>' .
           '<ul class="children">' . $children . '</ul>' . 
         '</li></ul>';
    
    echo $args['after_widget'];
  }
      
  // Widget Backend 
  public function form($instance) {
    $title = (isset($instance['title'])) ? $instance['title'] : '';
    ?>
    <p>
    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label> 
    <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
    </p>
    <?php 
  }
    
  // Updating widget replacing old instances with new
  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['title'] = (empty($new_instance['title'])) ? '' : strip_tags($new_instance['title']);
    return $instance;
  }
}

// Register and load the widget
function libria_load_widget() {
  register_widget('libria_subpages');
}
add_action('widgets_init', 'libria_load_widget');

// Disable the widget when it would be empty
function libria_disable_widget($sidebars_widgets) {
  global $wp_customize, $post;
  
  if (isset($wp_customize) && $wp_customize->is_preview()) {
    // don't disable any widgets in preview mode
    return $sidebars_widgets;
  }
  
  $disable = true;
  
  if (is_page()) {
    $children = wp_list_pages(array(
      'title_li' => '',
      'echo' => 0,
      'depth' => 2,
      'child_of' => ($post->post_parent != 0) ? $post->post_parent : $post->ID
    ));
    
    if ($children)
      $disable = false;
  }
  
  if (!$disable)
    return $sidebars_widgets;
  
  // for each widget area
  foreach ($sidebars_widgets as $widget_area => $widget_list) {

    // for each widget in that area
    foreach ($widget_list as $pos => $widget_id) {
      
      if (strpos($widget_id, 'libria_subpages') === 0) {
        unset($sidebars_widgets[$widget_area][$pos]);
      }
    }
  }

  return $sidebars_widgets;
}
add_filter('sidebars_widgets', 'libria_disable_widget');


if (!function_exists('get_page_link_by_slug')) {
  function get_page_link_by_slug($page_slug) {
    $page = get_page_by_path($page_slug);
    if ($page) {
      return get_permalink($page->ID);
    }
    else {
      return "#";
    }
  }
}
if (!function_exists('closetags')) {
  function closetags($html) {
    preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    for ($i=0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= '</'.$openedtags[$i].'>';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
  } 
}

function libria_last_articles($atts) {
  $a = shortcode_atts( array(
    'big' => 0,
    'small' => 0,
    'days' => 0,
    'categories' => 0,
    'link' => false,
  ), $atts );
  $big = intval($a['big']);
  $small = intval($a['small']);
  $days = $a['days'];
  $cats = $a['categories'];

  if ($big + $small == 0) {
    $big = -1;
  }

  $content = '';

  $subcats = get_categories(array('child_of' => $cats));
  $subcat_ids = array();
  foreach ($subcats as $subcat) {
    array_push($subcat_ids, $subcat->cat_ID);
  }
  
  $date_query = array();

  if ($days > 0) {
    $date_query = array(
      array(
        'column' => 'post_date_gmt',
        'after' => $days . ' days ago',
      )
    );
  }

  query_posts(array(
    'cat' => $cats,
    'posts_per_page' => $big + $small,
    'date_query' => $date_query,
  ));
  
  $is_small = false;
  
  if (have_posts()) {
    $count = 0;
    while (have_posts()) {
      the_post();
      $count++;
      if ($count <= $big || $big == -1) {
        $content .= '<div class="postItem">';
          
          $is_subcat = false;
          foreach (wp_get_post_categories(get_the_id()) as $postcat) {
            
            $cats_str = get_category_parents($postcat, false, '%#%');
            $cats_array = explode('%#%', $cats_str);
            $cat_depth = sizeof($cats_array)-2;

            if ($cat_depth > 0) {
              if ($is_subcat) {
                $content .= ', ';
              }
              else {
                $content .= '<h4>';
              }
              $content .= get_category($postcat)->name;
              $is_subcat = true;
            }
          } 
          if ($is_subcat) {
            $content .= '</h4>';
          }
          
          $content .= 
              '<h2 class="topic"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>' . 
              '<div class="entry-meta">geschrieben am <time class="entry-date" datetime="' . get_the_time('Y-m-d H:i') . '" title="' . get_the_time('Y-m-d H:i') . '">' . get_the_time(get_option('date_format')) . '</time>';
          if ($cats_meow = libria_cats_meow(', ')) { // Returns categories other than the one queried
              $content .= ' &#8211; <span class="cat-links">' . $cats_meow . '</span>';
          }
          $content .= '</div>';
          
          if (has_post_thumbnail()) {
            $content .= '<a href="' . get_permalink() . '" class="thumbnail">' . get_the_post_thumbnail(get_the_id(), 'thumbnail') . '</a>';
          } 
          
          $content .= '<div class="topic">';
          if (has_excerpt(get_the_id())) {
            $content .= wpautop(get_the_excerpt() . ' <a href="' . get_permalink() . '">&rarr; Weiterlesen</a>');
          }
          else {
            global $more;
            $temp = $more;
            $more = false;
            $content .= wpautop(do_shortcode(get_the_content('&rarr; Weiterlesen')));
            $more = $temp;
          }
          $content .= '</div';
          $content .= '<div class="clear"></div>';
        $content .= '</div>';
      } // if ($count <= $big)
      else {
        $is_small = true;
        if ($count == $big + 1) {
          if ($big > 0) {
            $content .= '<h4>Weitere Artikel:</h4>';
          }
          $content .= '<ul>';
        }
        
        $content .= '<li>' . get_the_date('d.m.Y');
        $is_subcat = false;
        foreach (wp_get_post_categories(get_the_id()) as $postcat) {
          if (cat_is_ancestor_of($cats, $postcat)) {
            if ($is_subcat) {
              $content .= ', ';
            }
            else { 
              $content .= ' (';
            }
            $content .= '<i>' . get_category($postcat)->name . '</i>';
            $is_subcat = true;
          }
        }
        if ($is_subcat) {
          $content .= ')';
        }
        $content .= ': <i><a href="' . get_permalink() . '" class="topic">' . get_the_title() . '</a></i></li>';
      } // else ($count <= $gross)
    } // while (have_posts())
    
    echo "cats: " . implode($subcat_ids, ", ");
    
    if ($a['link']) {
      echo "cats: " . implode($subcat_ids, ", ");
      $args = array(
        'include' => $subcat_ids
      );
      $categories = get_categories($args);
      
      foreach ($categories as $category) {
        echo '<p>Category: <a href="' . get_category_link( $category->term_id ) . '" title="' . sprintf( __( "View all posts in %s" ), $category->name ) . '" ' . '>' . $category->name . ' with '  . $category->count . ' posts </a> </p> ';
      }
    }
  } // if (have_posts())
  wp_reset_query();

  if ($is_small) {
      $content .= '</ul>';
  }

  return $content;
}

add_shortcode('last_articles', 'libria_last_articles');
  

add_filter('wp_list_categories', 'libria_remove_category_list_rel');
add_filter('the_category', 'libria_remove_category_list_rel');
function libria_remove_category_list_rel($output) {
  // to make output valid HTML5
  $output = str_replace(' rel="category tag"', '', $output);
  $output = str_replace(' rel="category"', '', $output);
  return $output;
}

// For category lists on category archives: Returns other categories except the current one (redundant)
function libria_cats_meow($glue) {
  $current_cat = single_cat_title('', false);
  $separator = "\n";
  $cats = explode($separator, get_the_category_list($separator));
  foreach ($cats as $i => $str) {
    if (strstr($str, ">$current_cat<")) {
      unset($cats[$i]);
      break;
    }
  }
  if (empty($cats))
    return false;
 
  return trim(join($glue, $cats));
} // end cats_meow


add_action('widgets_init', 'libria_register_sidebars');
function libria_register_sidebars() {
  $sidebars = array(
    'sidebar-all' => array(
      'name' => 'Sidebar (Alle)',
      'desc' => 'Diese Sidebar ist überall zuerst sichtbar.'
    ),
    'sidebar-home' => array(
      'name' => 'Sidebar (Home)',
      'desc' => 'Diese Sidebar ist auf der Startseite sichtbar.'
    ),
    'sidebar-articlelist' => array(
      'name' => 'Sidebar (Meldungen)',
      'desc' => 'Diese Sidebar ist auf der Beitrags&uuml;bersicht sichtbar.'
    ),
    'sidebar-blogentry' => array(
      'name' => 'Sidebar (Blogeintrag)',
      'desc' => 'Diese Sidebar ist bei einzelnen Blogeintr&auml;gen sichtbar.'
    ),
    'sidebar-page' => array(
      'name' => 'Sidebar (Seite)',
      'desc' => 'Diese Sidebar ist nur auf den Seiten sichtbar.'
    ),
    'sidebar-other' => array(
      'name' => 'Sidebar (Sonstige)',
      'desc' => 'Diese Sidebar ist &uuml;berall anders sichtbar.'
    )
  );
  
  foreach($sidebars as $id => $opts) {
    register_sidebar(array(
      'name' => $opts['name'],
      'id' => $id,
      'description' => $opts['desc'],
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="widgettitle">',
      'after_title' => '</h3>'
    ));
  }
}

function libria_get_current_sidebar() {
  global $wp_query;

  if ($wp_query->is_single()) return 'sidebar-blogentry';
  if ($wp_query->is_home()) return 'sidebar-articlelist';
  if ($wp_query->is_front_page()) return 'sidebar-home';
  if ($wp_query->is_page()) return 'sidebar-page';

  return 'sidebar-other';
}

function libria_has_sidebar() {
  global $wp_query;

  if ($wp_query->is_404()) return false;

  return is_active_sidebar('sidebar-all') || is_active_sidebar(libria_get_current_sidebar());
}


// changes excerpt length
remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'libria_trim_excerpt');
function libria_trim_excerpt($text) { // Fakes an excerpt if needed
  global $post;
  
  if('' == $text) {
    $text = get_the_content();
    //$text = apply_filters('the_content', $text);
    $text = str_replace(']]>', ']]>', $text);
    $text = strip_tags($text, '<strong><a>');
    $text = preg_replace('/^\[.*?\]\s*$/m', '', $text);
    $excerpt_length = 150;
    $words = explode(' ', $text, $excerpt_length+1);
    if (count($words) > $excerpt_length) {
      array_pop($words);
      $text = implode(' ', $words);
      $text = closetags($text);
      $text .= ' ' . libria_excerpt_more('');
    }
  }
  return $text;
}


// Puts link in excerpts more tag
add_filter('excerpt_more', 'libria_excerpt_more');
function libria_excerpt_more($more) {
  global $post;
  return '… <a class="moretag" href="' . get_permalink($post->ID) . '">weiterlesen &raquo;</a>';
}

// call our custom wp_title filter, with normal (10) priority, and 3 args
add_filter('wp_title', 'libria_title', 10, 3);
// filter function for wp_title
function libria_title($old_title='', $sep='', $sep_location='') {
  // add padding to the sep
  $ssep = ' ' . $sep . ' ';
  if ($sep != '') {
    $old_title = str_replace($ssep, '', $old_title);
  }
  
  $new_title = $old_title;

  $num = '';
  if (get_query_var('paged')) {
    // get the page number we're on (index)
    $num = ' (Seite ' . get_query_var('paged') . ')';
  }
  elseif (get_query_var('page')) {
    // get the page number we're on (multipage post)
    $num = ' (Seite ' . get_query_var('page') . ')';
  }
  
  if (is_home() || is_front_page())
    return get_bloginfo('name') . $num; // do not append or prepend anything
  elseif (is_category())
    $new_title = 'Kategorie &bdquo;' . $old_title . '&ldquo;';
  elseif (is_tag())
    $new_title = 'Schlagwort &bdquo;' . $old_title . '&ldquo;';
  elseif (is_author())
    $new_title = 'Beiträge von ' . $old_title;
  elseif (is_day())
    $new_title = 'Beiträge vom ' . get_the_time(get_option('date_format'));
  elseif (is_month())
    $new_title = 'Beiträge aus dem ' . get_the_time('F Y');
  elseif (is_year())
    $new_title = 'Beiträge von ' . get_the_time('Y');
  elseif (is_search())
    $new_title = 'Suchergebnisse für &bdquo;' . get_search_query() . '&ldquo;';
  elseif (is_feed())
    $new_title = 'Feed';
  
  if ($sep_location == 'right')
    return $new_title . $num . ($sep == '' ? '' : $ssep . get_bloginfo('name'));
  
  return ($sep == '' ? '' : get_bloginfo('name') . $ssep) . $new_title . $num;
}

add_action('wp_enqueue_scripts', 'libria_scripts');
function libria_scripts() {
  // style
  wp_enqueue_style('libria-style', get_stylesheet_uri());
  
  // scripts
  wp_register_script('skrollr', get_template_directory_uri() . '/skrollr.min.js');
  wp_enqueue_script('libria-script', get_template_directory_uri() . '/script.js', array('skrollr'));
  wp_dequeue_script('jquery'); // stop loading jquery if not used
}

function libria_register_menu() {
  register_nav_menu('header-menu', 'Header-Menü');
}
add_action('init', 'libria_register_menu');

add_filter('widget_text', 'do_shortcode'); 
add_editor_style();
add_theme_support('automatic-feed-links');
add_theme_support('post-thumbnails');
set_post_thumbnail_size(170, 170, true);

?>
