<?php namespace WPScrape;

final class Scrape {
  public \Dom\HTMLDocument $document;

  public function __construct( $file_path ) {
    if ( !file_exists( $file_path ) ) {
      throw new \Exception('Couldn\t open that file. Check its path and permissions');
    }

    $this->document = \Dom\HTMLDocument::createFromFile($file_path);
  }
  
  public static function sort_alpha($objet): array {
    usort($objet, function($a, $b) {
      return strnatcmp($a['name'], $b['name']);
    });
    
    return $objet;
  }

  public function get_hook():string {
    $hook_text = '';
    
    $nav = $this->document->querySelector('.is-current-page');
    
    if($nav) {
      $hook_text = $nav->textContent;
    }
    return $hook_text;
  }
  
  public function get_description():string {
    $value = '';
    $body = $this->document->body;
    $description = $body->querySelector('.wp-block-wporg-code-reference-summary');
    
    if($description) {
      $value = trim($description->textContent);
    }
    return $value;
  }

  public function get_source_location():string {
    $value = '';

    $body = $this->document->body;
    $source = $body->querySelector('.wp-block-wporg-code-reference-source');
    
    if ( $source ):
      $file_link = $source
                   ->querySelector('.wporg-dot-link-list a[href*="/reference/files/"]');
      $href = $file_link->attributes->getNamedItem('href')->value;
      
      $line = $source
              ->querySelector('.wp-block-code')
              ->attributes->getNamedItem('data-start')->value;
  
      $file = str_replace('/reference/files', '', parse_url($href,  PHP_URL_PATH));
      $value = sprintf('%s:%d', $file, intval($line));
    endif;
    
    return $value;
  }
  
  public function get_category():string {
    $value = '';
    $category = $this->document->body
                ->querySelector('.wporg-dot-link-list a[href*="/reference/files/"]');
    
    if ($category):    
      $cat_link = rtrim($category->attributes->getNamedItem('href')->value, '/');
      $value = pathinfo(parse_url($cat_link, PHP_URL_PATH), PATHINFO_FILENAME);
    endif;
    
    return $value;
  }
  
  public function get_version():string {
    $value = '';
    $change_log = $this->document->body
                  ->querySelector('.wp-block-wporg-code-reference-changelog tbody a');
                  
    if($change_log) {
      $value = $change_log->textContent;
    }
    return $value;
  }
  
  public function is_action():bool {
    $value = false;
    
    $hook_func = $this->document->querySelector('.hook-func');
    if($hook_func){
      $value = stristr($hook_func->textContent, 'do_action') !== false;
    }
    return $value;
  } 

  public function is_filter():bool {
    $value = false;
    
    $hook_func = $this->document->querySelector('.hook-func');
    if($hook_func) {
      $value = (stristr($hook_func->textContent, 'apply_filters') !== false);
    }
    
    return $value;
  }

  public function is_deprecated():bool {
    $value = false;
    
    $msg = $this->document->body;
    if( $msg ) {
      $deprecated = $msg->querySelectorAll('.wp-block-wporg-code-reference-deprecated');
      $value = boolval($deprecated->length);      
    }
    
    return $value;
  }
  
  public function is_internal(): bool {
    $msg = $this->document->body->querySelectorAll('.wp-block-wporg-code-reference-private-access');
    return boolval($msg->length);
  }
}