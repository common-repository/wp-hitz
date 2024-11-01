<?php
/*
Plugin Name: WP HITZ
Plugin URI: http://www.wphitz.com
Description: Awesome Hit Counter Provides Several Design Choices To Display The Total Number Of Hits Your WordPress Site Receives
Author: Market Domination Media
Version: 1.1
Author URI: http://www.marketdominationmedia.com
*/

function initialise_wphitz(){
	add_option("wphitz_showinfooter", "on");
}

register_activation_hook(__FILE__, 'initialise_wphitz');


function set_up_wphitz_menus(){
	add_menu_page('WP HITZ Settings', 'WP HITZ', "add_users", __FILE__."admin", 'wphitz_settings_menu', plugins_url('wphitz.png' , __FILE__ ));
}

add_action('admin_menu', 'set_up_wphitz_menus');


if (!function_exists('wphitz_textbox')) {
	function wphitz_textbox($name, $value="") {
		if (get_option($name)) { $value = get_option($name); }
		?>
		<input type="text" name="<?php echo esc_attr( $name ) ?>" size="30" value="<?php echo esc_attr( $value ) ?>" />
		<?php
	}
}

if (!function_exists('wphitz_checkbox')) {
	function wphitz_checkbox($name) {
		?>
		<?php if (get_option($name)): ?>
		<input type="checkbox" name="<?php echo esc_attr( $name ) ?>" checked="checked" />
		<?php else: ?>
		<input type="checkbox" name="<?php echo esc_attr( $name ) ?>" />
		<?php endif;
	}
}

function wphitz_dropdown($name, $data, $option="") {
   if (get_option($name)) { $option = get_option($name); }

   ?>
   <select name="<?php echo esc_attr( $name ) ?>">
   <?php

   if (is_vector($data)) {
      foreach ($data as $item) {
         if ($item == $option) {
            echo '<option selected="selected">' . $item . "</option>\n";
         }
         else {
            echo "<option>$item</option>\n";
         }
      }
   }

   else {
      foreach ($data as $value => $text) {
         if ($value == $option) {
            echo '<option value="' . $value . '" selected="selected">' . $text . "</option>\n";
         }
         else {
            echo '<option value="' . $value . '">' . "$text</option>\n";
         }
      }
   }

   ?>
   </select>
   <?php
}

if (!function_exists('is_vector')) {
   function is_vector( &$array ) {
      if ( !is_array($array) || empty($array) ) {
         return -1;
      }
      $next = 0;
      foreach ( $array as $k => $v ) {
         if ( $k !== $next ) return false;
         $next++;
      }
      return true;
   }
}


function wphitz_settings_menu(){
	echo '<div class="wrap"><center><h1>WP HITZ Settings</h1></center><hr>';
	echo '<form method="post" action="options.php">';
    wp_nonce_field('update-options');
	echo '<input type="hidden" name="action" value="update" /><input type="hidden" name="page_options" value="wphitz_hitcount, wphitz_showinfooter,wphitz_style,wphitz_size,wphitz_align,wphitz_canhazlink" />';
	echo '<label>Set current hit count:';
	wphitz_textbox("wphitz_hitcount");
	echo '</label><br/><br/>';
	echo '<label>Show hit counter in footer:';
	wphitz_checkbox("wphitz_showinfooter");
	echo '</label><br/><br/>';
	echo '<label>Style:';
	wphitz_dropdown("wphitz_style", array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10));
	echo '</label><br/><br/>';
	echo '<label>Size:';
	wphitz_dropdown("wphitz_size", array("small", "large"));
	echo '</label><br/><br/>';
	echo '<label>Align:';
	wphitz_dropdown("wphitz_align", array("left", "center", "right"));
	echo '</label><br/><br/>';
	echo '<label>Link My Site:';
	wphitz_checkbox("wphitz_canhazlink");
	echo '</label><br/><br/>';
	echo '<input type="submit" class="button" value="Save Changes" />';
	echo "</form>";
	echo "<hr /><h4>Style Previews</h4>";
	show_demo_counters();
	echo "</div>";
}

function wphitz_counter_html($demo = false, $dstyle = false){
	if(!is_home() && !$demo){
		return;
	}
	if(get_option("wphitz_showinfooter", "off") !== "on" && !$demo){
		return;
	}
	$hits = str_split(str_pad((string)get_option('wphitz_hitcount', '0'), 11, "0", STR_PAD_LEFT));
	if($dstyle){
		$style = $dstyle;
	} else {
		$style = get_option('wphitz_style', '1');
	}
	$size = get_option('wphitz_size', 'small');
	if($demo){
		$align = "left";
	} else {
		$align = get_option('wphitz_align', 'left');
	}
	$out = '<div style="';
	
	if($align == "left"){
		$out .= "";
	} elseif ($align == "right") {
		$out .= "width:21%;float:right;";
	} elseif($align == "center") {
		$out .= "width:55%;float:right;";
	}
	
	$out .='"> <div style="position:absolute;"><img src="'.plugins_url('styles/'.$size."/".$style.'/counterBG.png' , __FILE__ ).'" style="position:absolute;z-index:99;"';
	if(get_option("wphitz_canhazlink", "off") === "on"){
		$out .= ' usemap="#himgmap" />';
		$out .= '<map name="himgmap"><area shape="rect" coords="10,34,195,50" href="http://www.marketdominationmedia.com" alt="WP HITZ by Market Domination Media"></map>';
	} else {
		$out .= ' />';
	}	
	
	$first = true;
	foreach ($hits as $digit) {
		$out .= "<img src='".plugins_url('styles/'.$size."/".$style."/".$digit.'.png' , __FILE__ )."' alt='".$digit."' style='position:relative;z-index:100;";
		if($first){
			$out .= "padding-left:3px;padding-top:3px;' />";
			$first = false;
		} else {
			$out .= "padding-left:1px;padding-top:3px;' />";
		}
	}
	
	$out .= '</div></div>';	
	return $out;
}

function show_demo_counters(){
	$out = "";
	for($i = 1; $i <= 10; $i++){
		$out .= "Style ".$i.":<br/>" .wphitz_counter_html(true, $i)."<br /><br /><br /><br /><br />";
	}
	echo $out;
}

function wphitz_footer_counter() {
    echo wphitz_counter_html();
}
add_action('wp_footer', 'wphitz_footer_counter');

function wphitz_show_counter(){
	$out = wphitz_counter_html();
	return $out;
}

add_shortcode("wphitz", "wphitz_show_counter");

function wphitz_count_hit() {
	$stats = (int)get_option('wphitz_hitcount', '0');
	$stats++;
	update_option('wphitz_hitcount', $stats);
}
add_action('wp', 'wphitz_count_hit');



?>