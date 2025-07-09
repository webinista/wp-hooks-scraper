<?php namespace WPScrape;

/* Autoloads other class files in this directory */
spl_autoload_register(function ($class_name) {
    $load_dir = sprintf(
        '%1$s%2$sclasses%2$s',
        __DIR__,
        DIRECTORY_SEPARATOR
    );

    $fn = explode('\\', $class_name);

    $file = sprintf('%1$s%2$s.php', $load_dir, end($fn));

    print $file . PHP_EOL;
    
    if (file_exists($file)) :
        require $file;
    endif;
});

const CURRENT_HOOKS = 'wordpress-hooks.json';

if(!file_exists(Conf::WP_VERSION)) {
  $save_dir = mkdir(Conf::WP_VERSION);
} else {
  $save_dir = Conf::WP_VERSION;
}

$hooks_file = sprintf( 'since_%s.html', Conf::WP_VERSION );

#if( !file_exists( $hooks_file ) ):
  /* Download the latest hooks */
  $hooks = sprintf(Conf::SINCE_URL, Conf::WP_VERSION);
  $cmd = sprintf(
      'wget %s --output-document=%s',
      $hooks,
      $hooks_file
  );

  exec( $cmd );
# endif;

$list = [];

try {

  if ( !file_exists( $hooks_file ) ) {
    throw new ErrorException( sprintf( 'Can\'t find or read file %s.', $hooks_file) );
  }

  $hooks_list = \Dom\HTMLDocument::createFromFile( $hooks_file );
  $doc = $hooks_list->documentElement;

  $hooks = $doc->querySelectorAll('li:has(.hook)');

  /*
  Builds an object with the following keys:
  {
        "name": string,
        "url": string,
        "type": string,
        "deprecated": bool,
        "internal": bool,
        "description": string,
        "source": string,
        "category": string,
        "version": string
    }
  */

  foreach( $hooks as $hook ) :
    $source = trim( $hook->querySelector('.wp-block-wporg-code-type-usage-info__source')->textContent );
    $name = trim( $hook->querySelector('a[href*="/reference/hooks/"]')->textContent );

    $item = array(
      'name' => $name,
      'url' => sprintf( Conf::HOOKS_URL_BASE, $name ),
      'type' => trim( $hook->querySelector('.wp-block-wporg-code-short-title__type')->textContent ),
      'deprecated' => false,
      'internal' => false,
      'description' => trim( $hook->querySelector('.wp-block-post-excerpt__excerpt')->textContent ),
      'source' => trim( $source ),
      'category' => pathinfo( $source, PATHINFO_FILENAME ),
      'version' => Conf::WP_VERSION
    );

    $list[] = $item;
  endforeach;

} catch ( ErrorException $e ) {
  print_r( $e );
}

if( count( $list ) && file_exists( CURRENT_HOOKS ) ):
  $existing = json_decode( file_get_contents( CURRENT_HOOKS ), true );

  $updated = array_merge_recursive( $existing['hooks'], $list );
  $existing['hooks'] = Scrape::sort_alpha( $updated );

  $fh = fopen( CURRENT_HOOKS, 'w' );

  if ( $fh ) {
    fwrite( $fh, json_encode( $existing, JSON_PRETTY_PRINT ) );
    fclose( $fh );
  }

endif;

