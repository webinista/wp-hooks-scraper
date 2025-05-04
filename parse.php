<?php namespace WPScrape;

error_reporting( E_ALL & ~E_NOTICE & ~E_USER_NOTICE & ~E_WARNING );

// /Users/tiffany/wordpress-scrape-new/6.8/image_size_names_choose.html
// /Users/tiffany/wordpress-scrape-new/6.8/_admin_menu.html
// /Users/tiffany/wordpress-scrape-new/6.8/add_category_form_pre.html
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

$Updated = new \DateTimeImmutable();

$data = [
  'version' => Conf::WP_VERSION,
  'last_updated' => $Updated->format("j F Y"),
  'hooks' => []
];

#$files = dirname(__FILE__) . DIRECTORY_SEPARATOR . sprintf('pages_%s', Constants::VERSION);
$files = new \DirectoryIterator( sprintf('.%s%s', DIRECTORY_SEPARATOR, Conf::WP_VERSION) );


// Filenames should be numeric. e.g. 1.html, not index.html
while( $files->valid() ):
  
  if( !$files->isDot() && $files->isReadable() ):
      $scrape = new Scrape( $files->getRealPath() );
 
      $hook['name'] = $scrape->get_hook();
      $hook['url']  = sprintf(Conf::HOOKS_URL_BASE, $hook['name']);
      $hook['type'] = $scrape->is_action() ? 'action' : 'filter';
      $hook['deprecated'] = $scrape->is_deprecated();
      $hook['internal'] = $scrape->is_internal();
      $hook['description'] = $scrape->get_description();
      $hook['source'] = $scrape->get_source_location();
      $hook['category'] = $scrape->get_category();
      $hook['version'] = $scrape->get_version();
  endif;
  
  $data['hooks'][$files->key()] = $hook;
  
  $files->next();
endwhile;

/* Remove empty entries if there are any */
$data['hooks'] = array_filter(
  $data['hooks'],
  function($item) {
    return $item['name'] !== '' ;
  }
);

$data['hooks'] = array_values($data['hooks']);

$data['hooks'] = array_filter($data['hooks'],
  function($item) { return !is_null($item); }
);

$data['hooks'] = Scrape::sort_alpha($data['hooks']);

$fh = fopen('/Users/tiffany/wp-hooks/static/wordpress-hooks.json', 'w');

if($fh) {
  fwrite(
    $fh,
    json_encode($data)
  );
  
  fclose($fh);
}

