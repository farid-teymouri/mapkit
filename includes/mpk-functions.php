<?php

/**
 * Additional functions related to WordPress Admin Dashboard UI
 */
class mapkit_Admin_Functions
{
    public $menu_name, $message = array('error' => 0, "message" => "", "data" => array()), $mapkit;

    public function __construct()
    {
        if (!get_option('mapkit.list')) add_option("mapkit.list", array("0" => array('id' => 1, "parent_id" => -1, "slug" => "sitemap",)));
        if (get_option('mapkit.list')) $this->mapkit = get_option('mapkit.list');
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_init', array($this, 'init'));
    }
    public function load_admin_sources()
    {
        wp_enqueue_style('style', plugin_dir_url(__DIR__) . '/mapkit.css', false, '1.0.0');
        wp_enqueue_style('semantic', plugin_dir_url(__DIR__) . '/semantic-ui/semantic.rtl.min.css', false, '1.0.0');
        wp_enqueue_script('j-script', plugin_dir_url(__DIR__) . '/mapkit.js', false, '1.0.0');
    }
    /**
     * Add "Tools -> Link Sawing" to the admin sidebar menu
     */
    public function add_menu_page()
    {
        $this->menu_name = add_management_page('Mapkit', 'Mapkit', 'manage_options', 'mapkit', array($this, 'display_section',));

        if (strpos($_SERVER['REQUEST_URI'], 'mapkit') !== false) {
            add_action('admin_init', array($this, 'isset_actions'));
            add_action('admin_init', array($this, 'load_admin_sources'));
        }
    }

    public function isset_actions()
    {

        // initialing an empty array to get bigger id
        $majorMax = array();

        if (isset($_POST['bypage'])) {
            // get value of page selected by admin
            $page_id = filter_input(
                INPUT_POST,
                'page_id',
                FILTER_UNSAFE_RAW,
            );
            $page_radio = filter_input(
                INPUT_POST,
                'page_radio',
                FILTER_UNSAFE_RAW,
            );
            if ($page_id > 0) {

                // if we have more than 1 child directory in root
                if (count($this->mapkit) > 1) {
                    // iterate array of directories
                    foreach ($this->mapkit as $i => $map) {
                        // push their id to sort bigger id
                        if (!in_array($map['id'], $majorMax)) array_push($majorMax, $map['id']);
                    }
                    // that a new array from existing pages
                    $add = array(
                        "id" =>  intval(max($majorMax) + 1),
                        "WP_id" => $page_id,
                        "slug" => urldecode(get_post_field('post_name', $page_id)),
                    );
                    // iterate array of directories
                    foreach ($this->mapkit as $map) {
                        // if new directory is created before at this level
                        if ($map['WP_id'] == $add['WP_id'] && $map['slug'] == $add['slug']) {
                            // check new array isn't inside message data
                            if (!in_array($add, $this->message['data'])) {
                                // push new array in message data
                                array_push($this->message['data'], $add,);
                                // make enable message error 
                                $this->message['error'] = 1;
                                // generate message error
                                $this->message['message'] = "با عرض پوزش در سطح مورد نظر ، دایرکتوری {$add['slug']} از قبل ساخته شده.";
                            }
                        }
                    }
                    // generate a new array form new one and exsiting before in db
                    if (!in_array($add, $this->mapkit)) array_push($this->mapkit, $add);
                    // if there is no dublicated direcotries at same level ,  make update new one
                    if (!$this->message['error']) update_option("mapkit.list", $this->mapkit);
                    // generate costructor php codes
                    $content = '<?php ';
                    $content .= '$xmlstr = <<<XML
                    <?xml version="1.0" encoding="UTF-8"?>
                    <sitemapindex xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>
                    XML;';
                    $content .= '$xml = new SimpleXMLElement($xmlstr);';
                    // iterate array of directories
                    foreach ($this->mapkit as $map) {
                        if ($map['id'] > 1) {
                            $content .= '$sitemap = $xml->addChild("sitemap");';
                            $content .= '$sitemap->addChild("loc", "https://' . $_SERVER['SERVER_NAME'] . '/sitemap/' . $map['slug'] . '/");';
                        }
                    }
                    $content .= 'Header("Content-type: application/xml");';
                    $content .= 'print($xml->asXML());';
                    $content .= ' ?>';
                    $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/index.php', 'w');
                    fwrite($file, $content);
                    fclose($file);
                }
                // if there is no any child directories inside root folder
                if (count($this->mapkit) == 1 && !$this->message['error']) {
                    // try to make a new directory inside root folder
                    update_option(
                        "mapkit.list",
                        array(
                            "0" => array(
                                'id' => 1,
                                "slug" => "sitemap",
                            ),
                            array(
                                "id" => 2,
                                "WP_id" => $page_id,
                                "slug" => urldecode(get_post_field('post_name', $page_id)),
                            )
                        )
                    );
                    if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap')) {
                        mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap', 0777, true);
                    }
                    $content = '<?php ';
                    $content .= '$xmlstr = <<<XML
                        <?xml version="1.0" encoding="UTF-8"?>
                        <sitemapindex xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>
                        XML;';
                    $content .= '$xml = new SimpleXMLElement($xmlstr);';
                    $content .= '$sitemap = $xml->addChild("sitemap");';
                    $content .= '$sitemap->addChild("loc", "https://' . $_SERVER['SERVER_NAME'] . '/sitemap/' . urldecode(get_post_field('post_name', $page_id)) . '/");';
                    $content .= 'Header("Content-type: application/xml");';
                    $content .= 'print($xml->asXML());';
                    $content .= ' ?>';
                    $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/index.php', 'w');
                    fwrite($file, $content);
                    fclose($file);
                }

                switch ($page_radio) {
                    case "childs-and-self":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(get_post_field('post_name', $page_id)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(get_post_field('post_name', $page_id)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/page-functions.php";';
                        $content .= 'childs_and_self(' . $page_id . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(get_post_field('post_name', $page_id)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                    case "just-childs":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(get_post_field('post_name', $page_id)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(get_post_field('post_name', $page_id)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/page-functions.php";';
                        $content .= 'just_childs(' . $page_id . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(get_post_field('post_name', $page_id)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);

                        break;
                    case "just-self":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(get_post_field('post_name', $page_id)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(get_post_field('post_name', $page_id)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/page-functions.php";';
                        $content .= 'just_self(' . $page_id . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(get_post_field('post_name', $page_id)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                }
            }
        }


        if (isset($_POST['customdir'])) {
            // get value of page selected by admin
            $customfeild = filter_input(
                INPUT_POST,
                'customfeild',
                FILTER_UNSAFE_RAW,
            );
            $custom_radio = filter_input(
                INPUT_POST,
                'custom_radio',
                FILTER_UNSAFE_RAW,
            );

            if ($customfeild) {  // if we have more than 1 child directory in root
                if (count($this->mapkit) > 1) {
                    // iterate array of directories
                    foreach ($this->mapkit as $i => $map) {
                        // push their id to sort bigger id
                        if (!in_array($map['id'], $majorMax)) array_push($majorMax, $map['id']);
                    }
                    // that a new array from existing pages
                    $add = array(
                        "id" =>  intval(max($majorMax) + 1),
                        "WP_id" => -1,
                        "slug" => urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)),
                    );
                    // iterate array of directories
                    foreach ($this->mapkit as $map) {
                        // if new directory is created before at this level
                        if ($map['slug'] == $add['slug']) {
                            // check new array isn't inside message data
                            if (!in_array($add, $this->message['data'])) {
                                // push new array in message data
                                array_push($this->message['data'], $add,);
                                // make enable message error 
                                $this->message['error'] = 1;
                                // generate message error
                                $this->message['message'] = "با عرض پوزش در سطح مورد نظر ، دایرکتوری {$add['slug']} از قبل ساخته شده.";
                            }
                        }
                    }
                    // generate a new array form new one and exsiting before in db
                    if (!in_array($add, $this->mapkit)) array_push($this->mapkit, $add);
                    // if there is no dublicated direcotries at same level ,  make update new one
                    if (!$this->message['error']) update_option("mapkit.list", $this->mapkit);
                    // generate costructor php codes
                    $content = '<?php ';
                    $content .= '$xmlstr = <<<XML
                    <?xml version="1.0" encoding="UTF-8"?>
                    <sitemapindex xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>
                    XML;';
                    $content .= '$xml = new SimpleXMLElement($xmlstr);';
                    // iterate array of directories
                    foreach ($this->mapkit as $map) {
                        if ($map['id'] > 1) {
                            $content .= '$sitemap = $xml->addChild("sitemap");';
                            $content .= '$sitemap->addChild("loc", "https://' . $_SERVER['SERVER_NAME'] . '/sitemap/' . $map['slug'] . '/");';
                        }
                    }
                    $content .= 'Header("Content-type: application/xml");';
                    $content .= 'print($xml->asXML());';
                    $content .= ' ?>';
                    $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/index.php', 'w');
                    fwrite($file, $content);
                    fclose($file);
                }
                // if there is no any child directories inside root folder
                if (count($this->mapkit) == 1 && !$this->message['error']) {
                    // try to make a new directory inside root folder
                    update_option(
                        "mapkit.list",
                        array(
                            "0" => array(
                                'id' => 1,
                                "slug" => "sitemap",
                            ),
                            array(
                                "id" => 2,
                                "WP_id" => -1,
                                "slug" => urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)),
                            )
                        )
                    );
                    if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap')) {
                        mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap', 0777, true);
                    }
                    $content = '<?php ';
                    $content .= '$xmlstr = <<<XML
                    <?xml version="1.0" encoding="UTF-8"?>
                    <sitemapindex xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>
                    XML;';
                    $content .= '$xml = new SimpleXMLElement($xmlstr);';
                    $content .= '$sitemap = $xml->addChild("sitemap");';
                    $content .= '$sitemap->addChild("loc", "https://' . $_SERVER['SERVER_NAME'] . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)) . '/");';
                    $content .= 'Header("Content-type: application/xml");';
                    $content .= 'print($xml->asXML());';
                    $content .= ' ?>';
                    $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/index.php', 'w');
                    fwrite($file, $content);
                    fclose($file);
                }
                switch ($custom_radio) {
                    case "self-and-every-pages":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/custom-functions.php";';
                        $content .= 'self_and_every_pages(' . $customfeild . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                    case "every-pages":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/custom-functions.php";';
                        $content .= 'every_pages(' . $customfeild . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                    case "self-and-every-posts":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/custom-functions.php";';
                        $content .= 'self_and_every_posts(' . $customfeild . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                    case "every-posts":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/custom-functions.php";';
                        $content .= 'every_posts(' . $customfeild . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                    case "self-and-every-cats":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/custom-functions.php";';
                        $content .= 'self_and_every_cats(' . $customfeild . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                    case "every-cats":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/custom-functions.php";';
                        $content .= 'every_cats(' . $customfeild . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                    case "self-and-every-tags":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/custom-functions.php";';
                        $content .= 'self_and_every_tags(' . $customfeild . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                    case "every-tags":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/custom-functions.php";';
                        $content .= 'every_tags(' . $customfeild . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                    case "just-self":
                        if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)))) {
                            mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)), 0777, true);
                        }
                        $content = '<?php ';
                        $content .= 'require_once("' . $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php");';
                        $content .= 'require "' . plugin_dir_path(__FILE__) . 'dynamic-functions/custom-functions.php";';
                        $content .= 'just_self(' . $customfeild . ');';
                        $content .= ' ?>';
                        $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeild)) . '/index.php', 'w');
                        fwrite($file, $content);
                        fclose($file);
                        break;
                }
            }
        }

        if (isset($_POST['customdirstatic'])) {
            $stXml = filter_input(
                INPUT_POST,
                'stXml',
                FILTER_UNSAFE_RAW,
            );
            $customfeildstatic = filter_input(
                INPUT_POST,
                'customfeildstatic',
                FILTER_UNSAFE_RAW,
            );

            if ($customfeildstatic) {
                if (count($this->mapkit) > 1) {
                    // iterate array of directories
                    foreach ($this->mapkit as $i => $map) {
                        // push their id to sort bigger id
                        if (!in_array($map['id'], $majorMax)) array_push($majorMax, $map['id']);
                    }
                    // that a new array from existing pages
                    $add = array(
                        "id" =>  intval(max($majorMax) + 1),
                        "WP_id" => -1,
                        "slug" => urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeildstatic)),
                    );
                    // iterate array of directories
                    foreach ($this->mapkit as $map) {
                        // if new directory is created before at this level
                        if ($map['slug'] == $add['slug']) {
                            // check new array isn't inside message data
                            if (!in_array($add, $this->message['data'])) {
                                // push new array in message data
                                array_push($this->message['data'], $add,);
                                // make enable message error 
                                $this->message['error'] = 1;
                                // generate message error
                                $this->message['message'] = "با عرض پوزش در سطح مورد نظر ، دایرکتوری {$add['slug']} از قبل ساخته شده.";
                            }
                        }
                    }
                    // generate a new array form new one and exsiting before in db
                    if (!in_array($add, $this->mapkit)) array_push($this->mapkit, $add);
                    // if there is no dublicated direcotries at same level ,  make update new one
                    if (!$this->message['error']) update_option("mapkit.list", $this->mapkit);
                    // generate costructor php codes
                    $content = '<?php ';
                    $content .= '$xmlstr = <<<XML
                    <?xml version="1.0" encoding="UTF-8"?>
                    <sitemapindex xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>
                    XML;';
                    $content .= '$xml = new SimpleXMLElement($xmlstr);';
                    // iterate array of directories
                    foreach ($this->mapkit as $map) {
                        if ($map['id'] > 1) {
                            $content .= '$sitemap = $xml->addChild("sitemap");';
                            $content .= '$sitemap->addChild("loc", "https://' . $_SERVER['SERVER_NAME'] . '/sitemap/' . $map['slug'] . '/");';
                        }
                    }
                    $content .= 'Header("Content-type: application/xml");';
                    $content .= 'print($xml->asXML());';
                    $content .= ' ?>';
                    $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/index.php', 'w');
                    fwrite($file, $content);
                    fclose($file);
                }
                // if there is no any child directories inside root folder
                if (count($this->mapkit) == 1 && !$this->message['error']) {
                    // try to make a new directory inside root folder
                    update_option(
                        "mapkit.list",
                        array(
                            "0" => array(
                                'id' => 1,
                                "slug" => "sitemap",
                            ),
                            array(
                                "id" => 2,
                                "WP_id" => -1,
                                "slug" => urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeildstatic)),
                            )
                        )
                    );
                    if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap')) {
                        mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap', 0777, true);
                    }
                    $content = '<?php ';
                    $content .= '$xmlstr = <<<XML
                                    <?xml version="1.0" encoding="UTF-8"?>
                                    <sitemapindex xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>
                                    XML;';
                    $content .= '$xml = new SimpleXMLElement($xmlstr);';
                    $content .= '$sitemap = $xml->addChild("sitemap");';
                    $content .= '$sitemap->addChild("loc", "https://' . $_SERVER['SERVER_NAME'] . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeildstatic)) . '/");';
                    $content .= 'Header("Content-type: application/xml");';
                    $content .= 'print($xml->asXML());';
                    $content .= ' ?>';
                    $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/index.php', 'w');
                    fwrite($file, $content);
                    fclose($file);
                }

                if ($customfeildstatic) {
                    if (!file_exists(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeildstatic)))) {
                        mkdir(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeildstatic)), 0777, true);
                    }
                    $content = $stXml;

                    $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/' . urldecode(preg_replace('/[^A-Za-z0-9-]+/', '-', $customfeildstatic)) . '/index.xhtml', 'w');
                    fwrite($file, $content);
                    fclose($file);
                }
            }
        }

        foreach ($this->mapkit as $i => $map) {
            if (isset($_POST['remove-' . $map['id']])) {
                $dir = substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . 'sitemap/' . urldecode($map['slug']);
                if (is_dir($dir)) {
                    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                    $files = new RecursiveIteratorIterator(
                        $it,
                        RecursiveIteratorIterator::CHILD_FIRST
                    );
                    foreach ($files as $file) {
                        if ($file->isDir()) {
                            rmdir($file->getRealPath());
                        } else {
                            unlink($file->getRealPath());
                        }
                    }
                    rmdir($dir);
                }
                unset($this->mapkit[$i]);
                update_option("mapkit.list", $this->mapkit);
                // generate costructor php codes
                $content = '<?php ';
                $content .= '$xmlstr = <<<XML
                                    <?xml version="1.0" encoding="UTF-8"?>
                                    <sitemapindex xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></sitemapindex>
                                    XML;';
                $content .= '$xml = new SimpleXMLElement($xmlstr);';
                if (count($this->mapkit) > 1) {
                    // iterate array of directories
                    foreach ($this->mapkit as $map) {
                        if ($map['WP_id'] > 0) {
                            $content .= '$sitemap = $xml->addChild("sitemap");';
                            $content .= '$sitemap->addChild("loc", "https://' . $_SERVER['SERVER_NAME'] . '/sitemap/' . urldecode(get_post_field('post_name', $map['WP_id'])) . '/");';
                        }
                        if ($map['WP_id'] == -1) {
                            $content .= '$sitemap = $xml->addChild("sitemap");';
                            $content .= '$sitemap->addChild("loc", "https://' . $_SERVER['SERVER_NAME'] . '/sitemap/' . urldecode($map['slug']) . '/");';
                        }
                    }
                }
                $content .= 'Header("Content-type: application/xml");';
                $content .= 'print($xml->asXML());';
                $content .= ' ?>';
                $file = fopen(substr(getcwd(), 0, strpos(getcwd(), "wp-admin")) . '/sitemap/index.php', 'w');
                fwrite($file, $content);
                fclose($file);
            }
        }
    }

    public function display_section()
    {
        if (get_option('mapkit.list')) $this->mapkit = get_option('mapkit.list');
        $plug_path = $_SERVER['REQUEST_URI'];
        $domain = $_SERVER['SERVER_NAME'];
        $html = "<div class=\"xwrap ui segment\">";
        $html .= "<h2 id=\"plugin-name-heading\">";
        $html .= " <a href=\"https://www.linkedin.com/in/farid-teymouri/\" class=\"author-link\" target=\"_blank\">توسعه دهنده این افزونه را دنبال کنید.</a>";
        $html .= "</h2>";
        if ($this->message['error']) $html .= "<div class=\"ui red message\">{$this->message['message']}</div>";
        $html .= "<div class=\"ui two item menu mainBtns\">";
        switch ($plug_path) {

            case "/wp-admin/tools.php?page=mapkit&section=find-to-fix":
                $html .= "<a href=\"?page=mapkit&section=directories\" class=\"item blue\" style=\"box-shadow: none;border: 0;margin: 0;\">دایرکتوری‌ها</a>";
                $html .= "<a href=\"?page=mapkit&section=find-to-fix\" class=\"item active blue\" style=\"box-shadow: none;border: 0;margin: 0;\">توسعه در صورت توافق</a>";
                $html .= "</div>";
                break;

            case "/wp-admin/tools.php?page=mapkit&section=directories":
            default:
                $html .= "<a href=\"?page=mapkit&section=directories\" class=\"item active blue\" style=\"box-shadow: none;border: 0;margin: 0;\">دایرکتوری‌ها</a>";
                $html .= "<a href=\"?page=mapkit&section=find-to-fix\" class=\"item blue\" style=\"box-shadow: none;border: 0;margin: 0;\">توسعه در صورت توافق</a>";
                $html .= "</div>";
                $html .= "<p style=\"margin:2rem 0;\">تمامی دایرکتوری های موجود در کادر پایین قابل مشاهده است</p>";
                $html .= "<div dir=\"ltr\" class=\"ui inverted segment\">";
                $bigger = array();
                $disabled = array();

                if (count($this->mapkit)) {
                    $space = "";
                    foreach ($this->mapkit as $i => $item) {
                        if ($item['id'] > 1)  if (!in_array($item['id'], $bigger)) array_push($bigger, $item['id']);
                        if (!in_array($item['WP_id'], $disabled)) array_push($disabled, $item['WP_id']);
                    }
                    foreach ($this->mapkit as $i => $item) {
                        if ($item['id'] == 1) {
                            $html .= '
        <ul class="list-cmp">
            <li>.</li>
            <li>└── <a class="ll-uri" target="_blank" title="index" style="color:#00f3ff;" href="https://' . $domain . '/' . $item['slug'] . '">/' . $item['slug'] . '</a></li>';
                        }
                        if ($item['id'] > 1) {
                            if ($item["id"] < max($bigger)) {
                                $html .= '<li>' . $space . '&nbsp;&nbsp;&nbsp;&nbsp;├── <a class="ll-uri" target="_blank" title="index" style="color:#9bc714;" href="https://' . $domain . '/sitemap/' . $item['slug'] . '">/' . $item['slug'] . '</a><form method="post" class="ui icon buttons"><input type="submit" name="remove-' . $item["id"] . '" id="remove-' . $item["id"] . '" class="ui button black" style="color:red;padding:0.5rem !important;margin:0 .5rem;"value="🗙"></form></li>';
                            }
                            if ($item["id"] == max($bigger)) {
                                $html .= '<li>' . $space . '&nbsp;&nbsp;&nbsp;&nbsp;└── <a class="ll-uri" target="_blank" title="index" style="color:#9bc714;" href="https://' . $domain . '/sitemap/' . $item['slug'] . '">/' . $item['slug'] . '</a><form method="post" class="ui icon buttons"><input type="submit" name="remove-' . $item["id"] . '" id="remove-' . $item["id"] . '" class="ui button black" style="color:red;padding:0.5rem !important;margin:0 .5rem;" value="🗙"></form></li>';
                            }
                        }
                    }
                }
                $html .= "</div>";
                $html .=  "</pre>";
                $html .= "</ul>";
                $html .= "<div style=\"margin:2rem auto;\" class=\"ui divider\"></div>";

                $html .= "<h3>ایجاد دایرکتوری توسط برگه‌های موجود</h3>";
                $html .= "<div class=\"ui placeholder segment\">";
                // $html .= "<strong>برگه</strong>";
                $html .= "<form class=\"ui grid\" method=\"post\" id=\"formofparentpages\">";
                $html .= "<div class=\"eight wide column\">";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"pgchilds-and-self\" name=\"page_radio\" value=\"childs-and-self\" checked />";
                $html .= "<label for=\"pgchilds-and-self\">لینک تمامی برگه های فرزند و همچنین خوداش را، درون فایل XML اضافه کن.</label>";
                $html .= "</div>";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"pgjust-childs\" name=\"page_radio\" value=\"just-childs\" />";
                $html .= "<label for=\"pgjust-childs\" >فقط لینک برگه های فرزند را درون فایل XML اضافه کن.</label>";
                $html .= "</div>";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"pgjust-self\" name=\"page_radio\" value=\"just-self\" />";
                $html .= "<label for=\"pgjust-self\">فقط لینک خوداش را درون XML اضافه کن.</label>";
                $html .= "</div>";
                $html .= "</div>";
                $html .= "<p style=\"font-size:14px;margin-top:1rem;\">در این لیست می توانید دایرکتوری های خود را توسط، Slug برگه های موجود  ایجاد کنید.</p>";
                $args = array(
                    'numberposts'       => -1,
                    'post_type'         => 'page',
                    'post_status'       => 'publish',
                );
                if (count(get_pages($args))) {

                    $html .= "<div class=\"three wide column\">";
                    $html .= "<select id=\"page_id\" name=\"page_id\" >";
                    $html .= "<option value=\"-1\">انتخاب برگه</option>";
                    foreach (get_pages() as $item => $page) {
                        if (in_array($page->ID, $disabled)) {
                            $path = urldecode(substr(parse_url(get_permalink($page->ID), PHP_URL_PATH), 1));
                            $slug = urldecode(get_post_field('post_name', $page->ID));
                            $html .= "<option disabled value=\"{$page->ID}\"> {$path}</option>";
                        }
                        if (!in_array($page->ID, $disabled)) {
                            $path = urldecode(substr(parse_url(get_permalink($page->ID), PHP_URL_PATH), 1));
                            $slug = urldecode(get_post_field('post_name', $page->ID));
                            $html .= "<option  value=\"{$page->ID}\">{$path}</option>";
                        }
                    }
                    $html .= "</select>";
                    $html .= "</div>";
                    $html .= "<div class=\"three wide column\">";
                    $html .= "<input type=\"submit\" name=\"bypage\" id=\"bypage\" class=\"button button-primary\" value=\"ایجاد دایرکتوری\"  />";
                    $html .= "</div>";
                    $html .= "<div class=\"three wide column\">";
                    $html .= "</div>";
                    $html .= "</form>";
                } else {
                    $html .= "<p style=\"font-size:14px;color:red;\">متاسفانه در وردپرس شما هیچ برگه ای یافت نشد!</p>";
                }
                $html .= "</div>";
                $html .= "<h3>ایجاد دایرکتوری شخصی (داینامیک)</h3>";
                $html .= "<div class=\"ui placeholder segment\">";
                $html .= "<form class=\"ui grid\" method=\"post\" id=\"formofparentpages\">";
                $html .= "<div class=\"eight wide column\">";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"self-and-every-pages\" name=\"custom_radio\" value=\"self-and-every-pages\" checked />";
                $html .= "<label for=\"self-and-every-pages\">لینک خوداش و تمامی <strong>برگه ها</strong> را درون فایل XML اضافه کن.</label>";
                $html .= "</div>";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"every-pages\" name=\"custom_radio\" value=\"every-pages\"  />";
                $html .= "<label for=\"every-pages\">فقط تمامی <strong>برگه ها</strong> را درون فایل XML اضافه کن.</label>";
                $html .= "</div>";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"self-and-every-posts\" name=\"custom_radio\" value=\"self-and-every-posts\" />";
                $html .= "<label for=\"self-and-every-posts\">لینک خوداش و تمامی <strong>نوشته ها</strong> را درون فایل XML اضافه کن.</label>";
                $html .= "</div>";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"every-posts\" name=\"custom_radio\" value=\"every-posts\" />";
                $html .= "<label for=\"every-posts\" >فقط تمامی <strong>نوشته ها</strong> را درون فایل XML اضافه کن. </label>";
                $html .= "</div>";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"self-and-every-cats\" name=\"custom_radio\" value=\"self-and-every-cats\" />";
                $html .= "<label for=\"self-and-every-cats\">لینک خوداش و تمامی <strong>دسته بندی ها</strong> را درون فایل XML اضافه کن.</label>";
                $html .= "</div>";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"every-cats\" name=\"custom_radio\" value=\"every-cats\" />";
                $html .= "<label for=\"every-cats\" >فقط تمامی <strong>دسته بندی ها</strong> را درون فایل XML اضافه کن. </label>";
                $html .= "</div>";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"self-and-every-tags\" name=\"custom_radio\" value=\"self-and-every-tags\" />";
                $html .= "<label for=\"self-and-every-tags\">لینک خوداش و تمامی <strong>برچسب ها</strong> را درون فایل XML اضافه کن.</label>";
                $html .= "</div>";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"every-tags\" name=\"custom_radio\" value=\"every-tags\" />";
                $html .= "<label for=\"every-tags\" >فقط تمامی <strong>برچسب ها</strong> را درون فایل XML اضافه کن. </label>";
                $html .= "</div>";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<input type=\"radio\" id=\"just-self\" name=\"custom_radio\" value=\"just-self\" />";
                $html .= "<label for=\"just-self\" ><strong>فقط لینک خوداش</strong> را درون XML اضافه کن.</label>";
                $html .= "</div>";
                $html .= "</div>";

                $html .= "<p style=\"font-size:14px;\">شما می توانید به صورت شخصی و دلخواه نام دایرکتوری خود را وارد نمایید.</p>";
                $html .= "<p style=\"font-size:14px;\">برای مثال : contact-us, my_dir</p>";
                $html .= "<p style=\"font-size:14px;\">برای مثال :  صفحه-شخصی، دایرکتوری_من</p>";
                $args = array(
                    'numberposts'       => -1,
                    'post_type'         => 'post',
                    'post_status'       => 'publish',
                );
                if (count(get_posts($args))) {
                    $html .= "<form class=\"ui grid\" method=\"post\" id=\"formofparentpages\">";
                    $html .= "<div class=\"three wide column\">";
                    $html .= "<input type=\"text\" name=\"customfeild\" id=\"customfeild\" class=\"ui text \" value=\"\">";
                    $html .= "</div>";
                    $html .= "<div class=\"three wide column\">";
                    $html .= "<input type=\"submit\" name=\"customdir\" id=\"customdir\" class=\"button button-primary\" value=\"ایجاد دایرکتوری\"  />";
                    $html .= "</div>";
                    $html .= "<div class=\"three wide column\">";
                    $html .= "</div>";
                    $html .= "</form>";
                }
                $html .= "</div>";

                $html .= "<h3>ایجاد دایرکتوری شخصی (استاتیسک)</h3>";
                $html .= "<div class=\"ui placeholder segment\">";
                $html .= "<form class=\"ui grid\" method=\"post\" id=\"formofparentpages\">";
                $html .= "<div class=\"eight wide column\">";
                $html .= "<div style=\"margin:0.5rem 0;\">";
                $html .= "<label for=\"just-static\" >جهت ساخت فایل استاتیک در دایرکتوری دلخواه خود، کد های XML را در کادر پایین وارد نمایید.</label>";
                $html .= "<textarea style=\"margin-top:10px;\" id=\"stXml\" name=\"stXml\" rows=\"12\" cols=\"50\"></textarea>";
                $html .= "</div>";
                $html .= "</div>";

                $html .= "<p style=\"font-size:14px;\">شما می توانید به صورت شخصی و دلخواه نام دایرکتوری خود را وارد نمایید.</p>";
                $html .= "<p style=\"font-size:14px;\">برای مثال : contact-us, my_dir</p>";
                $html .= "<p style=\"font-size:14px;\">برای مثال :  صفحه-شخصی، دایرکتوری_من</p>";
                $args = array(
                    'numberposts'       => -1,
                    'post_type'         => 'post',
                    'post_status'       => 'publish',
                );
                if (count(get_posts($args))) {
                    $html .= "<form class=\"ui grid\" method=\"post\" id=\"formofparentpages\">";
                    $html .= "<div class=\"three wide column\">";
                    $html .= "<input type=\"text\" name=\"customfeildstatic\" id=\"customfeildstatic\" class=\"ui text \" value=\"\">";
                    $html .= "</div>";
                    $html .= "<div class=\"three wide column\">";
                    $html .= "<input type=\"submit\" name=\"customdirstatic\" id=\"customdirstatic\" class=\"button button-primary\" value=\"ایجاد دایرکتوری\"  />";
                    $html .= "</div>";
                    $html .= "<div class=\"three wide column\">";
                    $html .= "</div>";
                    $html .= "</form>";
                }
                $html .= "</div>";
                break;
        }

        $html .= "</div>";
        echo $html;
    }
}
