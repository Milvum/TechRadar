<?php
/*
Plugin Name: Tech Radar
Version: 1.0.0
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
  function compare_name($a, $b)
  {
    return strnatcmp($a['name'], $b['name']);
  }
  $items = get_option('tech_radar_items');
  usort($items, 'compare_name');
  foreach($items as $item) {
    ?>
    <div class='tr'>
      <span class='td'><?php echo $item['name']; ?></span>
      <span class='td'><?php echo $item['x']; ?></span>
      <span class='td'><?php echo $item['y']; ?></span>
      <button class='remove-button' data-name='<?php echo $item['name']?>'>X</button>
    </div>
    <?php
  }
  ?>
  <div class='tr spacing'></div>
    <form class="tr" method="post" action="add_item">
      <span class="td"><input type="text" autocomplete="off" name="name" /></span>
      <span class="td"><input type="text" autocomplete="off" name="x" /></span>
      <span class="td"><input type="text" autocomplete="off" name="y" /></span>
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
function addLabels($sectors) {
  $out = '<div class="labels embed-title">';
  foreach ($sectors as $value) {
    $title = strtoupper($value);
    $out .= "<a class='label-$value' onclick=\"select('$value');\">
      <i class='far fa-circle'></i>
      <span>$title</span>
    </a>";
  }
  $out .= '</div>';
  return $out;
}

// Helper function to draw the different quadrants of the radar.
function addQuadrants($sectors) {
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

function getSector($sectors, $x, $y) {
  // Sectors are defined: [top-left, top-right, bot-left, bot-right];
  $var = ($x > 50) + 2*($y > 50);
  return $sectors[$var];
}

// Helper function to draw the items in the radar.
function addItems($sectors) {
  $items = get_option('tech_radar_items');

  $out = '<div class="items">';
  foreach($items as $item) {
    $x = $item['x'];
    $y = $item['y'];
    $name = $item['name'];
    $sector = getSector($sectors, $x, $y);
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
    $out .= addLabels($sectors);
    $out .= '<div class="mask"><div class="stage">';
    $out .= addQuadrants($sectors);
    // TODO: Allow users to define circle titles.
    $out .= '<div class="circle circle-inner"><div class="legend"></div></div>';
    $out .= '<div class="circle circle-outer"><div class="legend"></div></div>';
    $out .= '<div class="line-x"></div>';
    $out .= '<div class="line-y"></div>';
    $out .= addItems($sectors);
    $out .= '</div></div></div></div>';
  return $out;
}

add_shortcode('techradar', 'techradar_display');
  
add_action( 'wp_ajax_remove_item', 'remove_item' );

function remove_item() {
	global $wpdb;
  $name = $_POST['name'];
  
  $items = get_option('tech_radar_items');
  if ($items) {
    $key = array_search($name, array_column($items, 'name'));
    array_splice($items, $key, 1);
    update_option('tech_radar_items', $items, 'no');
    echo 'Removed '.$name.' at position '.$key.'.';
  }

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'admin_footer', 'remove_item_javascript' );

function remove_item_javascript() {
  
  ?>
	<script type="text/javascript" >
	jQuery(document).on('click', 'button.remove-button', function($) {
    
    var name = jQuery(this).data('name');
		var data = {
			'action': 'remove_item',
			'name': name,
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
  
add_action( 'wp_ajax_add_item', 'add_item' );

function add_item() {
	global $wpdb;
  $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
  $x = filter_var($_POST['x'], FILTER_SANITIZE_NUMBER_FLOAT);
  $y = filter_var($_POST['y'], FILTER_SANITIZE_NUMBER_FLOAT);
  $items = get_option('tech_radar_items');
  if (!$items) {
    $items = array();
    array_push($items, array("name" => $name, "x" => $x, "y" => $y));
    add_option('tech_radar_items', $items, '', 'no');
  } else {
    array_push($items, array("name" => $name, "x" => $x, "y" => $y));
    update_option('tech_radar_items', $items, 'no');
  }
  
  echo 'Added '.$name;

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'admin_footer', 'add_item_javascript' );

function add_item_javascript() {
  
  ?>
  <script type="text/javascript" >
  jQuery(document).on('click', 'button.add-button', function($) {
    event.preventDefault();
		var data = {
			'action': 'add_item',
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
