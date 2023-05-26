<?php
/*
 * Plugin Name: Copyright protection plugin
 * Version: 1.0.0
 * Description: This is simple lightweight plugin that stops users from stealing/copying your content. Site users will recieve administrator defined disclaimer instead of page content when trying to copy the content.
 * License: MIT
 *
*/
if (!defined('ABSPATH')){
  die('Something is wrong');
}

//activation function sets up wp_co_pro_disclaimer table to save all disclaimers 
function wpb_copy_protection_plugin_activation(){
  global $wpdb;
  $t_name = 'co_pro_disclaimer';
  $wp_track_table = $wpdb->prefix . "$t_name";
  $charset_collate = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE IF NOT EXISTS $wp_track_table ( 
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  disclaimer text NOT NULL,
  PRIMARY KEY  (id)
  ) $charset_collate;";
  require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
  dbDelta($sql);
  $default_id = 0;
  $default_dis = 'The content you are trying to copy is intellectual property of the site owner';
  $wpdb->insert($wp_track_table,array('id'=>$default_id,'disclaimer'=>$default_dis));
}
function wpb_copy_protection_plugin_deactivation(){
  global $wpdb;
  $t_name = 'co_pro_disclaimer';
  $wp_track_table = $wpdb->prefix . "$t_name";
  $sql = "DROP TABLE IF EXISTS $wp_track_table ;";
  $wpdb->query($sql);
}


function wpb_copy_text_blocker(){
  if(!is_admin()){
    global $wpdb;
    $t_name = $wpdb->prefix . 'co_pro_disclaimer';
    $dis_message = $wpdb->get_var("SELECT disclaimer FROM $t_name ORDER BY id DESC LIMIT 1");
    
    $cm_de = str_replace('\\','',$dis_message);
    echo '<script defer>
    document.addEventListener(\'copy\', (event) => {
  const disclaimer = \'' . $cm_de . '\';
  event.clipboardData.setData(\'text/plain\', disclaimer);
  event.preventDefault();});</script>';
  }
}

function wpb_custom_config_submenu_page(){
  add_submenu_page(
    'tools.php',
    'Copy Blocker',
    'Copyright protection dashboard',
    'manage_options',
    'copy-block-menu',
    'wpb_just_support_fn_hsafksa_haedzgkdagk_krfhuasdfb'
  );
}

function wpb_just_support_fn_hsafksa_haedzgkdagk_krfhuasdfb(){

  global $wpdb;
  $t_name = $wpdb->prefix . 'co_pro_disclaimer';
  $curr_message = $wpdb->get_var("SELECT disclaimer FROM $t_name ORDER BY id DESC LIMIT 1");
  $cm_de = str_replace('\\','',$curr_message);
  echo "<div class=\"wrap\">
  <h2>Just testing my custom dashboard</h2>
  <p><strong>Current Disclaimer:</strong> $cm_de</p>
      <form method=\"post\" width=\"50vw\" >
  <p>Unsaved disclaimer:</p>
  <input name=\"disc\" class=\"regular-text ltr\" value= \"$cm_de\">
  <br>
  <br>
  <select name=\"update_options\" >
  <option value=\"reset\">RESET</option>
  <option selected value=\"update\">UPDATE</option>
  </select>
  <br>
  <br>
         <button type=\"submit\" class=\"button button-primary\">APPLY</button> 
    </form>
  </div>";

}
function wpb_push_the_copy_blocker_message_change(){
  if (is_admin()&&isset($_POST['disc'])&&isset($_SERVER['HTTP_REFERER'])&&str_contains($_SERVER['HTTP_REFERER'],'wp-admin/tools.php')){
    $changed_v = str_replace( '"','\\"', $_POST['disc']);
    global $wpdb;
    $t_name = $wpdb->prefix . 'co_pro_disclaimer';
    $wpdb->insert($t_name,array('disclaimer'=>$changed_v));
    if (strcmp($_POST['update_options'],"reset")==0) {
      #It may look like this string is vulnerable to SQL injection, but because no user input is accepted here, the code is safe
      $wpdb->query("DELETE FROM " . $t_name . " WHERE id != 1;");
    }
  }

}
add_action('init','wpb_push_the_copy_blocker_message_change');
add_action('admin_menu','wpb_custom_config_submenu_page');
add_action('wp_head','wpb_copy_text_blocker');
register_activation_hook(__FILE__,'wpb_copy_protection_plugin_activation');
register_deactivation_hook(__FILE__,'wpb_copy_protection_plugin_deactivation');
?>
