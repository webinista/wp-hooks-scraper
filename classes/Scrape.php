<?php namespace WPScrape;

final class Scrape {
  public \Dom\HTMLDocument $document;

  public function __construct( $file_path ) {
    if( !file_exists( $file_path ) ) {
      throw new Exception('Couldn\t open that file. Check its path and permissions');
    }
    
    // TODO: Validate the URL first.
    $this->document = \Dom\HTMLDocument::createFromFile($file_path);
  }
  
  public function get_hook():string {
    return $this->document->querySelector('.is-current-page')->textContent;
  }
  
  public function get_description():string {
    $body = $this->document->body;
    $description = $body->querySelector('.wp-block-wporg-code-reference-summary');
    
    return trim($description->textContent);
  }

  public function get_source_location():string {
    $body = $this->document->body;
    $source = $body->querySelector('.wp-block-wporg-code-reference-source');
    
    $file_link = $source
                 ->querySelector('.wporg-dot-link-list a[href*="/reference/files/"]');
    $href = $file_link->attributes->getNamedItem('href')->value;
    
    $line = $source
            ->querySelector('.wp-block-code')
            ->attributes->getNamedItem('data-start')->value;

    $file = str_replace('/reference/files', '', parse_url($href,  PHP_URL_PATH));
    
    return sprintf('%s:%d', $file, intval($line));
  }
  
  public function get_category():string {
    $category = $this->document->body
                ->querySelector('.wporg-dot-link-list a[href*="/reference/files/"]');
    
    $cat_link = rtrim($category->attributes->getNamedItem('href')->value, '/');
    return pathinfo(parse_url($cat_link, PHP_URL_PATH), PATHINFO_FILENAME);
  }
  
  public function get_version() {
    $change_log = $this->document->body
                  ->querySelector('.wp-block-wporg-code-reference-changelog tbody a')
                  ->textContent;
    return $change_log;
  }
  
  public function is_action() {
    $hook_func = $this->document->querySelector('.hook-func')->textContent;
    return stristr($hook_func, 'do_action') !== false;
  } 

  public function is_filter() {
    $hook_func = $this->document->querySelector('.hook-func')->textContent;
    return stristr($hook_func, 'apply_filters') !== false;
  }

  public function is_deprecated() {
    $msg = $this->document->body->querySelectorAll('.wp-block-wporg-code-reference-deprecated');
    return boolval($msg->length);
  }
  
  public function is_internal() {
    $msg = $this->document->body->querySelectorAll('.wp-block-wporg-code-reference-private-access');
    return boolval($msg->length);
  }
}