<?php

if (!defined('ABSPATH')) exit('No direct script access allowed');

/**
 * CodeNegar WooCommerce AJAX Product Filter functions
 *
 * Non object oriented functions
 *
 * @package    	WooCommerce AJAX Product Filter
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/go/wcpf
 * @version    	2.8.0
 */

function codenegar_wcpf_generate_widget($args) {
    global $codenegar_wcpf;
    do_action('wcpf_generate_widget', $args);
    wp_enqueue_style('codenegar-wcpf-frontend-style');
    wp_enqueue_script('jquery');
    wp_enqueue_script('codenegar-ajax-search-migrate');
    // Include jQueryUI core and jQueryUIUI Slider when the this widget needs it
    if($args['type'] == 'slider') {
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-slider');
    }

    wp_enqueue_script('codenegar-wcpf-history-js');
    // Include scrollTo script when the options is enabled
    if($codenegar_wcpf->options->scroll_to_top == 'yes'){
        wp_enqueue_script('codenegar-wcpf-scrollto-js');
    }
    wp_enqueue_script('codenegar-wcpf-frontend');

    if (!$codenegar_wcpf->is_localized) {
        $codenegar_wcpf->localize_script_config();
        $codenegar_wcpf->is_localized = true;
    }

    $widget = $args['widget'];
    $type = $args['type'];
    switch ($widget) {
        case 'attr':
            switch ($type) {
                case 'list':
                    $codenegar_wcpf->html->attr_list($args);
                    break;
                case 'slider':
                    $codenegar_wcpf->html->attr_slider($args);
                    break;
                case 'dropdown':
                    $codenegar_wcpf->html->attr_dropdown($args);
                    break;
            }
            break;
        case 'ctax':
            switch ($type) {
                case 'list':
                    $codenegar_wcpf->html->custom_taxonomy_list($args);
                    break;
                case 'slider':
                    $codenegar_wcpf->html->custom_taxonomy_slider($args);
                    break;
                case 'dropdown':
                    $codenegar_wcpf->html->custom_taxonomy_dropdown($args);
                    break;
            }
            break;
        case 'cat':
            switch ($type) {
                case 'list':
                    $codenegar_wcpf->html->cat_list($args);
                    break;
                case 'dropdown':
                    $codenegar_wcpf->html->cat_dropdown($args);
                    break;
            }
            break;
        case 'meta':
            switch ($type) {
                case 'slider':
                    $codenegar_wcpf->html->meta_slider($args);
                    break;
            }
            break;
        case 'orderby':
            $codenegar_wcpf->html->orderby($args);
            break;
        case 'image_attr':
            $codenegar_wcpf->html->image_attr($args);
            break;
        case 'nonh_cat':
            $codenegar_wcpf->html->nonh_cat($args);
            break;
    }
}

if (!function_exists('codenegar_parse_args')) {

    function codenegar_parse_args($args, $defaults = '') {
        if (is_object($args)){
            $r = get_object_vars($args);
        } elseif (is_array($args))
            $r = &$args;
        else {
            $r = array();
            wp_parse_str($args, $r); // second parameter is output
        }
        if (is_array($defaults))
            return codenegar_array_merge($defaults, $r);

        return $r;
    }
}

if (!function_exists('codenegar_array_merge')) {

    function codenegar_array_merge() {
        $params = func_get_args();
        $merged = array_shift($params); // using first array as base

        foreach ($params as $array) {
            foreach ($array as $key => $value) {
                if (isset($merged[$key]) && is_array($value) && is_array($merged[$key])) {
                    $merged[$key] = codenegar_array_merge($merged[$key], $value);
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }
}

function ajax_product_filter($data) {
    echo do_shortcode('[ajax_product_filter data="' . $data . '"]');
}

function codenegar_clear_old_wcpf_caches() {
    global $wpdb;
    do_action('wcpf_clear_old_caches');
    // Select option_name column which are older than 10 minutes
    $sql = "SELECT REPLACE(option_name, '_transient_timeout_', '') AS tran FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\__%' AND CAST(option_value as UNSIGNED) < " . (time() - 600);
    $expired = $wpdb->get_results($sql, ARRAY_N);
    if(count($expired) == 0){
        return;
    }
    $records = array();
    foreach ($expired as $ex) {
        $records[] = '_transient_' . $ex[0];
        $records[] = '_transient_timeout_' . $ex[0];
    }
    $records = array_map(array($wpdb, 'escape'), $records);
    $records = "'" . implode("','", $records) . "'";
    $sql = "DELETE FROM $wpdb->options WHERE option_name  IN ({$records})";
    $wpdb->query($sql);
}

function codenegar_get_current_page_id() {
    $page_object = get_queried_object();
    $page_id     = get_queried_object_id();

    return $page_id;
}

function is_wcpf_area(&$query = false) {
    global $codenegar_wcpf;

    return $codenegar_wcpf->helper->is_wcpf_area($query);
}

function is_wcpf_count_query(&$query = false) {
    if($query !== false){
        $is_wcpf_count_query =  $query->get('wcpf_count_query');
    }else{
        global $wp_query;
        $is_wcpf_count_query =  $wp_query->get('wcpf_count_query');
    }

    return (boolean) $is_wcpf_count_query;
}
