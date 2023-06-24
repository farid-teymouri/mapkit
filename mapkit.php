<?php

/**
 * Plugin Name: Mapkit
 * Description: ساختار دلخواه برای یک سایت مپ سفارشی شده
 * Plugin URI: https://asandev.com
 * Author: Farid Teymouri
 * Author URI: https://asandev.com
 * License: GNU General Public License v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mapkit
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Tested up to: 6.1
 * Version: 1.2.21
 */

// // Include mpk-functions.php, use require_once to stop the script if mpk-functions.php is not found
// require_once plugin_dir_path(__FILE__) . 'includes/mpk-functions.php';
// // Include mpk-options.php, use require_once to stop the script if mpk-options.php is not found
// require_once plugin_dir_path(__FILE__) . 'includes/mpk-options.php';


// enqueue css and js files




/**
 * The base class responsible for loading the plugin data as well as any plugin subclasses and additional functions
 */
class mapkit_Class
{
    public $link_sawing_options;
    public $sections, $functions;
    /**
     * Get options from DB, load subclasses & hooks
     */
    public function __construct()
    {
        $this->include_subclasses();
    }


    /**
     * Include back-end classes and set their instances
     */
    function include_subclasses()
    {

        // WP_List_Table needed for post types & taxonomies editors
        if (!class_exists('WP_List_Table')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }
        $classes = array(
            'core'  => array(
                'admin-functions'    => 'mapkit_Admin_Functions',
            ),
        );
        // Load classes and set-up their instances
        foreach ($classes as $class_type => $classes_array) {
            foreach ($classes_array as $class => $class_name) {
                // Include mpk-functions.php, use require_once to stop the script if mpk-functions.php is not found
                $filename = plugin_dir_path(__FILE__) . "includes/mpk-functions.php";
                if (file_exists($filename)) {
                    require_once $filename;
                    if ($class_name) {
                        $this->functions[$class] = new $class_name();
                    }
                }
            }
        }
    }
}
new mapkit_Class();


class mapkit_activation
{

    public static function plugin_activated()
    {
        global $wpdb;
        $db_option =  $wpdb->query("SELECT * FROM " . (false ? $wpdb->base_prefix : $wpdb->prefix) . "options WHERE option_name ='mapkit.list' LIMIT 1");
        if (!$db_option) {
            add_option(
                "mapkit.list",
                array(
                    "0" => array(
                        'id' => 1,
                        "parent_id" => -1,
                        "slug" => "sitemap",
                        "level" => 0,
                    )
                )
            );
        }
        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap')) {
            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap', 0777, true);
        }
    }
}

register_activation_hook(__FILE__, array('mapkit_activation', 'plugin_activated'));
