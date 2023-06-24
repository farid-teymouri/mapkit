<?php

function childs_and_self($page_id)
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
    $parents = array(0 => $page_id);
    $args = array(
        'numberposts'       => -1,
        'post_type'         => 'page',
        'post_status'       => 'publish',
    );
    foreach (get_pages($args) as $i => $page) {

        if ($page->post_parent == $page_id) {
            if (!in_array($page->ID, $parents)) {
                array_push($parents, $page->ID);
            }
        }
        foreach ($parents as $parent) {
            if ($page->post_parent == $parent) {
                if (!in_array($page->ID, $parents)) {
                    array_push($parents, $page->ID);
                }
            }
        }
    }
    foreach ($parents as $parent) {
        foreach (get_pages($args) as $i => $page) {
            if ($page->ID == $parent) {
                $pagedate = explode(" ", $page->post_modified);
                $url = $xml->addChild("url");
                $url->addChild("loc", '' . get_permalink($page->ID) . '');
                $url->addChild("lastmode", '' . $pagedate[0] . 'T' . $pagedate[1] . '' . $tempo . '');
            }
        }
    }

    Header("Content-type: application/xml");
    print($xml->asXML());
}


function just_childs($page_id)
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
    $parents = array();
    $args = array(
        'numberposts'       => -1,
        'post_type'         => 'page',
        'post_status'       => 'publish',
    );
    foreach (get_pages($args) as $i => $page) {

        if ($page->post_parent == $page_id) {
            if (!in_array($page->ID, $parents)) {
                array_push($parents, $page->ID);
            }
        }
        foreach ($parents as $parent) {
            if ($page->post_parent == $parent) {
                if (!in_array($page->ID, $parents)) {
                    array_push($parents, $page->ID);
                }
            }
        }
    }
    foreach ($parents as $parent) {
        foreach (get_pages($args) as $i => $page) {
            if ($page->ID == $parent) {
                $pagedate = explode(" ", $page->post_modified);
                $url = $xml->addChild("url");
                $url->addChild("loc", '' . get_permalink($page->ID) . '');
                $url->addChild("lastmode", '' . $pagedate[0] . 'T' . $pagedate[1] . '' . $tempo . '');
            }
        }
    }

    Header("Content-type: application/xml");
    print($xml->asXML());
}


function just_self($page_id)
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
        if ($page->ID == $page_id) {
            $pagedate = explode(" ", $page->post_modified);
            $url = $xml->addChild("url");
            $url->addChild("loc", '' . get_permalink($page->ID) . '');
            $url->addChild("lastmode", '' . $pagedate[0] . 'T' . $pagedate[1] . '' . $tempo . '');
        }
    }
    Header("Content-type: application/xml");
    print($xml->asXML());
}
