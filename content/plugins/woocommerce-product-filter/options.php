<?php
if (!defined('ABSPATH')) exit('No direct script access allowed');

 /**
 * CodeNegar WooCommerce AJAX Product Filter options page
 *
 * Options pages controls how plugin works
 *
 * @package    	WooCommerce AJAX Product Filter
 * @author      Farhad Ahmadi <ahm.farhad@gmail.com>
 * @license     http://codecanyon.net/licenses
 * @link		http://codenegar.com/go/wcpf
 * @version    	2.8.0
 */

global $codenegar_wcpf;

// Assets are loaded by hooks
wp_enqueue_script('media-upload');
wp_enqueue_script('thickbox');
wp_enqueue_style('thickbox');

$is_wc = $codenegar_wcpf->helper->is_wc();

$is_secure = false;

if(isset($_POST['product_filter_submit'])){
    $is_secure = wp_verify_nonce($_REQUEST['security'], $codenegar_wcpf->security);
}

if(isset($_POST['product_filter_submit']) && $is_secure) {
    $codenegar_product_filter = array();
    $codenegar_product_filter['loader_image'] = $codenegar_wcpf->helper->prepare_parameter($_POST['loader_image'], false);
    $codenegar_product_filter['display_no_products_message'] = $codenegar_wcpf->helper->prepare_parameter($_POST['display_no_products_message'], false);
    $codenegar_product_filter['enable_random_order'] = $codenegar_wcpf->helper->prepare_parameter($_POST['enable_random_order'], false);
    $codenegar_product_filter['hide_duplicate_pagination'] = $codenegar_wcpf->helper->prepare_parameter($_POST['hide_duplicate_pagination'], false);
    $codenegar_product_filter['absolute_positioned_container'] = $codenegar_wcpf->helper->prepare_parameter($_POST['absolute_positioned_container'], false);
    $codenegar_product_filter['disable_product_wrapping'] = $codenegar_wcpf->helper->prepare_parameter($_POST['disable_product_wrapping'], false);
    $codenegar_product_filter['reload_entire_page'] = $codenegar_wcpf->helper->prepare_parameter($_POST['reload_entire_page'], false);
    $codenegar_product_filter['ajax_overlay_color'] = $codenegar_wcpf->helper->prepare_parameter($_POST['ajax_overlay_color'], false);
    $codenegar_product_filter['ajax_overlay_opacity'] = $codenegar_wcpf->helper->prepare_parameter($_POST['ajax_overlay_opacity'], false);
    $codenegar_product_filter['ajax_overlay_style'] = $codenegar_wcpf->helper->prepare_parameter($_POST['ajax_overlay_style'], false);
    $codenegar_product_filter['count_filter_items'] = $codenegar_wcpf->helper->prepare_parameter($_POST['count_filter_items'], false);
    $codenegar_product_filter['hide_empty_items'] = $codenegar_wcpf->helper->prepare_parameter($_POST['hide_empty_items'], false);
    $codenegar_product_filter['hide_empty_items'] = ($codenegar_product_filter['count_filter_items'] == 'no') ? 'no' : $codenegar_product_filter['hide_empty_items']; // count items should be on

    $codenegar_product_filter['hide_empty_widgets'] = $codenegar_wcpf->helper->prepare_parameter($_POST['hide_empty_widgets'], false);
    $codenegar_product_filter['hide_empty_widgets'] = ($codenegar_product_filter['hide_empty_items'] == 'no') ? 'no' : $codenegar_product_filter['hide_empty_widgets']; // hide items should be on

    $codenegar_product_filter['scroll_to_top'] = $codenegar_wcpf->helper->prepare_parameter($_POST['scroll_to_top'], false);
    $codenegar_product_filter['cache_count'] = $codenegar_wcpf->helper->prepare_parameter($_POST['cache_count'], false);
    $codenegar_product_filter['display_count'] = $codenegar_wcpf->helper->prepare_parameter($_POST['display_count'], false);
    $codenegar_product_filter['display_count'] = ($codenegar_product_filter['count_filter_items'] == 'no') ? 'no' : $codenegar_product_filter['display_count']; // count items should be on
    $codenegar_product_filter['count_template'] = htmlspecialchars($_POST['count_template']);
    $codenegar_product_filter['custom_taxonomies_list'] = $codenegar_wcpf->helper->prepare_parameter($_POST['custom_taxonomies_list'], false);
    $codenegar_product_filter['wrapper_selector'] = $codenegar_wcpf->helper->prepare_parameter($_POST['wrapper_selector'], false);
    $codenegar_product_filter['custom_areas'] = $codenegar_wcpf->helper->prepare_parameter($_POST['custom_areas'], false);
    $codenegar_product_filter['custom_css'] = $codenegar_wcpf->helper->prepare_parameter($_POST['custom_css'], false);
    $codenegar_product_filter['custom_js'] = $codenegar_wcpf->helper->prepare_parameter($_POST['custom_js'], false);
    $codenegar_product_filter['sidebars'] = $codenegar_wcpf->helper->prepare_sidebars($_POST['sidebars']);
    $codenegar_product_filter['hashtag_fallback'] = (isset($_POST['hashtag_fallback']) && $_POST['hashtag_fallback'] == 'checked') ? 'true' : 'false';

    // Store array of settings to database
    update_option('codenegar_product_filter', $codenegar_product_filter);

    ?>
	<div class="updated"><p><?php _e("Changes Saved.", $codenegar_wcpf->text_domain); ?></p></div>
	<?php
}

if (isset($_POST['product_filter_submit']) && !$is_secure){
    ?>
	<div class="error"><p><?php _e('Security check failed.', $codenegar_wcpf->text_domain); ?></p></div>
	<?php
}
if(!$is_wc){
    ?>
	<div class="error"><p><?php _e('WooCommerce not found. Get a version at <a href="https://wordpress.org/plugins/woocommerce/">WordPress.rg</a>. ', $codenegar_wcpf->text_domain); ?></p></div>
	<?php
    die();
}

$codenegar_product_filter_options = get_option('codenegar_product_filter');
$defaults = $codenegar_wcpf->helper->default_options();
$codenegar_product_filter_options = codenegar_parse_args($codenegar_product_filter_options, $defaults);
?>
<script>
var sidebars = [ <?php echo $codenegar_wcpf->helper->get_sidebars_object($codenegar_product_filter_options['sidebars']); ?>];
(function($) {
$(document).ready(function() {
		$(function() {
			$(".chzn-select").chosen({width: "162px"});
		});
	});
})(jQuery);
</script>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br /></div>
<h2><?php _e('WooCommerce AJAX Product Filter', $codenegar_wcpf->text_domain); ?></h2>
<form method="post">
<div id="poststuff" class="metabox-holder" >
	<div class="postbox" >
	<div  class="handlediv"></div>
	<h3 class="hndle"><span><?php _e('Options', $codenegar_wcpf->text_domain); ?></span></h3>
	<div class="inside" >
    <h2><?php _e('Overlay & Container Settings', $codenegar_wcpf->text_domain); ?></h2>
	<label for="loader_image"><?php _e('AJAX overlay image', $codenegar_wcpf->text_domain); ?>:<span id="form_label"></span></label>
	<?php
    $codenegar_wcpf->html->image_upload_field($codenegar_product_filter_options['loader_image'], 'loader_image');
    ?>
	 <br />
	<br />
	<label for="ajax_overlay_style"><?php _e('AJAX overlay style', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<select name="ajax_overlay_style" id="ajax_overlay_style">
		<option <?php if($codenegar_product_filter_options['ajax_overlay_style'] == 'replace') echo 'selected="selected"' ?> value="replace"><?php _e('Replace', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_style'] == 'append') echo 'selected="selected"' ?> value="append"><?php _e('Append', $codenegar_wcpf->text_domain); ?></option>
        <option <?php if($codenegar_product_filter_options['ajax_overlay_style'] == 'prepend') echo 'selected="selected"' ?> value="prepend"><?php _e('Prepend', $codenegar_wcpf->text_domain); ?></option>
	</select>

	<br />
	<br />
	<label for="ajax_overlay_color"><?php _e('AJAX overlay color', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<select name="ajax_overlay_color" id="ajax_overlay_color">
		<option <?php if($codenegar_product_filter_options['ajax_overlay_color'] == 'transparent') echo 'selected="selected"' ?> value="transparent"><?php _e('Transparent', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_color'] == 'overlaid') echo 'selected="selected"' ?> value="overlaid"><?php _e('Overlaid', $codenegar_wcpf->text_domain); ?></option>
	</select>
		<br />
	<br />
	<label for="ajax_overlay_opacity"><?php _e('AJAX overlay opacity', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<select name="ajax_overlay_opacity" id="ajax_overlay_opacity">
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '1') echo 'selected="selected"' ?> value="1"><?php _e('1', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '0.9') echo 'selected="selected"' ?> value="0.9"><?php _e('0.9', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '0.8') echo 'selected="selected"' ?> value="0.8"><?php _e('0.8', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '0.7') echo 'selected="selected"' ?> value="0.7"><?php _e('0.7', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '0.6') echo 'selected="selected"' ?> value="0.6"><?php _e('0.6', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '0.5') echo 'selected="selected"' ?> value="0.5"><?php _e('0.5', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '0.4') echo 'selected="selected"' ?> value="0.4"><?php _e('0.4', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '0.3') echo 'selected="selected"' ?> value="0.3"><?php _e('0.3', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '0.2') echo 'selected="selected"' ?> value="0.2"><?php _e('0.2', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '0.1') echo 'selected="selected"' ?> value="0.1"><?php _e('0.1', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['ajax_overlay_opacity'] == '0') echo 'selected="selected"' ?> value="0"><?php _e('0', $codenegar_wcpf->text_domain); ?></option>
	</select>
    <br />
    <br />
    <label for="absolute_positioned_container"><?php _e('Absolute positioned container', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
    <select name="absolute_positioned_container" id="absolute_positioned_container">
        <option <?php if($codenegar_product_filter_options['absolute_positioned_container'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
        <option <?php if($codenegar_product_filter_options['absolute_positioned_container'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
    </select>
    <br />
    <br />
    <label for="disable_product_wrapping"><?php _e('Disable Product Wrapping', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
    <select name="disable_product_wrapping" id="disable_product_wrapping">
        <option <?php if($codenegar_product_filter_options['disable_product_wrapping'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
        <option <?php if($codenegar_product_filter_options['disable_product_wrapping'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
    </select>

    <br />
    <br />
    <label for="reload_entire_page"><?php _e('Reload Entire Page (No AJAX)', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
    <select name="reload_entire_page" id="reload_entire_page">
        <option <?php if($codenegar_product_filter_options['reload_entire_page'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
        <option <?php if($codenegar_product_filter_options['reload_entire_page'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
    </select>

    <h2><?php _e('Counting Products Settings', $codenegar_wcpf->text_domain); ?></h2>
	<label for="count_filter_items"><?php _e('Count filters items', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<select name="count_filter_items" id="count_filter_items">
		<option <?php if($codenegar_product_filter_options['count_filter_items'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['count_filter_items'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
	</select>
	<br />
	<br />
	<label for="hide_empty_items"><?php _e('Hide empty items', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<select name="hide_empty_items" id="hide_empty_items">
		<option <?php if($codenegar_product_filter_options['hide_empty_items'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['hide_empty_items'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
	</select>
	<br style="display:none;" />
	<br style="display:none;" />
	<label for="hide_empty_widgets" style="display:none;" ><?php _e('Hide empty widgets', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<select name="hide_empty_widgets" style="display:none;" id="hide_empty_widgets">
		<option <?php if($codenegar_product_filter_options['hide_empty_widgets'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['hide_empty_widgets'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
	</select>
	<br />
	<br />
	<label for="display_count"><?php _e('Display count of products', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<select name="display_count" id="display_count">
		<option <?php if($codenegar_product_filter_options['display_count'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['display_count'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
	</select>
    <br />
    <br />
    <label for="cache_count"><?php _e('Cache count of products', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
    <select name="cache_count" id="cache_count">
        <option <?php if($codenegar_product_filter_options['cache_count'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
        <option <?php if($codenegar_product_filter_options['cache_count'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
    </select>

	<br />
	<br />
	<label for="display_no_products_message"><?php _e('Display no products message', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<select name="display_no_products_message" id="display_no_products_message">
		<option <?php if($codenegar_product_filter_options['display_no_products_message'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['display_no_products_message'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
	</select>
    <br />
    <br />
    <label for="count_template"><?php _e('Count template', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
    <input spellcheck="false" type="text" id="count_template" size="90" placeholder="E.g. (%s) or &lt;small class=&quot;count&quot;&gt;%s&lt;/small&gt; " name="count_template" value="<?php echo stripcslashes($codenegar_product_filter_options['count_template']); ?>">
    <h2><?php _e('Dynamic Sidebars Settings', $codenegar_wcpf->text_domain); ?></h2>
    <label for="create-sidebar"><?php _e('Add or Remove a sidebar', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label><br/>

    <div id="dialog-form" title="<?php _e('Create new sidebar', $codenegar_wcpf->text_domain); ?>">

        <fieldset>
            <label for="title"><?php _e('Title', $codenegar_wcpf->text_domain); ?>:</label>
            <input type="text" name="title" id="sidebar_title" autocomplete="off" id="title" onkeyup="this.value=this.value.replace(/[^a-z0-9_]/g,'');" class="text ui-widget-content ui-corner-all" />
            <br/>
            <br/>
            <label for="visible_to"><?php _e('Visible To', $codenegar_wcpf->text_domain); ?>:</label>
            <br/>
            <select name="visible_to" class="visible_to chzn-select" id="visible_to">
                <option value="all"><?php _e('All Shop Archive', $codenegar_wcpf->text_domain); ?></option>
                <option value="cat"><?php _e('Category', $codenegar_wcpf->text_domain); ?></option>
                <option value="attr"><?php _e('Attribute', $codenegar_wcpf->text_domain); ?></option>
            </select>
    <span id="cat_drp" style="display: none;">
    <?php
    $cats = $codenegar_wcpf->helper->dropdown_cat(array('echo' => 0, 'class' => 'postform chzn-select', 'show_option_all' => ''));
    $cats = str_replace('<select', '<select multiple data-placeholder="Choose some cats"', $cats);
    echo $cats;
    ?>
    </span>
    <span id="attr_drp" style="display: none;">
    <select multiple name="attrib" class="attrib chzn-select" id = "attrib">
        <option value></option>
        <?php
        $attributes = (array) ($codenegar_wcpf->helper->get_attributes());
        $count = count($attributes);
        if($count>0){
            for($i = 0; $i<$count; $i++){
                $label = $attributes[$i]->attribute_label;
                if(!$label){
                    $label = $attributes[$i]->attribute_name;
                }
                ?>
                <option value="<?php echo $attributes[$i]->attribute_name; ?>"><?php echo $label; ?></option>
            <?php
            }
        }
        ?>
    </select>
    </span>
        </fieldset>

    </div>

    <div id="sidebars-contain" class="ui-widget">
        <table id="sidebars" class="ui-widget ui-widget-content">
            <thead>
            <tr class="ui-widget-header">
                <th><?php _e('Title', $codenegar_wcpf->text_domain); ?></th>
                <th><?php _e('Visible To', $codenegar_wcpf->text_domain); ?></th>
                <th><?php _e('Shortcode', $codenegar_wcpf->text_domain); ?></th>
                <th><?php _e('Action', $codenegar_wcpf->text_domain); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php echo $codenegar_wcpf->helper->get_sidebars_table($codenegar_product_filter_options['sidebars']); ?>
            </tbody>
        </table>
        <button id="create-sidebar"><?php _e('New Sidebar', $codenegar_wcpf->text_domain); ?></button>
    </div>

    <input type="hidden" id="sidebars" name="sidebars" value="<?php echo str_replace('"', "'", $codenegar_product_filter_options['sidebars']); ?>">
    <h2><?php _e('Enable Extra Functionality', $codenegar_wcpf->text_domain); ?></h2>
    <label for="scroll_to_top"><?php _e('Scroll to top after loading', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
    <select name="scroll_to_top" id="scroll_to_top">
        <option <?php if($codenegar_product_filter_options['scroll_to_top'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
        <option <?php if($codenegar_product_filter_options['scroll_to_top'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
    </select>
   	<br />
	<br />
	<label for="enable_random_order"><?php _e('Enable random order', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<select name="enable_random_order" id="enable_random_order">
		<option <?php if($codenegar_product_filter_options['enable_random_order'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['enable_random_order'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
	</select>
   	<br />
	<br />
	<label for="hide_duplicate_pagination"><?php _e('Hide duplicate pagination', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<select name="hide_duplicate_pagination" id="hide_duplicate_pagination">
		<option <?php if($codenegar_product_filter_options['hide_duplicate_pagination'] == 'yes') echo 'selected="selected"' ?> value="yes"><?php _e('Yes', $codenegar_wcpf->text_domain); ?></option>
		<option <?php if($codenegar_product_filter_options['hide_duplicate_pagination'] == 'no') echo 'selected="selected"' ?> value="no"><?php _e('No', $codenegar_wcpf->text_domain); ?></option>
	</select>
    <h2><?php _e('Advanced Settings', $codenegar_wcpf->text_domain); ?></h2>
	<label for="custom_taxonomies_list"><?php _e('Custom taxonomies', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<input spellcheck="false" type="text" id="custom_taxonomies_list" size="90" placeholder="product_brand, my_custom_tax" name="custom_taxonomies_list" value="<?php echo $codenegar_product_filter_options['custom_taxonomies_list']; ?>">
	<br />
	<br />
	<label for="wrapper_selector"><?php _e('Override products wrapper', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<input spellcheck="false" type="text" size="90" id="wrapper_selector"  placeholder="#products_list" name="wrapper_selector" value="<?php echo $codenegar_product_filter_options['wrapper_selector']; ?>">
	<br />
	<br />
	<label for="custom_areas"><?php _e('Custom areas to update', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
	<input spellcheck="false" type="text" size="90" id="custom_areas"  placeholder=".pagination, .result-count" name="custom_areas" value="<?php echo $codenegar_product_filter_options['custom_areas']; ?>">
	<br />
	<br />
   	<label for="custom_css"><?php _e('Custom CSS', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
    <div class="rel_pos">
        <div id="div_custom_css"></div>
    </div>
    <textarea style="display: none;" name="custom_css" id="custom_css"><?php echo $codenegar_product_filter_options['custom_css']; ?></textarea>
	<br />
	<br />
    <label for="custom_js"><?php _e('Custom JavaScript', $codenegar_wcpf->text_domain); ?>:<span id="postform"></span></label>
    <div class="rel_pos">
        <div id="div_custom_js" name="custom_js" ></div>
    </div>
    <textarea style="display: none;" name="custom_js" id="custom_js"><?php echo $codenegar_product_filter_options['custom_js']; ?></textarea>

	<?php wp_nonce_field($codenegar_wcpf->security, 'security') ?>
	</div>
	</div>
	</div>

	<tr valign="top">
	<th scope="row"></th>
		<td><p class="submit"><input type="submit" id="product_filter_submit" name="product_filter_submit" class="button-primary default_submit" value="<?php _e('Save Changes', $codenegar_wcpf->text_domain); ?>" /></p></td>
	</tr>
</form>
</div>
