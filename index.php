<?php
get_header();


$pageNr = (get_query_var('paged')) ? get_query_var('paged') : 1;

if ((is_home() || is_front_page())) {
  if($pageNr > 1) {
    echo '<h1>Seite ' . $pageNr . '</h1>';
  }
}
elseif (is_search()) {
  if (have_posts()) {
    echo '<h1 class="entry-title">' . libria_title(get_search_query()) . '</h1>';
  }
  else {
    echo '<h1 class="entry-title">Nichts gefunden</h1>';
    echo '<p>Zu deiner Suchanfrage &bdquo;' . get_search_query() . '&ldquo; konnte nichts gefunden werden.</p>';
  }
}
elseif (is_author()) {
  $curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));

  echo '<h1 class="entry-title">' . libria_title($curauth->display_name) . '</h1>';
  //echo get_avatar( $curauth->ID , 80 );
  
  if (1 == $pageNr) {
    if ($curauth->user_description) {
      echo '<p>' . $curauth->user_description . '</p>';
    }
    if ($curauth->user_url) {
      echo '<p>Webseite: <a href="' . $curauth->user_url . '">' . $curauth->user_url . '</a></p>';
    }
  }
}
elseif (is_day()) {
  echo '<h1 class="entry-title">' . libria_title() . '</h1>';
}
elseif (is_month()) {
  echo '<h1 class="entry-title">' . libria_title() . '</h1>';
}
elseif (is_year()) {
  echo '<h1 class="entry-title">' . libria_title() . '</h1>';
}
elseif (is_tag()) {
  echo '<h1 class="entry-title">Beiträge mit dem Schlagwort &bdquo;' . single_tag_title('', false) . '&ldquo;</h1>';
}
elseif (is_category()) {
  echo '<h1 class="entry-title">Beiträge aus der Kategorie &bdquo;' . single_cat_title('', false) . '&ldquo;</h1>';
}

if (!have_posts()) : ?>

  <article>Leider noch keine Artikel zum Anzeigen.</article>

<?php else :

while(have_posts()) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <?php if (is_single() || is_page()) : ?>
    <h1 class="entry-title"><?php the_title(); ?><?php edit_post_link(); ?></h1>
  <?php else : ?>
    <h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php printf('Permalink zu %s', the_title_attribute('echo=0') ); ?>" rel="bookmark"><?php the_title(); ?></a><?php edit_post_link(); ?></h2>

    <div class="entry-meta">geschrieben am <time class="entry-date" datetime="<?php the_time('Y-m-d H:i') ?>" title="<?php the_time('Y-m-d H:i') ?>"><?php the_time(get_option('date_format')); ?></time>
      <?php if ($cats_meow = libria_cats_meow(', ')) : // Returns categories other than the one queried ?>
        &#8211; <span class="cat-links"><?php echo $cats_meow; ?></span>
      <?php endif ?>
    </div>
  <?php endif; ?>
  
  <section class="entry-content"><?php
    if (is_single() || is_page()) {
      the_content();
      wp_link_pages(array(
        'before' => '<div class="page-link">Seiten:',
        'after' => '</div>'
      ));
    }
    else {
      if (has_post_thumbnail()) {
          ?><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="post-thumbnail"><?php the_post_thumbnail(); ?></a><?php
      }
      the_excerpt();
    }
  ?></section><!-- .entry-content -->
  
  <?php if (is_single()) {
    echo '<div class="entry-meta entry-meta-below">';
    echo 'geschrieben am <time class="entry-date" datetime="' . get_the_time('Y-m-d H:i') . '" title="' . get_the_time('Y-m-d H:i') . '">' . get_the_time(get_option('date_format')) . '</time> &#8211; ';
    
    $cats = get_the_category_list(', ');
    $tags = get_the_tag_list('', ', ');
    
    if ($cats) {
      echo 'Kategorien: <span class="cat-links">' . $cats . '</span>';
    }
    if ($cats && $tags) {
      echo ' &#8211; ';
    }
    if ($tags) {
      echo 'Tags: <span class="tag-links">' . $tags . '</span>';
    }
    echo '</div>';
  } ?>

  <div style="clear:left"></div>
</article>
<?php endwhile;
endif;

global $wp_query;
if ($wp_query->max_num_pages > 1) {
  echo '<div id="nav-below" class="navigation">';
  posts_nav_link(' &#8211; ');
  echo '</div>';
}

if (is_search()) {
  get_search_form();
}

get_footer(); ?>