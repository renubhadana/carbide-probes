<?php

if (!defined('ABSPATH')) exit('No direct script access allowed');

/**
 * CodeNegar WooCommerce AJAX Product Filter filter class
 *
 * changes wp_query global object to filter products
 *
 * @package    	WooCommerce AJAX Product Filter
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/go/wcpf
 * @version    	2.8.0
 */

class CodeNegar_wcpf_filter {

    public function __construct() {

    }

    public function set_s(&$q) {
        $s = "";
        if (isset($_GET['s']) && strlen($_GET['s']) > 0) {
            $s = $_GET['s'];
        } else {
            return;
        }
        $q->set('s', $s);
    }

    public function set_order(&$q) {
        global $woocommerce;
        $ordering_args = $woocommerce->query->get_catalog_ordering_args();
        $orderby = $ordering_args['orderby'];
        $order = $ordering_args['order'];
        $q->set('orderby', $orderby);
        $q->set('order', $order);
        if (isset($ordering_args['meta_key'])) {
            $q->set('meta_key', $ordering_args['meta_key']);
        }
    }

    public function set_meta_query(&$q, $custom_get = '') {
        if ($custom_get == '') {
            $custom_get = $_GET;
        }
        global $codenegar_wcpf;
        $valid_list = $codenegar_wcpf->helper->get_meta_list();
        $valid_list = array_keys($valid_list);
        $get = $custom_get;
        $selected_filters = array();
        foreach ($get as $key => $value) {
            if (!(strpos($key, "meta_") === false)) { // if key has meta_ string
                $new_key = str_replace("meta_", "", $key);
                if (in_array($new_key, $valid_list)) {
                    $selected_filters[$new_key] = $value;
                }
            }
        }
        $metaquery = array();

        foreach ($selected_filters as $key => $value) {
            $values = explode(",", $value);
            if (count($values) != 2) {
                continue;
            } // skip this filter
            $min = intval($values[0]);
            $max = intval($values[1]);
            $metaquery[] = array(
                'key' => $key,
                'value' => array($min, $max),
                'type' => 'numeric',
                'compare' => 'BETWEEN',
            );

        }

        if (count($metaquery) > 0) {
            $q->set('meta_query', $metaquery);
        }
    }

    public function set_tax_query(&$q, $custom_get = '') {
        if ($custom_get == '') {
            $custom_get = $_GET;
        }
        $custom_get = $this->remove_empty_parameters($custom_get);
        global $codenegar_wcpf;
        $taxquery = array('relation' => 'AND');
        // we only apply on of these three types of category filtring
        // hierarchical category filter
        if (isset($custom_get['cat_cat']) && intval($custom_get['cat_cat']) > 0) { // if category filter is applied
            $taxquery[] = array(
                'taxonomy' => 'product_cat',
                'field' => 'id',
                'terms' => intval($custom_get['cat_cat']),
                //'include_children' => true,
                'operator' => 'IN',
                );
            // "OR" categories filter
        } elseif (isset($custom_get['cato_cat']) && strlen($custom_get['cato_cat']) > 0) {
            $values = explode(",", $custom_get['cato_cat']);
            if (count($values) > 0) {
                $values = $codenegar_wcpf->helper->prepare_parameter($values, true);
                $taxquery[] = array(
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => $values,
                    'include_children' => false, // There may be a bug in wordpress for this parameter it should be true! but false works fow now!
                    'operator' => 'IN',
                );
            }
            // "AND" categories filter
        } elseif (isset($custom_get['cata_cat']) && strlen($custom_get['cata_cat']) > 0) {
            $values = explode(",", $custom_get['cata_cat']);
            if (count($values) > 0) {
                $values = $codenegar_wcpf->helper->prepare_parameter($values, true);
                $taxquery[] = array(
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => $values,
                    'include_children' => false, // There may be a bug in wordpress for this parameter it should be true! but false works fow now!
                    'operator' => 'AND', );
            }
        }

        $valid_list = $codenegar_wcpf->helper->get_attributes();
        $temp = array();
        foreach ($valid_list as $attr) {
            $temp[] = $attr->attribute_name;
        }
        $valid_list = $temp;
        unset($temp);
        $get = $custom_get;
        $selected_filters = array();
        $selected_tax = array();
        //$selected_tax, $selected_filters = [0][name, value, operator], [1][...]
        foreach ($get as $key => $value) {
            // if key has "attr_" string; attr + And, attr + Or
            if ((strpos($key, "attra_") !== false) || (strpos($key, "attro_") !== false) || (strpos($key, "attr_") !== false)) {
                if ((strpos($key, "attra_") !== false)) {
                    $operator = 'AND';
                } else {
                    $operator = 'IN';
                }
                $new_key = str_replace(array("attra_", "attro_", "attr_"), "", $key); // clean the key
                if (in_array($new_key, $valid_list)) {
                    //$selected_filters[$new_key] =  $value;
                    $selected_filters[] = array($new_key, $value, $operator);
                }
            } elseif ((strpos($key, "ctaxa_") !== false) || (strpos($key, "ctaxo_") !== false) || (strpos($key, "ctax_") !== false)) {
                if ((strpos($key, "ctaxa_") !== false)) {
                    $operator = 'AND';
                } else {
                    $operator = 'IN';
                }
                $new_key = str_replace(array("ctaxa_", "ctaxo_", "ctax_"), "", $key); // clean the key
                if (taxonomy_exists($new_key)) {
                    //$selected_tax[$new_key] =  $value;
                    $selected_tax[] = array($new_key, $value, $operator);
                }
            }
        }

        foreach ($selected_filters as $filter) {
            // $filter = [0][name, value, operator], , [1][...]
            $key = $filter[0];
            $value = $filter[1];
            $operator = $filter[2];
            if (strlen($value) == 0) {
                continue;
            } // skip this filter
            $values = explode(",", $value);
            if (count($values) == 0) {
                continue;
            } // skip this filter

            $values = $codenegar_wcpf->helper->prepare_parameter($values, true);
            $taxquery[] = array(
                'taxonomy' => 'pa_' . $key,
                'field' => 'id',
                'terms' => $values,
                'include_children' => true,
                'operator' => $operator, );
        }

        foreach ($selected_tax as $filter) {
            // $filter = [0][name, value, operator], , [1][...]
            $key = $filter[0];
            $value = $filter[1];
            $operator = $filter[2];
            if (strlen($value) == 0) {
                continue;
            } // skip this filter
            $values = explode(",", $value);
            if (count($values) == 0) {
                continue;
            } // skip this filter

            $values = $codenegar_wcpf->helper->prepare_parameter($values, true);
            $taxquery[] = array(
                'taxonomy' => $key,
                'field' => 'id',
                'terms' => $values,
                'include_children' => true,
                'operator' => $operator, );
        }

        if (count($taxquery) > 0) {
            $q->set('tax_query', $taxquery);
        }
    }

    public function posts_where($where) {
        global $codenegar_wcpf, $wpdb;
        // removes space and double quote and check product post type
        if (!$codenegar_wcpf->helper->is_attrs() && !$codenegar_wcpf->helper->is_ctaxs() && strpos(str_replace(array('"', " "), array("'", ""), $where), "post_type='product'") === false) {
            return $where;
        }

        $operator = ' AND ';

        $get = $_GET;
        $selected_filters = array();
        foreach ($get as $key => $value) {
            if (!(strpos($key, "attrs_") === false)) { // if key has "attrS_" string: attr + slider
                $new_key = str_replace("attrs_", "", $key); // clean the key
                $selected_filters[$new_key] = $value;
            }
        }

        $selected_ctax = array();
        foreach ($get as $key => $value) {
            if (!(strpos($key, "ctaxs_") === false)) { // if key has "ctaxS_" string(cusom taxonomy slider)
                $new_key = str_replace("ctaxs_", "", $key); // clean the key
                $selected_ctax[$new_key] = $value;
            }
        }

        $new_where = '';
        $i = 1;
        foreach ($selected_filters as $key => $value) {
            if (strlen($value) == 0) {
                continue;
            } // skip this filter
            $values = explode(",", $value);
            if (count($values) != 2) {
                continue;
            } // skip this filter, min and max

            $values = $codenegar_wcpf->helper->prepare_parameter($values, true); // values are integer

            $new_where .= $operator . " ((cntt{$i}.taxonomy='pa_{$key}') AND CAST(cnt{$i}.name AS SIGNED) BETWEEN '{$values[0]}' AND '{$values[1]}') ";
            $i++;
        }

        $i = 1;
        foreach ($selected_ctax as $key => $value) {
            if (strlen($value) == 0) {
                continue;
            } // skip this filter
            $values = explode(",", $value);
            if (count($values) != 2) {
                continue;
            } // skip this filter, min and max

            $values = $codenegar_wcpf->helper->prepare_parameter($values, true); // values are integer

            $new_where .= $operator . " ((cnct{$i}.taxonomy='{$key}') AND CAST(cnte{$i}.name AS SIGNED) BETWEEN '{$values[0]}' AND '{$values[1]}') ";
            $i++;
        }

        $where .= $new_where;

        return $where;
    }

    public function posts_join($join) {
        global $codenegar_wcpf;
        if (!$codenegar_wcpf->helper->is_attrs() && !$codenegar_wcpf->helper->is_ctaxs()) {
            return $join;
        }

        // okay attribute slider exists

        $get = array_keys($_GET);
        $get = implode(" ", $get);

        $count_attrs = substr_count($get, 'attrs_');
        global $wpdb;
        for ($i = 1; $i <= $count_attrs; $i++) {
            $join .= " LEFT JOIN {$wpdb->term_relationships} cntr{$i} ON {$wpdb->posts}.ID = cntr{$i}.object_id INNER JOIN {$wpdb->term_taxonomy} cntt{$i} ON cntt{$i}.term_taxonomy_id=cntr{$i}.term_taxonomy_id INNER JOIN {$wpdb->terms} cnt{$i} ON cnt{$i}.term_id = cntt{$i}.term_id ";
        }

        $count_ctaxs = substr_count($get, 'ctaxs_');
        global $wpdb;
        for ($i = 1; $i <= $count_ctaxs; $i++) {
            $join .= " LEFT JOIN {$wpdb->term_relationships} cntrc{$i} ON {$wpdb->posts}.ID = cntrc{$i}.object_id INNER JOIN {$wpdb->term_taxonomy} cnct{$i} ON cnct{$i}.term_taxonomy_id=cntrc{$i}.term_taxonomy_id INNER JOIN {$wpdb->terms} cnte{$i} ON cnte{$i}.term_id = cnct{$i}.term_id ";
        }

        return $join;
    }

    public function posts_groupby($groupby) {
        global $codenegar_wcpf;
        if (!$codenegar_wcpf->helper->is_attrs()) {
            return $groupby;
        }

        global $wpdb;
        // we need to group on post ID
        $groupby_id = "{$wpdb->posts}.ID";
        if (strpos($groupby, $groupby_id) !== false) return $groupby;

        // groupby was empty, use ours
        if (!strlen(trim($groupby))) return $groupby_id;

        // wasn't empty, append ours
        return $groupby . ", " . $groupby_id;
    }

    public function set_date(&$q) { // product publish date filter
        // todo: Maybe we add date filtering in future versions
    }

    public function filter_products(&$q) {

        global $codenegar_wcpf;
        // We only want to affect the main query of shop pages
        if(!$codenegar_wcpf->helper->is_wcpf_area($q)){
            return;
        }

        global $wp_query;
        $this->set_tax_query($q);
        $this->set_order($q);
        $this->set_meta_query($q);
        $this->set_s($q);
        if (isset($_GET['cnep']) && intval($_GET['cnep']) == 0) {
            $wp_query->set('paged', 1);
        }

        return; // inuput is sent by reference so we don't need to return any value
    }

    public function post_count($key = '', $val = '') {
        global $wp_query, $codenegar_wcpf;
        $is_cache_used = false;
        // Create a copy of global query(copy by value not reference)
        $original_query = clone $wp_query;
        $temp_query = clone $wp_query;

        // By default zero results, when we have no results WordPress doesn't update this number
        $temp_query->found_posts = 0;
        // set new url parameters
        $custom_get = $_GET;
        $custom_get['cnpf'] = 1;
        $custom_get['cnep'] = 0;
        $custom_get['post_type'] = 'product';
        $custom_get[$key] = $val;
        // build multiple selection; comma separated lists
        if (strpos($key, 'cato_') !== false || strpos($key, 'cata_') !== false || strpos($key, 'cat_cat') !== false || strpos($key, 'attro_') !== false ||
            strpos($key, 'attra_') !== false || strpos($key, 'ctaxa_') !== false || strpos($key, 'ctaxo_') !== false) {

            // if currently some values are applied
            if (isset($_GET[$key]) && !empty($_GET[$key])) {
                $val = $_GET[$key] . ',' . $val;
            }

            $val = array_unique(explode(',', $val));
            $val = implode(',', $val);
            $custom_get[$key] = $val;
        }

        $custom_get = $this->remove_empty_parameters($custom_get);

        $temp_query->set('post_status', 'publish');
        $temp_query->set('post_type', 'product');
        // Get only one post to get best performance
        $temp_query->set('posts_per_page', 1);
        // No offset, get one post from the beginning
        $temp_query->set('paged', 1);
        // Only get post IDs and no other element such as post meta,... to get best performance
        $temp_query->set('fields', 'ids');
        // Don't order results to get best performance
        $temp_query->set('orderby', 'none');
        // Load min comments to get best performance
        $temp_query->set('comments_per_page', 1);
        // Control WordPress core query caching
        // $temp_query->set('cache_results', false);
        // Ask WooCommerce not to mess this query
        $temp_query->set('wc_query', false);
        // Set a flag for count query, so other plugins can recognize its queries easily
        $temp_query->set('wcpf_count_query', true);
        // Apply new key & value and return count
        $this->set_tax_query($temp_query, $custom_get);

        // get cached results
        $num = false;
        $hash = '';
        if($codenegar_wcpf->options->cache_count == 'yes'){
            $hash = 'cn_wcpf_' . md5(serialize($temp_query));
            $num = get_transient($hash);
            $is_cache_used = true;
        }

        // execute the query only when there is no cache
        if($num === false){
            // Execute query and count found posts
            $temp_query->get_posts();
            $num = (int) $temp_query->found_posts;
            // There were no cache so save the result
            if($codenegar_wcpf->options->cache_count == 'yes') {
                // todo: For now cache time is 60 minutes, in the next version we will add an option for it
                set_transient($hash, $num, 3600);
            }
        }
        unset($temp_query);
        $wp_query = null;
        $wp_query = clone $original_query;
        if(!$is_cache_used){
            // We have modified current archive main query, roll it back to normal
            $wp_query->get_posts();
        }

        return $num;
    }

    //removes empty parameters from custom get array
    public function remove_empty_parameters($get) {
        foreach ($get as $key => $value) {
            $value = trim($value);
            if (empty($value)) {
                unset($get[$key]);
            }
        }

        return $get;
    }

}
