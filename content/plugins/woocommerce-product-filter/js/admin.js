/*!
 * CodeNegar WooCommerce AJAX Product Filter 
 *
 * Admin Panel Script File
 *
 * @package	WooCommerce AJAX Product Filter
 * @author	Farhad Ahmadi
 * @link	http://codenegar.com/go/wcpf
 * version	2.8.0
 */
 
  jQuery(function() {
	jQuery("#sidebars tr a.remove_row").live("click", function(){
		var $this = jQuery(this).closest("tr");
		var index = parseInt($this.attr("data-row-id"));
		sidebars[index] = {};
		$this.remove();
		var json = JSON.stringify(sidebars);
		json = json.replace(/"/g, "'");
		jQuery("input#sidebars").val(json);
	});
	
	jQuery("#visible_to").live("change", function(){
		var $this = jQuery(this);
		if($this.val()=='cat'){
			jQuery("#cat_drp").show();
			jQuery("#attr_drp").hide();
		}else if($this.val()=='attr'){
			jQuery("#cat_drp").hide();
			jQuery("#attr_drp").show();
		}else if($this.val()=='all'){
			jQuery("#cat_drp").hide();
			jQuery("#attr_drp").hide();
		}
	});
    var title = jQuery( "#sidebar_title" ),
      visible_to = jQuery( "#visible_to" ),
      attrib = jQuery( "#attrib" ),
      cat = jQuery( "#cat" ),
      allFields = jQuery( [] ).add( title ).add( visible_to ).add( attrib ).add( cat ),
      tips = jQuery( ".validateTips" );
 
    function checkLength( o, n, min, max ) {
      if ( o.val().length > max || o.val().length < min ) {
        o.addClass( "ui-state-error" );
        return false;
      } else {
        return true;
      }
    }
	
	function insert_sidebar(title, visible_to, apply_to){
		sidebar = {title: title, visible_to: visible_to, apply_to: apply_to};
		sidebars.push(sidebar);
	}
 
    jQuery( "#dialog-form" ).dialog({
      autoOpen: false,
      width: 380,
      height: 420,
      modal: true,
      buttons: [{
		text: wcpf_words.create_sidebar,
        click: function() {
          var bValid = true;
          allFields.removeClass( "ui-state-error" );
          bValid = bValid && checkLength( title, "title", 1, 30 );
          if ( bValid ) {
			var count = sidebars.length;
			var apply_to = 0;
			if(visible_to.val()=='cat'){
				apply_to = (cat.val() != null)? cat.val() : 0;
			}else if(visible_to.val()=='attr'){
				apply_to = (attrib.val() != null)? cat.val() : 0;
			}
            jQuery( "#sidebars tbody" ).append( '<tr data-row-id="' + count + '">' +
              "<td>" + title.val().toLowerCase() + "</td>" +
              "<td>" + visible_to.find('option:selected').text() + "</td>" +
              "<td>" + '[ajax_product_filter data=' + title.val().toLowerCase() + ':' + visible_to.val() + ':' + apply_to + ']' + "</td>" +
              "<td>" + '<a href="#" class="remove_row">' + wcpf_words.remove + '</a>' + "</td>" +
            "</tr>" );
			
			insert_sidebar(title.val().toLowerCase(), visible_to.val(), apply_to);
			var json = JSON.stringify(sidebars);
			json = json.replace(/"/g, "'");
			jQuery("input#sidebars").val(json);
			
            jQuery( this ).dialog( "close" );
          }
        }},
		{
        text: wcpf_words.cancel,
		click: function() {
          jQuery( this ).dialog( "close" );
        }
		}
      ],
      close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
      }
    });
 
    jQuery( "#create-sidebar" )
      .button()
      .click(function(e) {
		e.preventDefault();
        jQuery( "#dialog-form" ).dialog( "open" );
      });
  });

jQuery(function() {
    jQuery("form input").keypress(function (e) {
        if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
            jQuery('input[type=submit].default_submit').click();
            return false;
        } else {
            return true;
        }
    });
});
css_editor = '';
js_editor = '';
jQuery(document).ready(function($) {
    css_editor = ace.edit("div_custom_css");
    css_editor.getSession().setMode("ace/mode/css");
    css_editor.setValue($("#custom_css").val());
    css_editor.getSession().on('change', function(e) {
        $("#custom_css").val( css_editor.getValue());
    });
    js_editor = ace.edit("div_custom_js");
    js_editor.getSession().setMode("ace/mode/javascript");
    js_editor.setValue($("#custom_js").val());
    js_editor.getSession().on('change', function(e) {
        $("#custom_js").val( js_editor.getValue());
    });
});