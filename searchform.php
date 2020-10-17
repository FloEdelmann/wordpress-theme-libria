<form role="search" method="get" class="search" action="<?php echo home_url( '/' ); ?>">
  <input type="search" value="<?php the_search_query(); ?>" name="s" size="30" placeholder="Suchbegriff eingeben..." />
  <button type="submit"><span>Suchen</span></button>
</form>
