<?php
/**
 * helpers.php
 *
 * Contains various helper functions for generating markup.
 *
 * @package rdmgumby
 */

function generate_phone_link($phone) {
	$stripped_phone = str_replace(["-", "(", ")", " "], "", $phone);
	return "<a href='tel:" . $stripped_phone . "'>" . $phone . "</a>";
}

function flux_cant_delete_me( $id ){
	$cant_delete_ids = array( 2, 3 );

	if ( in_array( $id, $cant_delete_ids ) )
		wp_die( '<h2 style="text-align:center;">'.'OOPs!'.'</h2>'.'<p style="text-align:center;">'.'Seems you are attempting an operation that is not possible.'.'</p>');
}

add_action('delete_user', 'flux_cant_delete_me');

function flux_login_logo() {
echo '<style type="text/css">
.login form{border-radius: 6px;}
.login h1 a { background-image: url('.get_stylesheet_directory_uri().'/assets/img/logo.png) !important;background-position: center center !important;background-repeat: no-repeat !important;background-size: contain !important; height: 3em; width: 100%;}
#nav, #backtoblog{font-size: 10px !important;}
.login #nav{float: right !important;}
#backtoblog{margin: 24px 0 !important;}
.login #backtoblog a:hover, .login #nav a:hover, .login h1 a:hover{color: #d94a2e ;}
.login #backtoblog, .login #nav{padding:0 !important;}
.wp-core-ui .button-primary {background: #333333 !important;
	 border-color: #333333 #000000 #000000 !important;
	 -webkit-box-shadow: 0 1px 0 #000000 !important;
	 box-shadow: 0 1px 0 #000000 !important;
	 color: #fff;
	 text-decoration: none;
		text-shadow: 0 -1px 1px #000000,1px 0 1px #000000,0 1px 1px #000000,-1px 0 1px #000000 !important;
}
.wp-core-ui .button-primay:hover{
 color: #fff !important;
 background-color: #000000 !important;
 border-color: #000000 !important;
}</style>';
}
add_action('login_head', 'flux_login_logo');
