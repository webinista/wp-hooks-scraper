<?php namespace WPScrape;

error_reporting(0);

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

$data = [];

if ($handle = opendir('./6.8')):

  while (false !== ($entry = readdir($handle))):
  
    if(strpos($entry, '.') === false) continue;

    $path = sprintf(
      '.%1$s%2$s%1$s%3$s',
      DIRECTORY_SEPARATOR,
      Conf::WP_VERSION,
      $entry
    );

    $hook = [];

    if(strpos($entry, '.') !== false):
      $scrape = new Scrape($path);        

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
      
      $data[] = $hook;
  endwhile;
  
endif;

$fh = fopen('wordpress-hooks.json', 'w+');

if($fh) {
  fwrite(
    $fh,
    json_encode($data)
  );
  
  fclose($fh);
}

