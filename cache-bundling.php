<?php

require 'composer/vendor/autoload.php';
use axy\sourcemap\SourceMap;

class awesome_Cache_Bundling
{
  /**
   * Autoload method
   * @return void
   */
  function __construct() {
    //Add submenu in settings page
    add_action('admin_menu', array(&$this, 'register_sub_menu'));

    //Cache Bundling and JS Sourcemap
    add_filter("awesome_get_content_before_render", array(&$this, 'cache_bundling_functionality'), 10, 1);

    //Add defer in script tag
    add_filter('script_loader_tag', array(&$this, 'add_defer_attribute_script_tag'), 10, 2);

    //Add link in style tag
    add_filter('style_loader_tag', array(&$this, 'add_onload_attribute_link_tag'), 10, 2);
  }

  /**
   * Add onload in link tag 
   * @return string
   */
  function add_onload_attribute_link_tag($tag, $handle) {
    $tag = str_replace('<link rel=\'stylesheet\'', '<link rel=\'stylesheet\' onload="this.media == \'none\'?this.media=\'all\':\'\'" ', $tag);
    return $tag;
  }

  /**
   * Add defer in script tag 
   * @return string
   */
  function add_defer_attribute_script_tag($tag, $handle) {
    $defer_js = get_option("awesome-cache-defer-js") ?: "on";
    if (!is_admin() && $defer_js == "on") {
      $tag = str_replace(' src', ' defer="defer" src', $tag);
    }
    return $tag;
  }

  /**
   * Register submenu
   * @return void
   */
  function register_sub_menu() {
    add_submenu_page(
      'options-general.php',
      'Cache Bundling',
      'Cache Bundling',
      'manage_options',
      'cache-bundling-setting',
      array(&$this, 'submenu_page_callback')
    );
  }

  /**
   * Render submenu
   * @return void
   */
  function submenu_page_callback() {
    //Update options
    if (isset($_POST['update-option'])) {
      update_option('awesome-cache-bundle', sanitize_text_field($_POST['cache-bundle']));
      update_option('awesome-cache-minfy-html', sanitize_text_field($_POST['minfy-html']));
      update_option('awesome-cache-defer-js', sanitize_text_field($_POST['defer-js']));
      update_option('awesome-cache-exclude-page-url', sanitize_text_field($_POST['exclude-page-url']));
      update_option('awesome-cache-exclude-js-file', sanitize_text_field($_POST['exclude-js-file']));
      echo '<div class="notice notice-success is-dismissible"><p>Setting options successfully  updated.</p></div>';
    }

    //Clear Cache
    if (isset($_POST['clear-cache'])) {
      $cache_root_path = ABSPATH . "wp-content/cache/";
      $cache_folder = array("js", "css");
      foreach ($cache_folder as $folder) {
        $this->deleteDir($cache_root_path . $folder);
      }
      echo '<div class="notice notice-success is-dismissible"><p>Cache successfully cleared.</p></div>';
    }

    $cache_bundle = get_option("awesome-cache-bundle") ?: "on";
    $minfy_html = get_option("awesome-cache-minfy-html") ?: "on";
    $defer_js = get_option("awesome-cache-defer-js") ?: "on";
    $exclude_page_url = get_option("awesome-cache-exclude-page-url") ?: "";
    $exclude_js_file = get_option("awesome-cache-exclude-js-file") ?: "";

    ?>
  <h3>Cache Bundling</h3>

  <form method="POST" action="" class="setting-page-container">
    <div class="setting-page-row">
      <label class="setting-page-label">Cache Bundle</label>
      <input type="radio" id="cache-bundle-toggle-on" name="cache-bundle" value="on" <?php echo (($cache_bundle == "on") ? "checked" : ""); ?>><label for="cache-bundle-toggle-on">On</label>
      <input type="radio" id="cache-bundle-toggle-off" name="cache-bundle" value="off" <?php echo (($cache_bundle == "off") ? "checked" : ""); ?>><label for="cache-bundle-toggle-off">Off</label>
    </div>

    <div class="setting-page-row">
      <hr>
    </div>

    <div class="setting-page-row">
      <label class="setting-page-label">Minify HTML</label>
      <input type="radio" id="minfy-html-toggle-on" name="minfy-html" value="on" <?php echo (($minfy_html == "on") ? "checked" : ""); ?>><label for="minfy-html-toggle-on">On</label>
      <input type="radio" id="minfy-html-toggle-off" name="minfy-html" value="off" <?php echo (($minfy_html == "off") ? "checked" : ""); ?>><label for="minfy-html-toggle-off">Off</label>
    </div>

    <div class="setting-page-row">
      <label class="setting-page-label">Defer parsing of JS files</label>
      <input type="radio" id="defer-js-toggle-on" name="defer-js" value="on" <?php echo (($defer_js == "on") ? "checked" : ""); ?>><label for="defer-js-toggle-on">On</label>
      <input type="radio" id="defer-js-toggle-off" name="defer-js" value="off" <?php echo (($defer_js == "off") ? "checked" : ""); ?>><label for="defer-js-toggle-off">Off</label>
    </div>

    <div class="setting-page-row">
      <label class="setting-page-label">Exclude by Page URL <br>(Separate by comma, without domain name) <br>eg : / = homepage <br> /live = live landing page <br> /guide/guide-post-url = guide single post type page</label>
      <textarea name="exclude-page-url" rows="5" cols="75"><?php echo $exclude_page_url; ?></textarea>
    </div>

    <div class="setting-page-row">
      <label class="setting-page-label">Exclude JS files <br>(Separate by comma, without domain) <br>eg : /wp-content/themes/themesname/js/js-filename.js</label>
      <textarea name="exclude-js-file" rows="5" cols="75"><?php echo $exclude_js_file; ?></textarea>
    </div>

    <div class="setting-page-row">
      <label class="setting-page-label">&nbsp;</label>
      <input type="submit" name="update-option" class="button button-primary" value="Save Changes">
    </div>

    <hr>

    <div class="setting-page-clear-cache">
      <div class="notice notice-error">
        <p><strong>Note: </strong>Clearing cache will delete JS and CSS cache folder, we do not recommended to clear the same until and unless its throughly necessary.</p>
      </div>

      <input type="submit" name="clear-cache" class="button button-default" value="Clear Cache (Not Recommend)">
    </div>
  </form>
  <?php }

/*
 * Remove the directory and its content (all files and subdirectories).
 * @param string $dir the directory name
 */
function deleteDir($dir) {
  foreach (glob($dir) as $file) {
    if (is_dir($file)) {
      $this->deleteDir("$file/*");
      rmdir($file);
    } else {
      unlink($file);
    }
  }
}

/**
 * Add custom filter "awesome_get_content_before_render" 
 * Cache bundling functionality with sourcemap
 * @return string
 */
function cache_bundling_functionality($content) {

  //check if cache bundle feature disable
  $cache_bundle = get_option("awesome-cache-bundle") ?: "on";
  if ($cache_bundle == "off") {
    return $content;
  }

  //Exclude cache bundling by page URL
  $exclude_page_url = get_option("awesome-cache-exclude-page-url") ?: "";
  $exclude_page_url_arr = explode(",", $exclude_page_url);

  foreach ($exclude_page_url_arr as $url) {
    if ($url == parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH)) {
      return $content;
    }
  }

  //Create Cache folder with index.html
  $cache_root_dir = ABSPATH;
  $cache_root_path = $cache_root_dir . 'wp-content';
  $cache_root_php = '<?php if(!defined("ABSPATH")){ exit; } ?>';

  $cache_folder = array("/cache", "/cache/js", "/cache/js/map", "/cache/css");
  foreach ($cache_folder as $folder) {
    //create folder
    if (!file_exists($cache_root_path . $folder)) {
      mkdir($cache_root_path . $folder);
    }
    //create html files
    if (!file_exists($cache_root_path . $folder . '/index.php')) {
      file_put_contents($cache_root_path . $folder . '/index.php', $cache_root_php);
    }
  }

  //JS Cache bundling with sourcemap
  $script_md5 = '';
  $script_files = array();
  $slack_notification = false;

  // Get script files.
  if (preg_match_all('#<script.*</script>#Usmi', $content, $matches)) {
    foreach ($matches[0] as $tag) {
      /**
       * get external scripts file
       *  */
      if (preg_match('#<script[^>]*src=("|\')([^>]*)("|\')#Usmi', $tag, $source)) {
        $url = $source[2];

        //Exclude JS File
        $exclude_js_file = get_option("awesome-cache-exclude-js-file") ?: "";
        $exclude_js_file_arr = explode(",", $exclude_js_file);
        $parse_url_path = parse_url($url, PHP_URL_PATH);

        //only wp-content folder js files
        if (strpos($url, '/wp-content/') !== false && !in_array($parse_url_path, $exclude_js_file_arr)) {

          $path_file_info = pathinfo($cache_root_dir . $parse_url_path);
          $path_map_file = $path_file_info['dirname'] . "/maps/" . $path_file_info['basename'] . '.map';

          //Check map file exists
          if (file_exists($path_map_file)) {
            //md5 name creation creating with files name and query string
            $script_md5 .= str_replace("/wp-content/themes/themesname/", "", $parse_url_path) . parse_url($url, PHP_URL_QUERY);

            $script_files[] = array("parse_url_path" => $cache_root_dir . $parse_url_path, "map_file" => $path_map_file);

            //remove script tags from content
            $content = str_replace($tag, '', $content);
          } else if ($slack_notification) {
            //Alert to slack channel of file not have sourcemap integration
            $slack_notify = new AwesomeSlackNotify();
            $message = "File sourcamap integration fail error. \n Page : " .
              "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . " \n File : " . $url;

            $slack_notify->webhook_notify("channel-name", ":nginx:", "slack-hooks-url", $message);
          }
        }
      }
    }
  }

  //Check JS md5 exists
  $js_md5_path = $cache_root_path . '/cache/js/awesome_' . md5($script_md5) . '.js';
  $js_md5_map_path =  $cache_root_path . '/cache/js/map/awesome_' . md5($script_md5) . '.js.map';

  if (!file_exists($js_md5_path)) {
    //axy sourcemap init
    $map = new SourceMap();
    $map->file = $js_md5_path;

    // The content of the resulting file
    $result = [];
    $line = 0;

    foreach ($script_files as $script) {
      $map_file = $script["map_file"];

      //load the next file
      $js_content = file_get_contents($script["parse_url_path"]);

      //remove link to a source map
      $js_content = preg_replace('~//# sourceMappingURL.*$~s', '', $js_content);
      $js_content = rtrim($js_content);
      $result[] = $js_content;

      //add next file
      $map->concat($map_file, $line);
      //shift to next position    
      $line += substr_count($js_content, "\n") + 1;
    }

    // add a link to the resulting source map
    $result[] = '//# sourceMappingURL=' . str_replace(ABSPATH, '/', $js_md5_map_path);

    // save the resulting JS-file
    $result = implode("\n", $result);
    file_put_contents($js_md5_path, $result);

    // save the resulting source map
    $map->save($js_md5_map_path);
  }

  $defer_js = get_option("awesome-cache-defer-js") ?: "on";

  $new_tag = '<script ' . ($defer_js == "on" ? ' defer="defer" ' : '') . ' src="' . get_home_url() . '/wp-content/cache/js/awesome_' . md5($script_md5) . '.js?v=' . filemtime($js_md5_path) . '"></script>';

  $end = stripos($content, '</body');
  $content = substr_replace($content, $new_tag, $end, 0);

  /**
   * CSS Cache bundling without sourcemap
   *  */
  $style_md5 = '';
  $style_files = array();

  /**
   * Get stylesheet files.
   */
  if (preg_match_all('#<link[^>]*stylesheet[^>]*>#Usmi', $content, $matches)) {
    foreach ($matches[0] as $tag) {
      /**
       * get external stylesheet file 
       */
      if (preg_match('#<link[^>]*href=("|\')([^>]*)("|\')#Usmi', $tag, $source)) {
        $url = $source[2];

        //only wp-content folder stylesheet files
        if (strpos($url, '.com/wp-content/') !== false) {
          $parse_url = parse_url($url, PHP_URL_PATH);

          //md5 name creation creating with files name and query string
          $style_md5 .= str_replace("/wp-content/themes/themesname/", "", $parse_url) . parse_url($url, PHP_URL_QUERY);

          $style_files[] = $parse_url;

          //remove link tags from content
          $content = str_replace($tag, '', $content);
        }
      }
    }
  }

  //check md5 exists
  $css_md5_file = $cache_root_path . '/cache/css/awesome_' . md5($style_md5) . '.css';

  if (!file_exists($css_md5_file)) {
    $file_contents = '';
    foreach ($style_files as $style) {
      $file_contents .= file_get_contents($cache_root_dir . $style);
    }

    //Replace background path url
    $parse_domain_path = parse_url(get_template_directory_uri(), PHP_URL_PATH);

    $file_contents = str_replace( 'url("../../img/', 'url("'. $parse_domain_path . '/img/' , $file_contents);
    $file_contents = str_replace( 'url("../img/', 'url("'. $parse_domain_path . '/img/' , $file_contents);

    $fp = fopen($css_md5_file, 'w');
    fwrite($fp, $file_contents);
    fclose($fp);
  }

  $new_tag = '<link onload="this.media == \'none\'?this.media=\'all\':\'\'" rel="stylesheet" href="' . get_home_url() . '/wp-content/cache/css/awesome_' . md5($style_md5) . '.css?v=' . filemtime($css_md5_file) . '" media="none"/><noscript><link rel="stylesheet" href="' . get_home_url() . '/wp-content/cache/css/awesome_' . md5($style_md5) . '.css?v=' . filemtime($css_md5_file) . '" media="all"/></noscript>';

  $end = stripos($content, '</head');
  $content = substr_replace($content, $new_tag, $end, 0);

  return $content;
  }
}

new awesome_Cache_Bundling();
