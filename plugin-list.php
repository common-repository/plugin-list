<?php
/*
Plugin Name: Plugin List
Plugin URI: http://www.wiso.cz/2006/09/20/my-first-wordpress-plugin-wp-plugin-list-10/
Description: By placing <!--plugin list--> in your page or post by sources code or using toolbar icon 'Plugin List' in your Rich text editor(only if enabled) plugin replaces it with list of currently installed WordPress plugins.
Version: 2.3
Author: Martin Wiso
Author URI: http://www.wiso.cz/
*/
require_once('wpol/wpol.inc.php');

/** Front functions **/
if(!function_exists('pl_plain_plugin_list')) {
  function pl_plain_plugin_list() 	{
    return pl_callback('<!--plugin list-->');
  }
}

/** Admin functions **/
if(!function_exists('pl_header')) {	
  function pl_header() {
    wpol_add_settings_page("Plugin List Options", "Plugin List", "administrator", "plugin-list/plugin-list.php", "pl_settings_page");    
    pl_register_settings();
  }
}
if(!function_exists('pl_init')) {
  function pl_init() {
    wpol_init('plugin-list');
    //wpol_enqueue_css('srlcss', wpol_option('siteurl').'wp-content/plugins/plugin-list/media/style.css');
  }
}
if(!function_exists('pl_settings_page')) {
  function pl_settings_page() {

    // refresh cache after saving new options
    if ('true' == $_REQUEST['updated']) {      
      wp_cache_add('pl_pluginlist_data', pl_get_plugin_list());
    }
  
    // create UI
    $page = '<p>By placing Maker &lt;!--plugin list--&gt; (just like (X)HTML comment with two dashes) in your page or post by sources code in your Rich text editor(only if enabled) plugin replaces it with list of currently installed WordPress plugins. Plugin List has some options that can be configured below.</p>';
    $page .= wpol_label('Show deactivated plugins', 'wppl_show_deactivated');
    $page .= wpol_checkbox('wppl_show_deactivated', pl_get_option('wppl_show_deactivated'), '', array('selected'=>pl_checked('wppl_show_deactivated')));
    $page .= wpol_br();
    $page .= wpol_label('Show plugin version', 'wppl_show_plugin_version');
    $page .= wpol_checkbox('wppl_show_plugin_version', pl_get_option('wppl_show_plugin_version'), '', array('selected'=>pl_checked('wppl_show_plugin_version')));
    $page .= wpol_br();
    $page .= wpol_label('Show plugin description', 'wppl_show_plugin_description');
    $page .= wpol_checkbox('wppl_show_plugin_description', pl_get_option('wppl_show_plugin_description'), '', array('selected'=>pl_checked('wppl_show_plugin_description')));
    $page .= wpol_br();
    $page .= wpol_label('Show Plugin List itself', 'wppl_show_plugin_itself');
    $page .= wpol_checkbox('wppl_show_plugin_itself', pl_get_option('wppl_show_plugin_itself'), '', array('selected'=>pl_checked('wppl_show_plugin_itself')));
    $page .= wpol_br(2);
    $page .= wpol_label('Show headline with stats', 'wppl_total_headline');
    $page .= wpol_checkbox('wppl_total_headline', pl_get_option('wppl_total_headline'), '', array('selected'=>pl_checked('wppl_total_headline')));
    $page .= wpol_br();
    $page .= wpol_label('Headline text', 'wppl_total_headline_text');
    $page .= wpol_text('wppl_total_headline_text', pl_get_option('wppl_total_headline_text'), '', array('style'=>'width:250px'));

    // render page
    wpol_settings_page('plugin-list', 'Plugin List Options', $page, $script);
  }
}
function pl_checked($key) {
  $val = get_option($key);  

  return (!empty($val) && $val == '1') ? 'checked' : '';
}
function pl_get_option($key) {
  $val = get_option($key);

  return (!empty($val)) ? $val : 1;
}
function pl_callback($content) {	
  $output = '';	
	
  // get plugins from cache
  $plugins = wp_cache_get('pl_pluginlist_data');
  if ($plugins == false)   	{
    $plugins = pl_get_plugin_list();  	
    wp_cache_add('pl_pluginlist_data', $plugins);
  }
  
  // to disable cache uncoment line below
  //$plugins = pl_get_plugin_list();  	
  
  if (get_settings('active_plugins'))
    $current = get_settings('active_plugins');
  
  if (count($plugins) < 1) {
    $output .= '<p>There are no plugin to display :(</p>';
  } else {	
    $total = 0;
    uksort($plugins, 'pl_sort_plugins');
    
    // list plugins based on settings
    $output .= "<ul>\n";
    $description = '';
    $version =  '';
    foreach($plugins as $file => $data) {
      // detection of non active plugins
      if (!get_option('wppl_show_deactivated') && !empty($current) && !in_array($file, $current))
	continue;

      $data['Description'] = wp_kses($data['Description'], array('a' => array('href' => array(),'title' => array()),'abbr' => array('title' => array()),'acronym' => array('title' => array()),'code' => array(),'em' => array(),'strong' => array()) );
			
      // disable display of this plugin itself
      if (!get_option('wppl_show_plugin_itself') && strpos($data['Title'], 'Plugin List') > 0)
	continue;			
			
      // display description and version
      $description = get_option('wppl_show_plugin_description') ? "{$data['Description']}<em>{$data['Author']}</em>" : "";
      $version = get_option('wppl_show_plugin_version') ? "v{$data['Version']}" : "";
      
      $output .= sprintf("<li>%s %s<br />%s</li>\n", $data['Title'], $version, $description);				
      $total++;
    }
    
    // headline with total
    $headline = '';
    if (get_option('wppl_show_total_headline') && strlen(get_option('wppl_total_headline_text')) > 0) {
      if (strpos(get_option('wppl_total_headline_text'), '%TOTAL%') != false)
	$headline .= "<p>&nbsp;</p><p><strong>".str_replace('%TOTAL%', $total, get_option('wppl_total_headline_text'))."</strong></p>\n";
    }
		
    $output = $headline.$output;
    $output .= "</ul>\n";
  }  	
  	
  /* Small hack to reported issues */
  if (strpos('<!--<del-->plugin list-&gt;', $pattern) > 0)
    return preg_replace('|<!--<del-->plugin list-&gt;|', $output, $content);
  else if (strpos('<!<del>-plugin list</del>->', $pattern) > 0)
    return preg_replace('|<!<del>-plugin list</del>->|', $output, $content);
  else if (strpos('<!-<del>plugin list</del>->', $pattern) > 0)
    return preg_replace('|<!-<del>plugin list</del>->|', $output, $content);
  else if (strpos('&#60;!&#45;&#45;plugin list&#45;&#45;&#62;', $pattern) > 0)
    return preg_replace('|&#60;!&#45;&#45;plugin list&#45;&#45;&#62;|', $output, $content);
  else
    return preg_replace('|<!--plugin list-->|', $output, $content);
}
if(!function_exists('pl_sort_plugins')) {
  function pl_sort_plugins($_plug1, $_plug2)  {
    return strnatcasecmp($_plug1['Name'], $_plug2['Name']);
  }
}
if(!function_exists('pl_register_settings')) {
  function pl_register_settings() {
    register_setting('plugin-list', 'wppl_show_deactivated');
    register_setting('plugin-list', 'wppl_show_plugin_version');
    register_setting('plugin-list', 'wppl_show_plugin_description');
    register_setting('plugin-list', 'wppl_show_plugin_itself');
    register_setting('plugin-list', 'wppl_total_headline');
    register_setting('plugin-list', 'wppl_total_headline_text');
  }
}
function pl_get_plugin_detail($pluginfile) {
  $data = implode('', file($pluginfile));
  preg_match("|Plugin Name:(.*)|i", $data, $plugin_name);
  preg_match("|Plugin URI:(.*)|i", $data, $plugin_uri);
  preg_match("|Description:(.*)|i", $data, $description);
  preg_match("|Author:(.*)|i", $data, $author_name);
  preg_match("|Author URI:(.*)|i", $data, $author_uri);  
  $version = (preg_match("|Version:(.*)|i", $data, $version)) ? trim($version[1]) : '';
  $description = wptexturize(trim($description[1]));  
  $name = trim($plugin_name[1]);
  $plugin = $name;  
  if ('' != $plugin_uri[1]  &&  '' != $name) {
    $plugin = '<a href="' . trim($plugin_uri[1]) . '" target="_blank" title="'.__('Visit plugin homepage').'">'.$plugin.'</a>';
  }  
  $author = ('' == $author_uri[1]) ? trim($author_name[1]) : '<a href="'.trim($author_uri[1]).'" target="_blank" title="'.__('Visit author homepage').'">'.trim($author_name[1]).'</a>';
  
  return array ('Name' => $name, 'Title' => $plugin, 'Description' => $description, 'Author' => $author, 'Version' => $version, 'Template' => $template[1]);
}
function pl_get_plugin_list() {
  $pluginlist = array();
  $pluginfiles = array();
  $pluginroot = ABSPATH.'wp-content/plugins';
  
  // Files in wp-content/plugins directory
  $pluginsdir = @ dir($pluginroot);
  if ($pluginsdir) {
    while (($filepath = $pluginsdir->read()) !== false) {
      if (preg_match('|^\.+$_|', $filepath))
	continue;
						
      if (is_dir($pluginroot.'/'.$filepath)) {
	$pluginssubdir = @ dir($pluginroot.'/'.$filepath);
	if ($pluginssubdir) {
	  while (($subfilepath = $pluginssubdir->read()) !== false) {
	    if (preg_match('|^\.+$_|', $subfilepath))
	      continue;
	    
	    if (preg_match('|\.php$|', $subfilepath))
	      $pluginfiles[] = "$filepath/$subfilepath";
	  }
	}
      } else {
	if (preg_match('|\.php$|', $filepath))
	  $pluginfiles[] = $filepath;
      }					
    }
  }
  
  // prepare data
  sort($pluginfiles);		
  foreach ($pluginfiles as $pluginfile) {
    if (!is_readable("$pluginroot/$pluginfile"))
      continue;
    
    $plugindata = pl_get_plugin_detail("$pluginroot/$pluginfile");
    
    if (empty($plugindata['Name']))
      continue;
		
    $pluginlist[plugin_basename($pluginfile)] = $plugindata;
  }
  
  return $pluginlist;
}

// register actions
if(is_admin()) {
  add_action('admin_menu', 'pl_header');
  add_action('admin_init', 'pl_register_settings');
} else {
  add_filter('the_content', 'pl_callback', 7);
}

// end of file
?>