<?php
function self_and_every_pages($custom)
{
    $xmlstr = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
    XML;
    $xml = new SimpleXMLElement($xmlstr);
    if (str_replace('-', '', get_option('gmt_offset')) < 10) {
        $tempo = '-0' . str_replace('-', '', get_option('gmt_offset'));
    } else {
        $tempo = get_option('gmt_offset');
    }
    if (strlen($tempo) == 3) {
        $tempo = $tempo . ':00';
    }
    $url = $xml->addChild("url");
    $url->addChild("loc", 'https://' . $_SERVER['SERVER_NAME'] . '/' . $custom);
    $args = array(
        'numberposts'       => -1,
        'post_type'         => 'page',
        'post_status'       => 'publish',
    );
    foreach (get_pages($args) as $i => $page) {
        $pagedate = explode(" ", $page->post_modified);
        $url = $xml->addChild("url");
        $url->addChild("loc", '' . get_permalink($page->ID) . '');
        $url->addChild("lastmode", '' . $pagedate[0] . 'T' . $pagedate[1] . '' . $tempo . '');
    }
    Header("Content-type: application/xml");
    print($xml->asXML());
}

function every_pages($custom)
{
    $xmlstr = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
    XML;
    $xml = new SimpleXMLElement($xmlstr);
    if (str_replace('-', '', get_option('gmt_offset')) < 10) {
        $tempo = '-0' . str_replace('-', '', get_option('gmt_offset'));
    } else {
        $tempo = get_option('gmt_offset');
    }
    if (strlen($tempo) == 3) {
        $tempo = $tempo . ':00';
    }
    $args = array(
        'numberposts'       => -1,
        'post_type'         => 'page',
        'post_status'       => 'publish',
    );
    foreach (get_pages($args) as $i => $page) {
        $pagedate = explode(" ", $page->post_modified);
        $url = $xml->addChild("url");
        $url->addChild("loc", '' . get_permalink($page->ID) . '');
        $url->addChild("lastmode", '' . $pagedate[0] . 'T' . $pagedate[1] . '' . $tempo . '');
    }
    Header("Content-type: application/xml");
    print($xml->asXML());
}
function self_and_every_posts($custom)
{
    $xmlstr = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
    XML;
    $xml = new SimpleXMLElement($xmlstr);
    if (str_replace('-', '', get_option('gmt_offset')) < 10) {
        $tempo = '-0' . str_replace('-', '', get_option('gmt_offset'));
    } else {
        $tempo = get_option('gmt_offset');
    }
    if (strlen($tempo) == 3) {
        $tempo = $tempo . ':00';
    }
    $url = $xml->addChild("url");
    $url->addChild("loc", 'https://' . $_SERVER['SERVER_NAME'] . '/' . $custom);
    $target = array(
        'numberposts'       => -1,
        'post_type'         => 'post',
        'post_status'       => 'publish',
    );
    foreach (get_posts($target) as $i => $post) {
        $postdate = explode(" ", $post->post_modified);
        $url = $xml->addChild("url");
        $url->addChild("loc", '' . get_permalink($post->ID) . '');
        $url->addChild("lastmode", '' . $postdate[0] . 'T' . $postdate[1] . '' . $tempo . '');
    }
    Header("Content-type: application/xml");
    print($xml->asXML());
}

function every_posts($custom)
{
    $xmlstr = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
    XML;
    $xml = new SimpleXMLElement($xmlstr);
    if (str_replace('-', '', get_option('gmt_offset')) < 10) {
        $tempo = '-0' . str_replace('-', '', get_option('gmt_offset'));
    } else {
        $tempo = get_option('gmt_offset');
    }
    if (strlen($tempo) == 3) {
        $tempo = $tempo . ':00';
    }
    $target = array(
        'numberposts'       => -1,
        'post_type'         => 'post',
        'post_status'       => 'publish',
    );
    foreach (get_posts($target) as $i => $post) {
        $postdate = explode(" ", $post->post_modified);
        $url = $xml->addChild("url");
        $url->addChild("loc", '' . get_permalink($post->ID) . '');
        $url->addChild("lastmode", '' . $postdate[0] . 'T' . $postdate[1] . '' . $tempo . '');
    }
    Header("Content-type: application/xml");
    print($xml->asXML());
}


function self_and_every_cats($custom)
{
    $xmlstr = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
    XML;
    $xml = new SimpleXMLElement($xmlstr);
    $url = $xml->addChild("url");
    $url->addChild("loc", 'https://' . $_SERVER['SERVER_NAME'] . '/' . $custom);
    foreach (get_categories() as $i => $cat) {
        $url = $xml->addChild("url");
        $url->addChild("loc", '' . get_category_link($cat->cat_ID) . '');
    }
    Header("Content-type: application/xml");
    print($xml->asXML());
}

function every_cats($custom)
{
    $xmlstr = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
    XML;
    $xml = new SimpleXMLElement($xmlstr);
    foreach (get_categories() as $i => $cat) {
        $url = $xml->addChild("url");
        $url->addChild("loc", '' . get_category_link($cat->cat_ID) . '');
    }
    Header("Content-type: application/xml");
    print($xml->asXML());
}


function self_and_every_tags($custom)
{
    $xmlstr = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
    XML;
    $xml = new SimpleXMLElement($xmlstr);
    $url = $xml->addChild("url");
    $url->addChild("loc", 'https://' . $_SERVER['SERVER_NAME'] . '/' . $custom);
    foreach (get_tags() as $i => $tag) {
        $url = $xml->addChild("url");
        $url->addChild("loc", '' . get_category_link($tag->term_id) . '');
    }
    Header("Content-type: application/xml");
    print($xml->asXML());
}

function every_tags($custom)
{
    $xmlstr = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
    XML;
    $xml = new SimpleXMLElement($xmlstr);
    foreach (get_tags() as $i => $tag) {
        $url = $xml->addChild("url");
        $url->addChild("loc", '' . get_category_link($tag->term_id) . '');
    }
    Header("Content-type: application/xml");
    print($xml->asXML());
}

function just_self($custom)
{
    $xmlstr = <<<XML
    <?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
    XML;
    $xml = new SimpleXMLElement($xmlstr);
    $url = $xml->addChild("url");
    $url->addChild("loc", 'https://' . $_SERVER['SERVER_NAME'] . '/' . $custom);
    Header("Content-type: application/xml");
    print($xml->asXML());
}
