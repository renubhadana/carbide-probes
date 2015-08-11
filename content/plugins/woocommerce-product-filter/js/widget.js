/*!
 * CodeNegar WooCommerce AJAX Product Filter 
 *
 * Admin Widget Script File
 *
 * @package	WooCommerce AJAX Product Filter
 * @author	Farhad Ahmadi
 * @link	http://codenegar.com/go/wcpf
 * version	2.8.0
 */

function update_form(element){
	var val = element.val();
	if(val=='slider'){
		element.closest(".widget-content").children(".slider_option").slideDown();
	}else if(val=='list' || val=='dropdown'){ // we want to make sure to targt the right dropwon, because WordPress add same class to all dropdown in widgets
		element.closest(".widget-content").children(".slider_option").slideUp();
	}
}

(function($) {
$(document).ready(function() {
	$(function() {
		 $('.widget_type').live("change", function(){
			 update_form($(this));
		 });
	});
	
	$(function() {
		 $('.wcsl_attr_image').live("change", function(){
			var $this = $(this);
			$this.closest("form").find('[type="submit"]').trigger("click");
			 
		 });
	});
	
});
})(jQuery);