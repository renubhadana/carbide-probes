/*!
 * CodeNegar WooCommerce AJAX Product Filter 
 * WCPF: WooCommerce Product Filter
 *
 * Frontend Script File
 *
 * @package	WooCommerce AJAX Product Filter
 * @author	Farhad Ahmadi
 * @link	http://codenegar.com/go/wcpf
 * version	2.8.0
 */
 
var codenegar_page_title = "";
function codenegar_addParameter(url, parameterName, parameterValue, atStart){
    replaceDuplicates = true;
    if(url.indexOf('#') > 0){
        var cl = url.indexOf('#');
        urlhash = url.substring(url.indexOf('#'),url.length);
    } else {
        urlhash = '';
        cl = url.length;
    }
    sourceUrl = url.substring(0,cl);

    var urlParts = sourceUrl.split("?");
    var newQueryString = "";

    if (urlParts.length > 1)
    {
        var parameters = urlParts[1].split("&");
        for (var i=0; (i < parameters.length); i++)
        {
            var parameterParts = parameters[i].split("=");
            if (!(replaceDuplicates && parameterParts[0] == parameterName))
            {
                if (newQueryString == "")
                    newQueryString = "?";
                else
                    newQueryString += "&";
                newQueryString += parameterParts[0] + "=" + (parameterParts[1]?parameterParts[1]:'');
            }
        }
    }
    if (newQueryString == "")
        newQueryString = "?";

    if(atStart){
        newQueryString = '?'+ parameterName + "=" + parameterValue + (newQueryString.length>1?'&'+newQueryString.substring(1):'');
    } else {
        if (newQueryString !== "" && newQueryString != '?')
            newQueryString += "&";
        newQueryString += parameterName + "=" + (parameterValue?parameterValue:'');
    }
    return urlParts[0] + newQueryString + urlhash;
}

// Replace cnep=0 with cnep=1 
function codenegar_correct_cnep($pagination){
	$pagination.find('a[href]').each(function(){
		var new_href = jQuery(this).attr('href').replace(/cnep=0/g,'cnep=1');
		jQuery(this).attr('href', new_href);
	})
	return $pagination;
}

// List of bad pagination themes
function codenegar_update_pagination(){
	var current_theme = codenegar_wcpf_config.current_theme;
	if(current_theme.indexOf('stylemag') > -1){
		return true;
	}
	if(current_theme.indexOf('storefront') > -1){
		return false;
	}
	if(current_theme.indexOf('statement') > -1){
		return true;
	}
	if(current_theme.indexOf('goodstore') > -1){
		return false;
	}
	if(current_theme.indexOf('wpo shopping') > -1){
		return true;
	}
	if(current_theme.indexOf('adrenalin') > -1){
		return false;
	}
	if(current_theme.indexOf('buyshop') > -1){
		return false;
	}
	// 0 is false but shows that the theme is not listed here
	return 0;
}

function codenegar_encode_url(url){
	url = url.replace(/,/g, '%2C');
	return url;
}

function codenegar_add_overlay($wrap, options){
	// Reload entire body, no need to add overlay
	if(codenegar_wcpf_config.reload_entire_page == 'yes'){
		return;
	}
	var defaults = {
		bgColor 		: '#fff',
		opacity			: codenegar_wcpf_config.ajax_overlay_opacity,
		duration		: 600,
		spinner 		: codenegar_wcpf_config.loader_img,
		wrap_id			: 'codenegar_ajax_loader',
		wrap_class		: 'codenegar-ajax-loader blockUI blockOverlay' // using native WooCommerce classes to be blended in with active theme
	}

	options = jQuery.extend(defaults, options);
	if(codenegar_wcpf_config.ajax_overlay_color=='transparent'){
		options.bgColor = 'transparent';
	}
	var overlay = jQuery('<div></div>').css({
		'background-color': 	options.bgColor,
		'opacity': 				options.opacity,
		'width': 				'100%',
		'height': 				'100%',
		'top': 					'0px',
		'left': 				'0px',
		'cursor': 				'default',
		'text-align': 			'center',
		'z-index': 				 99900,
		'border': 				 'none',
		'margin': 				 '0px',
		'padding': 				 '0px',
		'border': 				 'none',
		'background-image': 	 'url("'+options.spinner+'")',
		'background-position': 	 '50% 50%',
		'background-repeat': 	 'no-repeat'

	}).attr('class', options.wrap_class).attr('id',options.wrap_id);
	if(codenegar_wcpf_config.absolute_positioned_container=='yes'){
		overlay.css('position', 'absolute');
		$wrap.css('position', 'relative');
	}

	if(codenegar_wcpf_config.ajax_overlay_style=='append'){
		$wrap.append(overlay).fadeIn(options.duration);
	}else if(codenegar_wcpf_config.ajax_overlay_style=='prepend'){
		$wrap.prepend(overlay).fadeIn(options.duration);
	}else if(codenegar_wcpf_config.ajax_overlay_style=='replace'){
		$wrap.html(overlay).fadeIn(options.duration);
	}
	
}

function codenegar_remove_overlay($wrap){
	$wrap.children('#codenegar_ajax_loader').fadeOut('500', function() {
		jQuery(this).remove();
	});
}

function codenegar_format_range(template, min, max){
    var ret = template;
    ret = ret.replace("%s", min);
    ret = ret.replace("%e", max);
	return ret;
}

function codenegar_get_parameter(name){
	name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp( regexS );
	var state = History.getState();
	var current_url = state.cleanUrl;
	var results = regex.exec( current_url );
	if( results == null )
		return "";
	else
		return results[1];
}

// if hierarchical cat is just clicked returns true
function wcpf_is_cat_cat_clicked(){
	var State = History.getState();
	var selected_cat = "not";
	var url = State.cleanUrl;
	var last_parameter = url.substring(url.lastIndexOf('&') + 1);	
	if(last_parameter.length>0 && last_parameter.indexOf("=")>-1){
		last_parameter = last_parameter.split("=");
		if(last_parameter.length == 2 && last_parameter[0]== "cat_cat"){
			selected_cat = last_parameter[1];
		}
	}
	if(parseInt(selected_cat)>0 || selected_cat=="0"){
		return true;
	}
	return false;
}

function codenegar_wcpf_add_parameter(widget, type, key, value){
	var state = History.getState();
	var current_url = state.cleanUrl;
	var new_url = "";
	var new_key = widget + "_" + key;
	if(new_key=='orderby_orderby') new_key = 'orderby'; // use native WooCommerce ordering
	new_url = codenegar_addParameter(current_url, "cnpf", "1", true); // at first
	new_url = codenegar_addParameter(new_url, "cnep", "0"); // disable paging: shows first page
	new_url = codenegar_addParameter(new_url, new_key, value);
	// some themes bring shop at home, so make it possible to filter products at home
	if((document.URL==codenegar_wcpf_config.home_url) || (document.URL==codenegar_wcpf_config.home_url+"/")){
		new_url = codenegar_addParameter(new_url, "post_type", "product");
	}
	if(codenegar_page_title.length==0){
		codenegar_page_title = jQuery("title").text();
	}
	History.pushState({state:1}, codenegar_page_title, new_url);
}

function codenegar_make_slider($this){
	var min = parseInt($this.closest(".codenegar_slider_wrapper").next().attr("data-min"));
	var max = parseInt($this.closest(".codenegar_slider_wrapper").next().attr("data-max"));
	var template = $this.closest(".codenegar_slider_wrapper").next().attr("data-template");
	var widget = $this.closest(".codenegar_slider_wrapper").next().attr("data-widget");
	var type = $this.closest(".codenegar_slider_wrapper").next().attr("data-type");
	var key = $this.closest(".codenegar_slider_wrapper").next().attr("data-key");
	var step = parseInt($this.closest(".codenegar_slider_wrapper").next().attr("data-step"));
	current_values = codenegar_get_parameter(widget + "_" + key);
    var current_min = 0;
    var current_max = 0;
	if(parseInt(codenegar_get_parameter("cnpf"))==1 && current_values.length>0){ // If CodeNegar Product Filter is active and has url parameters
        current_values = current_values.split(",");
        if(current_values.length==2){
            current_min = parseInt(current_values[0]);
            current_max = parseInt(current_values[1]);
        }
    }
	if(!((parseInt(current_min)>0))){
		current_min = min;
	}
	if(!((parseInt(current_max)>0))){
		current_max = max;
	}
	$this.slider({
	  range: true,
	  min: min,
	  max: max,
	  step: step,
	  values: [ current_min, current_max ],
	  create : function( event, ui ) {
			$this.closest(".codenegar_slider_wrapper").next().children(".min_value").val(current_min);
			$this.closest(".codenegar_slider_wrapper").next().children(".max_value").val(current_max);
	  },
	  slide: function( event, ui ) {
		$this.closest(".codenegar_slider_wrapper").next().children(".min_value").val(ui.values[ 0 ]);
		$this.closest(".codenegar_slider_wrapper").next().children(".max_value").val(ui.values[ 1 ]);
		$this.closest(".codenegar_slider_wrapper").next().children(".amount").html( codenegar_format_range(template, ui.values[ 0 ], ui.values[ 1 ]) );
	  },
	  change: function( event, ui ){
			if (event.originalEvent) { // if user changegs it not by code
				var value = ui.values[ 0 ] + "," + ui.values[ 1 ];
				codenegar_wcpf_add_parameter(widget, type, key, value);
			}
	  }
	});
	
	$this.closest(".codenegar_slider_wrapper").next().children(".amount").html(codenegar_format_range(template, current_min, current_max));
}

function update_filters_count($data){
	// if is counting is not active return
	if(codenegar_wcpf_config.count_filter_items != 'yes'){
		return;
	}
	var is_cat_cat_clicked = wcpf_is_cat_cat_clicked();
	$data.find(".wcpf_updating_widget").each(function(){
		var $this = jQuery(this);
		var id = $this.attr("id");
		// cat_cat is hierarchical and updates itself
		if(is_cat_cat_clicked==true && id == 'wcpf_cat_cat_list'){
			return true; // means continue
		}
		
		//update each widget from $data variable
		jQuery("#"+id).hide().html($this.html()).fadeIn(); // replaces current id contents with updated data
		/* hide a widget if it's completely empty */
	});
}

function codenegar_get_theme_custom_areas(){
	var current_theme = codenegar_wcpf_config.current_theme;
	if(current_theme.indexOf('oxygen') > -1){
		return 'span.results';
	}
	if(current_theme.indexOf('buyshop') > -1){
		return '.wft_pagination, p.woocommerce-result-count:nth-child(2)';
	}
	return '';
}

function codenegar_get_theme_selector(){
	var current_theme = codenegar_wcpf_config.current_theme;
	if(current_theme.indexOf('the retailer') > -1){
		if(jQuery(".listing_products").length>0){
			return ".listing_products";
		}
		if(jQuery(".listing_products_no_sidebar").length>0){
			return ".listing_products_no_sidebar";
		}
	}
	if(current_theme.indexOf('blanco') > -1){
		return '#default_products_page_container';
	}
	if(current_theme.indexOf('simplicity') > -1){
		return '#contentarea .grid_9';
	}
	if(current_theme.indexOf('flatsome') > -1){
		return 'div.large-9';
	}
	if(current_theme.indexOf('foxy') > -1){
		return 'div#et_results_settings';
	}
    if(current_theme.indexOf('mr. tailor') > -1){
        return 'ul.products';
    }
    if(current_theme.indexOf('the7.2') > -1){
        return 'div#content';
    }
    if(current_theme.indexOf('storefront') > -1){
        return '.site-main';
    }
    if(current_theme.indexOf('wpo shopping') > -1){
        return '.products';
    }
    if(current_theme.indexOf('goodstore') > -1){
        return '.archive-content';
    }
    if(current_theme.indexOf('adrenalin') > -1){
        return '.product-listing-wrapper';
    }
    if(current_theme.indexOf('oxygen') > -1){
        return '.shop-grid';
    }
    if(current_theme.indexOf('buyshop') > -1){
        return '.span9';
    }
	return '';
}

function wcpf_auto_override_selector(){
	var current_theme = codenegar_wcpf_config.current_theme;
	if(current_theme.indexOf('flatsome') > -1 && jQuery(".codenegar-shop-loop-wrapper ul").length==0){
		return true;
	}
	return false;
}

function hide_empty_widgets(){
	// return if hiding is not active
	if(codenegar_wcpf_config.hide_empty_widgets != 'yes'){
		return;
	}
	
	jQuery(".wcpf_updating_widget ul").each(function(){
		var $this = jQuery(this);
		var size =  parseInt($this.children("li").size());
		if(size == 0){
			$this.parents(".wcpf_master_wrap").hide();
		}else{
			$this.parents(".wcpf_master_wrap").show(); // It may be hidden from previous queries
		}
	});
	
	jQuery(".wcpf_updating_widget select").each(function(){
		var $this = jQuery(this);
		var size =  parseInt($this.children("option").size());
		if(size == 0){
			$this.parents(".wcpf_master_wrap").hide();
		}else{
			$this.parents(".wcpf_master_wrap").show(); // It may be hidden from previous queries
		}
	});
	
}

function wcpf_get_main_ul(){
	// checking standard names
	if(jQuery("ul.products").length>0){
		return "ul.products";
	}
	
	if(jQuery("ul#products").length>0){
		return "ul#products";
	}
	
	// main ul has the largest width
	var max = -1;
	var selector = '';
	jQuery("ul").not(".ab-top-menu, .nav, .ab-submenu, #sidebar ul, .sidebar ul").not("nav ul").not("aside ul").not("ul[role='navigation']").not(".codenegar_product_filter_wrap  ul").each(function(){
		if(jQuery(this).width()>max){
			selector = this;
			max = jQuery(this).width();
		}
	});
	if(typeof selector == 'string' && selector.length>0){
		return selector;
	}else{
		selector = jQuery(".woocommerce-ordering").next();
		return selector;
	}
	
}

jQuery(function() {
jQuery(".range_meta_slider").each(function(){
	codenegar_make_slider(jQuery(this));
});

codenegar_page_title = jQuery("title").text();

jQuery("a.codenegar_product_filter_reset_button").live("click", function(e){ // Reset filters
	e.preventDefault();
	$this = jQuery(this);
	var new_url = $this.attr("data-url");
	new_url = codenegar_addParameter(new_url, "cnpf", "1");
	new_url = codenegar_addParameter(new_url, "cnep", "0");
	if(codenegar_page_title.length==0){
		codenegar_page_title = jQuery("title").text();
	}
	// reset category and check list
	if(parseInt(codenegar_get_parameter("cat_cat"))>0 || parseInt(codenegar_get_parameter("cnpf"))=="0"){
		var applied_id = codenegar_get_parameter("cat_cat");
		var cat_parameter = jQuery('.codenegar_product_filter_wrap ul li.codenegar_applied_filter_cat a[data-old-value="'+applied_id+'"]').attr("data-value");
		new_url = codenegar_addParameter(new_url, "cat_cat", cat_parameter);
	}
	
	// reset slider values
	jQuery(".range_meta_slider").each(function(){
		$this = jQuery(this);
		var min = parseInt($this.closest(".codenegar_slider_wrapper").next().attr("data-min"));
		var max = parseInt($this.closest(".codenegar_slider_wrapper").next().attr("data-max"));
		var template = $this.closest(".codenegar_slider_wrapper").next().attr("data-template");
		$this.slider( "values", [ min, max ] );
		$this.closest(".codenegar_slider_wrapper").next().children(".amount").html( codenegar_format_range(template, min, max) );
	});
	
	// reset dropdown
	jQuery(".codenegar_product_filter_wrap select option:first-child").each(function(){
		jQuery(this).attr("selected", "selected");
	});
	
	// push new url
	History.pushState({state:1}, codenegar_page_title, new_url);
	var chosen_class = 'codenegar_applied_filter chosen';
	if (typeof wcpf_chosen_class == 'function') {
		  chosen_class = wcpf_chosen_class();
	}
	jQuery(".codenegar_product_filter_wrap ul li").removeClass(chosen_class);
});

jQuery(".codenegar_product_filter_wrap ul li a").live("click", function(e){
	e.preventDefault();
	var $this = jQuery(this);
	var chosen_class = 'codenegar_applied_filter chosen';
	if (typeof wcpf_chosen_class == 'function') {
		  chosen_class = wcpf_chosen_class();
	}
	if($this.attr("data-key") == "cat" && $this.attr("data-widget") == "cat" ){
		$this.closest("li").siblings().removeClass(chosen_class);
	}
	$this.closest("li").toggleClass(chosen_class);
	var widget = $this.attr("data-widget");
	var type = $this.attr("data-type");
	var key = $this.attr("data-key");
	var value = $this.attr("data-value");
	
	var new_key = widget + "_" + key; // key for url
	var current_value = codenegar_get_parameter(new_key);
	//current_value += "," + value;
	if(current_value != "" && new_key != "cat_cat"){
		current_value = current_value.split(",");
	}else{
		current_value =[];
	}
	var value_index = jQuery.inArray(value, current_value);
	if(value_index > -1){  // if duplicated remove item to have toggle effect
		current_value.splice(value_index, 1);
	}else{
		current_value.push(value);
	}
	//current_value = jQuery.unique(current_value);
	new_value = current_value.join(",");
	if(new_key == "cat_cat" && codenegar_get_parameter("cat_cat") == new_value){
		new_value = ""; // toggle effect
	}
	codenegar_wcpf_add_parameter(widget, type, key, new_value);
});

jQuery(".codenegar_product_filter_wrap select").live("change" ,function(){
	var $this = jQuery('option:selected', this);
	var widget = $this.attr("data-widget");
	var type = $this.attr("data-type");
	var key = $this.attr("data-key");
	var value = $this.val();
	codenegar_wcpf_add_parameter(widget, type, key, value);
});

	var state = History.getState();
	var current_url = state.cleanUrl;
	var has_pushstate = !!(window.history && history.pushState);
	var has_hash = !!(current_url.indexOf("#?") != -1);
	var cnpf = !!(current_url.indexOf("cnpf") != -1);
	if(!has_pushstate && has_hash && cnpf){
		// load current url data by ajax
		current_url = current_url.replace('#?', "?");
		if(codenegar_page_title.length==0){
			codenegar_page_title = jQuery("title").text();
		}
		History.pushState({state:1}, codenegar_page_title, current_url);
	}
});

(function(window,undefined){

    // Prepare
    var History = window.History; // Note: We are using a capital H instead of a lower h
    if ( !History.enabled ) {
         // History.js is disabled for this browser.
         // This is because we can optionally choose to support HTML4 browsers or not.
        return false;
    }

    // Bind to StateChange Event
    History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
        var State = History.getState(); // Note: We are using History.getState() instead of event.state
        //History.log(State);
		var selector = ".codenegar-shop-loop-wrapper";
		var $wrap = jQuery(".codenegar-shop-loop-wrapper");
		if($wrap.length==0){
			// theme is not standard an missing WooCommerce actions/hooks
			/* find main ul list */
			selector = wcpf_get_main_ul();
			$wrap = jQuery(selector);
		}
		if(codenegar_get_theme_selector().length>0){
			// theme is not standard an missing WooCommerce actions/hooks
			$wrap = jQuery(codenegar_get_theme_selector());
			selector = codenegar_get_theme_selector();
		}
		// if wrapper is not proper, it will be overrided
		if(wcpf_auto_override_selector()){
			$wrap = jQuery(codenegar_get_theme_selector());
			selector = codenegar_get_theme_selector();
		}
		
		// if admin has overrided the selector it has the top priority
		if(codenegar_wcpf_config.wrapper_selector.length>0){
			$wrap = jQuery(codenegar_wcpf_config.wrapper_selector);
			selector = codenegar_wcpf_config.wrapper_selector;
		}

		// Reload entire body
		if(codenegar_wcpf_config.reload_entire_page == 'yes'){
			window.location.href = State.cleanUrl;
		}

		// Add AJAX spinner overlay to the product container
		// {} means use the default spinner options
		codenegar_add_overlay(jQuery(selector), {});

		if (typeof wcsl_before_ajax == 'function') {
		  wcsl_before_ajax();
		}
		if (typeof wcpf_before_ajax == 'function') {
		  wcpf_before_ajax();
		}

		// now load filtered products
		jQuery.get(codenegar_encode_url(State.cleanUrl), function(data) {
			var $data = jQuery(data);
			
			var shop_loop = $data.find(selector);
			
			if(shop_loop.length>0){
				$wrap.hide().html(shop_loop.html()).fadeIn();
			}else{
				if(codenegar_wcpf_config.display_no_products_message=='yes'){
					$wrap.hide().html(codenegar_wcpf_config.no_products_message).fadeIn();
				}
			}
			if($data.find(selector).children(".codenegar-shop-pagination-wrapper").length==0){
				jQuery(".codenegar-shop-pagination-wrapper").hide().html($data.find(".codenegar-shop-pagination-wrapper")).fadeIn();
			}
			
			// woocommerce-result-count is another standard css class
			if($data.find(selector).children(".woocommerce-result-count").length==0){
				jQuery(".woocommerce-result-count").hide().html($data.find(".woocommerce-result-count")).fadeIn();
			}
			
			// update non-standard pagination
			// if pagination is not inside main wrap update it
			var paging_selector = ".pagination, .page-links, .paginator, .woo-pagination, .woocommerce-pagination, .emm-paginate, .wp-pagenavi, .pagination-wrapper, .general-pagination, .template-pagination";
			var update_pagination = $data.find(".codenegar-shop-loop-wrapper").children(paging_selector).length==0 || codenegar_update_pagination();
			if(codenegar_update_pagination() === false){
				update_pagination = false;
			}
			if (typeof wcpf_update_pagination == 'function') { 
			  update_pagination = wcpf_update_pagination(); 
			}
			if(update_pagination){
				var new_pagination = $data.find(paging_selector);
				new_pagination = codenegar_correct_cnep(new_pagination);
				jQuery(paging_selector).hide().html(new_pagination).fadeIn();
			}
			if(update_pagination && codenegar_wcpf_config.hide_duplicate_pagination=='yes'){
				// Hide possible duplicate pagination links
				var duplicate_pagination = paging_selector + ':nth-child(2)';
				duplicate_pagination = duplicate_pagination.replace(/,/g,':nth-child(2),');
				jQuery(duplicate_pagination).hide();
			}
			var selected_cat = 0;
			var url = State.cleanUrl;
			var last_parameter = url.substring(url.lastIndexOf('&') + 1);	
			if(last_parameter.length>0 && last_parameter.indexOf("=")>-1){
				last_parameter = last_parameter.split("=");
				if(last_parameter.length == 2 && last_parameter[0]== "cat_cat"){
					selected_cat = last_parameter[1];
				}
			}
			if(parseInt(selected_cat)>0 || selected_cat=="0"){
				var clicked_widget = jQuery('.codenegar_product_filter_wrap ul li a[data-value='+ selected_cat +']').closest(".codenegar_product_filter_wrap");
				var updated_widget = $data.find('.codenegar_product_filter_wrap ul li a[data-old-value='+ selected_cat +']').closest(".codenegar_product_filter_wrap").children();
				if(updated_widget.length>0){
					clicked_widget.hide().html(updated_widget).fadeIn();
				}
			}
			// update count of filters and hide empty items
			update_filters_count($data);
			hide_empty_widgets();
			// update custom areas via function
			if (typeof wcsl_on_update == 'function' || typeof wcpf_on_update == 'function') {
				var areas = [];
				if(typeof wcpf_on_update == 'function'){
					areas = wcpf_on_update();
				}else{
					areas = wcsl_on_update();
				}
			  var areas = wcsl_on_update();
			  var length = areas.length;
			  for (var i = 0; i < length; i++){
					var element = areas[i];
					// if(element.length==0){
						// continue;
					// }
					var updated_area = $data.find(element).html();
					if(updated_area && updated_area.length>0){
					jQuery(element).hide().html(updated_area).fadeIn();
					}else{
						jQuery(element).hide().html("");
					}
				}
			}

			// Update custom areas for some specific non standard themes
			var theme_custom_areas = codenegar_get_theme_custom_areas();
			if(theme_custom_areas.length>0){
				var areas = codenegar_get_theme_custom_areas().split(",");
				var length = areas.length;
				for (var i = 0; i < length; i++){
					var element = areas[i];
					var updated_area = $data.find(element).html();
					if(updated_area && updated_area.length>0){
						jQuery(element).hide().html(updated_area).fadeIn();
					}else{
						jQuery(element).hide().html("");
					}
				}
			}

			// Update custom areas via option
			// User's areas are processed at the end to have more priority
			if(codenegar_wcpf_config.custom_areas.length>0){
				var areas = codenegar_wcpf_config.custom_areas.split(",");
				var length = areas.length;
				for (var i = 0; i < length; i++){
					var element = areas[i];
					var updated_area = $data.find(element).html();
					if(updated_area && updated_area.length>0){
						jQuery(element).hide().html(updated_area).fadeIn();
					}else{
						jQuery(element).hide().html("");
					}
				}
			}

			
		}).done(function() {
			// Remove AJAX spinner overlay
			codenegar_remove_overlay(jQuery(selector));

			//scroll to the wrapper selector if scroll is enabled
			if(codenegar_wcpf_config.scroll_to_top=='yes'){
				jQuery(selector).ScrollTo();
			}
			if (typeof wcsl_ajax_done == 'function') { 
			  wcsl_ajax_done(); 
			}
			if (typeof wcpf_ajax_done == 'function') { 
			  wcpf_ajax_done(); 
			}
			})
		  .fail(function() {
			if (typeof wcsl_ajax_fail == 'function') { 
			  wcsl_ajax_fail(); 
			}
			if (typeof wcpf_ajax_fail == 'function') { 
			  wcpf_ajax_fail(); 
			}
			//if(codenegar_wcpf_config.display_no_products_message=='yes'){
				$wrap.hide().html(codenegar_wcpf_config.no_products_message).fadeIn();
			//}
		   })
		 .always(function() {
			//update_filters_count($data);
			if (typeof wcsl_after_ajax == 'function') {
			  wcsl_after_ajax(); 
			}
			if (typeof wcpf_after_ajax == 'function') {
			  wcpf_after_ajax(); 
			}
		   });
    });

})(window);

(function($) {
	$(document).ready(function() {
		$(function() {
			// use "live" to make WooCommerce default orderby work after ajax loading
			jQuery("select.orderby").live("change",function(){
				if(jQuery(this).hasClass("codenegar_product_filter_orderby")) return;
				jQuery(this).closest("form").submit();
			});
			
		});
	});
})(jQuery);