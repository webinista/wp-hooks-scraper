<?php namespace WPScrape;


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

$file = '_admin_menu.html';

$path = sprintf(
  '.%1$s%2$s%1$s%3$s',
  DIRECTORY_SEPARATOR,
  Conf::WP_VERSION,
  $file
);

$scrape = new Scrape($path);

/* 
{
  "name": "the_password_form",
  "url": "https:\/\/developer.wordpress.org\/reference\/hooks\/the_password_form\/",
  "type": "filter",
  "deprecated": false,
  "description": "Filters the HTML output for the protected post password form.",
  "source": "wp-includes\/post-template.php:1830",
  "category": "post-template",
  "version": "2.7.0"
} */
        
        
$hook['name'] = $scrape->get_hook();
$hook['url']  = sprintf(Conf::HOOKS_URL_BASE, $hook['name']);
$hook['type'] = $scrape->is_action() ? 'action' : 'filter';
$hook['deprecated'] = $scrape->is_deprecated();
$hook['internal'] = $scrape->is_internal();
$hook['description'] = $scrape->get_description();
$hook['source'] = $scrape->get_source_location();


print_r( json_encode($hook, JSON_PRETTY_PRINT) );

# HOOKS_URL_BASE
