<?php
/**
 * Plugin Name: zM Contact
 * Plugin URI:
 * Description: Adds a Contact Post Type (with form submission) geared towards developers/designers, with the following fields: First Name (text), Last Name (text), Subject (text), Category (select box), Budget (select box), Email (text), Phone (text), Website (text), Message (textarea).
 * Version: 0.1-alpha
 * Author: Zane M. Kolnik
 * Author URI: http://zanematthew.com/
 * License: GPL
 */


require_once plugin_dir_path( __FILE__ ) . 'lib/zm-easy-cpt/functions.php';
$z = New zMCore;
$z->zm_easy_cpt_reqiure( plugin_dir_path(__FILE__) );


add_action('wp_head','pluginname_ajaxurl');
function pluginname_ajaxurl() { ?>
    <script type="text/javascript">var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';</script>
<?php }