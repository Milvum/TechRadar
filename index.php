<?php
/*
Plugin Name: Tech Radar
Version: 1.1.6
Plugin URI: t.b.t.
Author: Milvum, Blue Harvest
Author URI: https://milvum.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Description: A simple visual tool to showcase technologies in a project or organisation. Full credits to https://blueharvest.io/ for the original design.
*/
add_action('admin_init', 'tech_radar_init');
add_action('admin_menu', 'tech_radar_add_page');

add_action('admin_init', 'techradar_scripts');

function techradar_scripts()
{
    wp_enqueue_style('techradar-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_style('settings-style', plugin_dir_url(__FILE__) . 'settings.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('techradar-js', plugin_dir_url(__FILE__) . 'techradar.js');
}

function tech_radar_validate($input)
{
  foreach($input as $item) {
    $item['name'] = sanitize_text_field($item['name']);
    $item['x'] = intval(sanitize_key($item['x']));
    $item['y'] = intval(sanitize_key($item['y']));
  }
  return $input;
}

// Init plugin options to white list our options
function tech_radar_init()
{
  register_setting('tech_radar_options', 'tech_radar_items', 'tech_radar_validate');

}

// Add settings page
function tech_radar_add_page()
{
    add_options_page('Tech Radar Builder', 'Tech Radar', 'manage_options', 'tech_radar', 'tech_radar_do_page');
}

// Draw the settings page
function tech_radar_do_page()
{
  wp_enqueue_style('settings-style', plugin_dir_url(__FILE__) . 'settings.css');
  ?>
  <h1>Techradar Settings</h1>
  <hr/>
  <h2>Techradar Items</h2>
  <text>
    The tech radar contains four quadrants. <br/>
    These are the top-left, top-right, bottom-left, and bottom-right. <br/>
    Colors for items are assigned based on their location. <br/>
    To avoid cutoffs, be careful with values close to the midway points between quadrants (values around 50). <br/>
    <br/>
    Display the radar on a page by using a shortcode block with [techradar]. <br/>
  </text>
  <div style='float: left'>
    <h3>Items</h3>
    <div class='table'>
      <div class='tr header'>
        <span class='td'>Name</span>
        <span class='td'>Horizontal position in %</span>
        <span class='td'>Vertical position in %</span>
        <span/>
      </div>
  <?php
  $items = get_option('tech_radar_items');
  if($items) {
    foreach($items as $key=>$item) {
      $name = esc_attr($item['name']);
      $x = esc_attr($item['x']);
      $y = esc_attr($item['y']);
      ?>
      <div class='tr' data-index='<?php echo esc_attr($key); ?>' data-x='<?php echo $x; ?>' data-y='<?php echo $y; ?>'>
        <span class='td'><?php echo $name; ?></span>
        <span class='td'><input class='coordinate' type="number" data-dir='x' value=<?php echo $x; ?>></input></span>
        <span class='td'><input class='coordinate' type="number" data-dir='y' value=<?php echo $y; ?>></input></span>
        <button class='remove-button' data-index='<?php echo esc_attr($key); ?>'>X</button>
      </div>
      <?php
    }
  }
  ?>
  <div class='tr spacing'></div>
    <form class="tr" method="post" action="tech_radar_add_item">
      <span class="td"><input type="text" autocomplete="off" name="name" /></span>
      <span class="td"><input type="number" autocomplete="off" name="x" /></span>
      <span class="td"><input type="number" autocomplete="off" name="y" /></span>
      <button class='add-button'>+</button>
    </form>
  </div>
</div>
<div style='float: left; margin-left: 20px;'>
  <h3 class='TechRadar'>Preview</h3>
  <?php echo techradar_display(); ?>
</div>
  <?php
}

add_action('init', 'techradar_scripts');

// Helper function to draw the labels.
function tech_radar_add_labels($sectors) {
  $out = '<div class="labels embed-title">';
  foreach ($sectors as $value) {
    $title = strtoupper($value);
    $out .= "<a class='label-$value' onclick=\"select('$value');\">
      <span>$title</span>
    </a>";
  }
  $out .= '</div>';
  return $out;
}

// Helper function to draw the different quadrants of the radar.
function tech_radar_add_quadrants($sectors) {
  $out = '';
  foreach ($sectors as $value) {
    $title = strtoupper($value);
    $out .= "<div class='quadrant quadrant-$value'>
      <div class='border'></div>
      <div class='quadrant-title embed-title'>
        <span>$title</span>
      </div>
    </div>
    ";
  }
  return $out;
}

function tech_radar_get_sector($sectors, $x, $y) {
  // Sectors are defined: [top-left, top-right, bot-left, bot-right];
  $var = ($x > 50) + 2*($y > 50);
  return $sectors[$var];
}

// Helper function to draw the items in the radar.
function tech_radar_add_items($sectors) {
  $items = get_option('tech_radar_items');
  $out = '<div class="items">';
  foreach($items as $item) {
    $x = esc_attr($item['x']);
    $y = esc_attr($item['y']);
    $name = esc_attr($item['name']);
    $sector = tech_radar_get_sector($sectors, $x, $y);
    $out .= "<div class='Item is-$sector' style='left: $x%; top: $y%'>
          <div class='hit'></div>
          <div class='target'>
            <div class='name'>
              <span>$name</span>
            </div>
          </div>
        </div>";
  }
  $out .= '</div>';
  return $out;
}

// Draw the radar.
function techradar_display()
{
    // TODO: Allow users to define the sectors.
    $sectors = array('frameworks', 'languages', 'platforms', 'tools');
    wp_enqueue_style('techradar-style1', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('techradar-js', plugin_dir_url(__FILE__) . 'techradar.js');

    $out = '<div class="TechRadar" id="TechRadar">';
    $out .= tech_radar_add_labels($sectors);
    $out .= '<div class="mask"><div class="stage">';
    $out .= tech_radar_add_quadrants($sectors);
    $out .= '<div class="circle circle-inner"><div class="legend"></div></div>';
    $out .= '<div class="circle circle-outer"><div class="legend"></div></div>';
    $out .= '<div class="line-x"></div>';
    $out .= '<div class="line-y"></div>';
    $out .= tech_radar_add_items($sectors);
    $out .= '</div></div></div></div>';
  return $out;
}

add_shortcode('techradar', 'techradar_display');
  
add_action( 'wp_ajax_tech_radar_remove_item', 'tech_radar_remove_item' );

function tech_radar_remove_item() {
  global $wpdb;
  $key = sanitize_key($_POST['index']);
  $items = get_option('tech_radar_items');
  if ($items) {
    array_splice($items, $key, 1);
    update_option('tech_radar_items', $items, 'no');
    echo 'Removed item at position '.$key.'.';
  }

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'admin_footer', 'tech_radar_remove_item_javascript' );

function tech_radar_remove_item_javascript() {
  
  ?>
	<script type="text/javascript" >
	jQuery(document).on('click', 'button.remove-button', function($) {
    
    var index = jQuery(this).data('index');
		var data = {
			'action': 'tech_radar_remove_item',
			'index': index,
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
      console.log(response);
      // This is rather data inefficient, but suffices for now.
      window.location.reload();
		});
	});
  </script> <?php
}
  
add_action( 'wp_ajax_tech_radar_add_item', 'tech_radar_add_item' );

function tech_radar_add_item() {
	global $wpdb;
  $name = sanitize_text_field($_POST['name']);
  $x = intval(sanitize_key($_POST['x']));
  $y = intval(sanitize_key($_POST['y']));
  $items = get_option('tech_radar_items');
  if (!$items) {
    $items = array();
    array_push($items, array("name" => $name, "x" => $x, "y" => $y));
    add_option('tech_radar_items', $items, '', 'no');
  } else {
    array_push($items, array("name" => $name, "x" => $x, "y" => $y));
  }
  update_option('tech_radar_items', $items, 'no');  
  echo 'Added '.$name;

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'admin_footer', 'tech_radar_add_item_javascript' );

function tech_radar_add_item_javascript() {
  
  ?>
  <script type="text/javascript" >
  jQuery(document).on('click', 'button.add-button', function($) {
    event.preventDefault();
		var data = {
			'action': 'tech_radar_add_item',
      'name': document.forms[0].name.value,
      'x': document.forms[0].x.value,
      'y': document.forms[0].y.value,
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
      console.log(response);
      // This is rather data inefficient, but suffices for now.
      window.location.reload();
		});
	});
  </script> <?php
}
  
add_action( 'wp_ajax_tech_radar_update_item', 'tech_radar_update_item' );

function tech_radar_update_item() {
	global $wpdb;
  $key = intval(sanitize_key($_POST['index']));
  $x = intval(sanitize_key($_POST['x']));
  $y = intval(sanitize_key($_POST['y']));
  $items = get_option('tech_radar_items');
  if (!$items) {
    return;
  } 
  $items[$key] = array("name" => $items[$key]['name'], "x" => $x, "y" => $y);
  update_option('tech_radar_items', $items, 'no');
  
  echo 'Updated item at '.$key;
  
	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'admin_footer', 'tech_radar_update_item_javascript' );

function tech_radar_update_item_javascript() {
  
  ?>
  <script type="text/javascript" >
  jQuery(document).on('blur', 'input.coordinate', function($) {
    var data = jQuery(this).parent().parent().data();
    var value = jQuery(this).val();
    var dir = jQuery(this).data('dir');
    data[dir] = parseInt(value);
		var data = {
			'action': 'tech_radar_update_item',
      'index': data['index'],
      'x': data['x'],
      'y': data['y'],
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
      console.log(response);
      // This is rather data inefficient, but suffices for now.
      window.location.reload();
		});
	});
  </script> <?php
}
