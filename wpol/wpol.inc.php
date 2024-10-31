<?php
/**
 * WordPress OpenLab Helper Library
 * 
 * Version: 1.0
 * Author: Martin Wiso
 * Author URI: http://www.openlab.net/
 */

if (!function_exists('wpol_init')) {
	
  // wpol ajax related helpers
  function wpol_init($path) {
    wpol_enqueue_js('json2', wpol_option('siteurl').'/wp-content/plugins/'.$path.'/wpol/json2.js');
    wpol_enqueue_js('wpoljs', wpol_option('siteurl').'/wp-content/plugins/'.$path.'/wpol/wpol.js');
    wpol_enqueue_css('wpolcss', wpol_option('siteurl').'/wp-content/plugins/'.$path.'/wpol/media/style.css');
  }
  function wpol_ajax_indicator($path) {
    return wpol_img("../wp-content/plugins/$path/wpol/media/activity-indicator.gif", 'Loading...', array('style'=>"display:none;float:left;margin-top: 7px;margin-left: 10px", 'id'=>"activity"));
  }
  function wpol_ajax_token($action) {
    return wp_create_nonce($action);
  }
  function wpol_ajax_verify($nonce, $action) {
    return wp_verify_nonce($nonce, $action);
  }
  
  // wp api helpers
  function wpol_valid_request($id) {	
    return wp_verify_nonce($_REQUEST['_wpnonce'], $id);
  }
  function wpol_request_token($id) {
    return wp_create_nonce($id);
  }
  function wpol_enqueue_js($name, $path, $dependencies=null) {
    wp_enqueue_script($name, $path, $dependencies);
  }
  function wpol_enqueue_css($name, $path) {
    wp_enqueue_style($name, $path);
  }
  
  function wpol_add_menu_page($name, $level, $path, $callbackName, $icon=null) {
    add_menu_page($name, $name, $level, $path, $callbackName, $icon);
  }
  function wpol_add_settings_page($name, $title, $capability, $path, $callbackName=null, $icon=null) {
    add_options_page($name, $title, $capability, $path, $callbackName, $icon);
  }
  
  function wpol_option($name) {
    global $wpdb;	
    return $wpdb->get_var("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name='$name'");
  }
	
  // UI element helpers
  function wpol_admin_page($name, $body, $script=null) {
    $javascript = '';
    if ($script != null) {
      $javascript = sprintf('<script type="text/javascript">
			//<![CDATA[
			%s
			//]]>
			</script>', $script);	
    }
		
    printf('%s
	  <div class="wrap">
    	    <h2>%s</h2>
	    <br /><br />
 	    %s  			
	  </div>', $javascript, $name, $body);
  }
  function wpol_settings_page($plugin, $name, $body, $script=null) {
    if ($script != null) {
      printf('<script type="text/javascript">
	      //<![CDATA[
	      %s
	      //]]>
	     </script>', $script);
    }  

    // start
    echo '<div class="wrap">';
    echo wpol_headline(2, $name);
    echo '<form method="post" action="options.php">';

    settings_fields($plugin);

    // content
    echo $body;

    // buttons area
    echo '<p class="submit">';
    echo wpol_submit('submit', 'Update Options', 'Update Options', array('class'=>'button-primary'));
    echo '</p>';

    // end
    echo '</form></div>';
  }
	
  // element helpers
  function wpol_list($items, $attrs=array()) {
    $lis = '';
    foreach ($items as $text) {
      $lis .= wpol_tag('li', null, $text);
    }
			
    return wpol_tag('ul', $attrs, $lis);
  }
  function wpol_link_list($items) {
    $lis = '';
    foreach ($items as $href=>$text) {
      $lis .= wpol_tag('li', null, wpol_link($text, $href));
    }
    
    return wpol_tag('ul', null, $lis);
  }
  function wpol_link($text, $href, $attrs=array()) {
    return wpol_tag('a', wpol_merge_attrs(array('href'=> $href), $attrs), $text);
  }
  function wpol_br($count=1, $attrs=array()) {
    if ($count > 1) {
      for ($i = 0; $i < $count; $i++) {
	$br .= wpol_tag('br', $attrs); 
      }
    } else {
      $br = wpol_tag('br', $attrs);
    }
    
    return $br;
  }
  function wpol_div($content, $css=null, $attrs=array()) {
    return wpol_tag('div', wpol_merge_attrs((($css != null) ? array('class'=>$css) : array()), $attrs), ((is_array($content)) ? join("\n", $content) : $content));
  }
  function wpol_img($src, $alt='', $attrs=array()) {
		return wpol_tag('img', wpol_merge_attrs(array('src'=>$src,'alt'=>$alt), $attrs), $text.$suffix);
  }
  function wpol_headline($type, $text, $attrs=array()) {
    return wpol_tag('h'.$type, $attrs, $text);
  }
  function wpol_table($cols, $rows=array(), $attrs=array(), $emptyText='') {
    $attributes = array_merge(array('class'=>'widefat post fixed', 'cellspacing'=>0), $attrs);
    
    // create table content
    $output .= '<thead><tr>';
    foreach ($cols as $col) {
      $output .= "<th>$col</th>";
    }
    $output .= '</tr></thead><tbody>';
    if (count($rows) > 0) {
      foreach ($rows as $row) {
	$output .= "<tr>";
	foreach ($row as $cell) {
	  $output .= "<td>$cell</td>";   				
	}
	$output .= "</tr>";
      }
    } else {
      $output .= "<tr><td colspan=\"".count($cols)."\">$emptyText</td></tr>";
    }
    $output .= '</tbody>';
    
    return wpol_tag('table', $attributes, $output);		
  }
  
  // form helpers
  function wpol_form($items, $action='', $attrs=array()){
    return wpol_tag('form', wpol_merge_attrs(array('action'=>$action), $attrs), join("\n", $items));
  }
  function wpol_text($name, $value='', $title='', $attrs=array()) {	
    return wpol_input($name, $value, $title, 'text', $attrs);
  }
  function wpol_file($name, $title='', $attrs=array()) {
    return wpol_input($name, '', $title, 'file', $attrs);
  }
  function wpol_hidden($name, $value='', $attrs=array()) {
    return wpol_input($name, $value, '', 'hidden', $attrs);
  }
  function wpol_checkbox($name, $value='', $title='', $attrs=array()) {	
    return wpol_input($name, $value, $title, 'checkbox', $attrs);
  }
  function wpol_radio($name, $value='', $title='', $attrs=array()) {
    return wpol_input($name, $value, $title, 'radio', $attrs);
  }
  function wpol_button($name, $value='', $title='', $attrs=array()) {
    return wpol_input($name, $value, $title, 'button', $attrs);
  }
  function wpol_textarea($name, $value='', $attrs=array()) {
    return wpol_tag('textarea', array_merge(array('name'=>$name, 'id'=>$name), $attrs), $value);
  }
  function wpol_submit($name, $value='', $title='', $attrs=array()) {
    return wpol_input($name, $value, $title, 'submit', $attrs);
  }
  function wpol_input($name, $value='', $title='', $type='text', $attrs=array()) {
    $attributes = array('type'=>$type, 'name'=>$name, 'id'=>$name, 'value'=>$value);				   
    if ($title != '') {
      $attributes = wpol_merge_attrs($attributes, array('title'=>$title));;
    }	
    
    return wpol_tag('input', wpol_merge_attrs($attributes, $attrs));
  }
  function wpol_combo($name, $items, $selected=false, $attrs=array()) {		
    $options = '';
    foreach ($items as $value=>$text) {
      if ($selected != false && $selected == $value) {
	$options .= wpol_tag('option', array('value'=>$value, 'selected'=>'selected'), $text);
      } else {
	$options .= wpol_tag('option', array('value'=>$value), $text);
      }
    }
    
    return wpol_tag('select', wpol_merge_attrs(array('name'=>$name, 'id'=>$name), $attrs), $options);
  }
  function wpol_label($text, $for=null, $suffix=':', $attrs=array()) {
    $attributes = $attrs;
    if ($for != null) {
      $attributes = wpol_merge_attrs($attributes, array('for'=>$for));		
    }
    
    return wpol_tag('label', $attributes, $text.$suffix);
  }
  
  // html helpers
  function wpol_merge_attrs($attributes, $newone) {
    if (count($attributes) == 0 && count($newone) == 0) {
      $merged = array();
    } else {
      if ($attributes != null && count($attributes) > 0) {
	$merged = array_merge($attributes, $newone);
      } else if ($newone != null && count($newone) > 0) {
	$merged = array_merge($newone, $attributes);
      }
    }
    
    return $merged;
  }
  function wpol_tag($name, $attrs=array(), $value=null) {
    $attributes = '';
    if (count($attrs) > 0) {
      foreach ($attrs as $attr_name=>$attr_value) {
	if (!empty($attr_value)) {
	  if ($attr_name == "selected") {
	    $attributes .= sprintf(' %s', $attr_value);
	  } else {
	    $attributes .= sprintf(' %s="%s"', $attr_name, $attr_value);
	  }
	}	
      }
    }
    
    if ($value != null || $name == 'textarea' || $name == 'ul' || $name == 'ol') {
      return sprintf('<%s%s>%s</%s>', $name, $attributes, $value, $name);
    } else {
      return sprintf('<%s%s />', $name, $attributes);
    }
  }
 }

// end of file
?>
