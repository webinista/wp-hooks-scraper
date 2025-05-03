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


if(!file_exists(Conf::WP_VERSION)) {
  $save_dir = mkdir(Conf::WP_VERSION);
} else {
  $save_dir = Conf::WP_VERSION;
}

$to_download = \Dom\HTMLDocument::createFromFile('filter-reference.html');

$doc = $to_download->documentElement;
$article = $doc->querySelectorAll('article')->item(0);

$hooks = $article->querySelectorAll('article a[href*="/reference/hooks/"]');

foreach($hooks as $hook):
  $file_name = pathinfo(
    $hook->attributes->getNamedItem('href')->value,
    PATHINFO_FILENAME
  );
  
  $cmd = sprintf(
    'wget %1s --output-document=%2$s/%3$s.html',
    $hook->attributes->getNamedItem('href')->value,
    Conf::WP_VERSION,
    rtrim($file_name,'/')
  );

  print shell_exec( $cmd );
  
endforeach;
