<?php 
/*
Plugin Name: Cropnote
Plugin URI: http://jeffandkelly.net/wordpress/cropnote-pretty-photo-image-annotations-for-wordpress-and-jquery/
Description: Allows you to add textual annotations to prettyPhoto popup images. Based on Demon Image Annotations by Demon
Author: Jeff Meadows
Author URI: http://jeffandkelly.net/wordpress/
Version: 1.0.4
*/

//*************** Header function ***************
function load_jquery_js() {
	wp_deregister_script('jquery');
	wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js');
    wp_enqueue_script( 'jquery' );
	
	$plugindir = plugin_dir_url(__FILE__);
	wp_deregister_script('jcrop');
	wp_register_script('jcrop', $plugindir ."/js/jquery.Jcrop.min.js", array('jquery'));
	wp_enqueue_script('jcrop');

	wp_deregister_script('prettyPhoto');
	wp_register_script('prettyPhoto', $plugindir ."/js/jquery.prettyPhoto.min.js", array('jquery'));
	wp_enqueue_script('prettyPhoto');

	wp_deregister_script('md5');
	wp_register_script('md5', $plugindir ."/js/jquery.md5.js", array('jquery'));
	wp_enqueue_script('md5');

	wp_deregister_script('cropnote');
	wp_register_script('cropnote', $plugindir ."/js/jquery.cropnote.js",array('jquery', 'jcrop', 'md5'));
	wp_enqueue_script('cropnote');

	wp_deregister_style('jcrop');
	wp_register_style('jcrop', $plugindir.'/css/jquery.Jcrop.min.css');
	wp_enqueue_style('jcrop');

	wp_deregister_style('prettyPhoto');
	wp_register_style('prettyPhoto', $plugindir.'/css/jquery.prettyPhoto.min.css');
	wp_enqueue_style('prettyPhoto');

	wp_deregister_style('cropnote');
	wp_register_style('cropnote', $plugindir.'/css/jquery.cropnote.css');
	wp_enqueue_style('cropnote');
}

function load_image_annotation_js() {
	$options = get_option('cropnote_settings');
	
	function ae_detect_ie()
	{
		if (isset($_SERVER['HTTP_USER_AGENT']) && 
		(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
			return true;
		else
			return false;
	}
	?>
    <script language="javascript">
	<?php if( ($options[enabled]) ) { ?>
		jQuery.noConflict();
		
		var cropnote_nonce = "<?php echo wp_create_nonce('cropnote-ajax-nonce'); ?>";
		
		jQuery(document).ready(function($) {
		    var opts = { theme: '<?php echo $options['prettyPhoto']['theme']; ?>', <?php $custom = $options['prettyPhoto']['custom']; echo empty($custom) ? '' : $custom.','; ?> changepicturecallback: function() { appendToChildren() }, callback: function() {
		    	var close = $('.cropnote-close');
		    	if (close) close.click();
		    }};
		    
		    $("a[rel^='<?php echo $options['prettyPhoto']['rel']; ?>']").prettyPhoto(opts);
		});
				
		function appendToChildren() {
				//image annotaion
				jQuery("<?php echo $options['selector']; ?> img").each(function() {						
						var idname = jQuery(this).attr("id")
						var source = jQuery(this).attr('src');
						
						if(idname.substring(4,idname.length) != 'exclude') {
							//check if image annotation addable attribute exist
							var src = jQuery(this).attr('src');
							var addablecon = jQuery(".addable").find("a").is(function(index) {
								return jQuery(this).attr('href') == src;
							});
														
							//disable if image annotation addable for admin only
							<?php if (can_user('cropnote_add')) { ?>addablecon = true;<?php } ?>
							
							var addablepage = true;
							var editable = true;
							
							//find image link if exist
							var imagelink = jQuery(this).parent("a").attr('href');
							var imgid = ""
								
							//auto insert image id attribute
							<?php if( $options['automatic_id'] ) { ?>imgid = jQuery(this).getMD5(source);
								<?php if( $options['automatic_post_id'] ) { ?>var postid = <?php global $wp_query; $thePostID = $wp_query->post->ID; echo $thePostID ? $thePostID : 0; ?>;
									imgid = "img-" + postid + "-" + imgid.substring(0,10);
								<?php } else { ?>imgid = "img-" + imgid.substring(0,10);
								<?php }; ?>
							<?php }; ?>
							
							//replace if image id attribute exist
							if(idname.substring(0,4) == "img-") {
								imgid = idname;
							}
							
							if(imgid.substring(0,4) == "img-") {
								//deactive the lnik if exist
								jQuery(this).parent("a").removeAttr("href");
								jQuery(this).parent("a").removeAttr("title");
								
								jQuery(this).attr("id", imgid);
								jQuery(this).wrap(jQuery('<div id=' + imgid.substring(4,imgid.length) + ' ></div>'));
							
								jQuery(this).cropnote({
									getPostID: <?php global $wp_query; $thePostID = $wp_query->post->ID; echo $thePostID; ?>,
									getImgID: imgid,
									pluginUrl: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
									editable: <?php get_currentuserinfo(); if (can_user('cropnote_edit_own') || can_user('cropnote_edit_all')) { ?>editable<?php } else { ?> false <?php } ?>,
									addable: addablecon,
									showActionMessage: <?php $use = $options['use_action_message']; if($use === false) echo 'false'; else if ($use === true) echo 'true'; else echo is_mobile() ? 'true' : 'false'; ?>,
									actionMessage: '<?php echo $options['action_message'];?>',
									hover: <?php $clicks = $options['clicks']; if($clicks === true) echo 'false'; else if ($clicks === false) echo 'true'; else echo is_mobile() ? 'false' : 'true'; ?>,
									beforeAjax: function(jqXHR, settings) {
										if(cropnote_nonce == null) return false;
										settings.url += "&ajaxNonce=" + cropnote_nonce;
										cropnote_nonce = null;
									},
									afterAjax: function(jsonData) {
										cropnote_nonce = jsonData.ajaxNonce;
									}
								});
							}
						}
					
				});
		}
	<?php } ?>	
	</script>
    <?php
}

//*************** Comment function ***************
function getImgID() {
	global $comment;
	$commentID = $comment->comment_ID;
	
	global $wpdb;
	$table_name = $wpdb->prefix . "cropnote";
	$imgIDNow = $wpdb->get_var("SELECT note_img_ID FROM ".$table_name." WHERE note_comment_id = ".(int)$commentID);
	
	if($imgIDNow != "") {
		$str = substr($imgIDNow, 4, strlen($imgIDNow));
		echo "<div id=\"comment-".$str."\"><a href='#".$str."'>noted on #".$imgIDNow."</a></div>";
	} else {
		echo "&nbsp;";	
	}
}

function is_mobile() {

	// Get the user agent

	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	// Create an array of known mobile user agents
	// This list is from the 21 October 2010 WURFL File.
	// Most mobile devices send a pretty standard string that can be covered by
	// one of these.  I believe I have found all the agents (as of the date above)
	// that do not and have included them below.  If you use this function, you 
	// should periodically check your list against the WURFL file, available at:
	// http://wurfl.sourceforge.net/


	$mobile_agents = Array(
		"240x320",
		"acer",
		"acoon",
		"acs-",
		"abacho",
		"ahong",
		"airness",
		"alcatel",
		"amoi",	
		"android",
		"anywhereyougo.com",
		"applewebkit/525",
		"applewebkit/532",
		"asus",
		"audio",
		"au-mic",
		"avantogo",
		"becker",
		"benq",
		"bilbo",
		"bird",
		"blackberry",
		"blazer",
		"bleu",
		"cdm-",
		"compal",
		"coolpad",
		"danger",
		"dbtel",
		"dopod",
		"elaine",
		"eric",
		"etouch",
		"fly " ,
		"fly_",
		"fly-",
		"go.web",
		"goodaccess",
		"gradiente",
		"grundig",
		"haier",
		"hedy",
		"hitachi",
		"htc",
		"huawei",
		"hutchison",
		"inno",
		"ipad",
		"ipaq",
		"ipod",
		"jbrowser",
		"kddi",
		"kgt",
		"kwc",
		"lenovo",
		"lg ",
		"lg2",
		"lg3",
		"lg4",
		"lg5",
		"lg7",
		"lg8",
		"lg9",
		"lg-",
		"lge-",
		"lge9",
		"longcos",
		"maemo",
		"mercator",
		"meridian",
		"micromax",
		"midp",
		"mini",
		"mitsu",
		"mmm",
		"mmp",
		"mobi",
		"mot-",
		"moto",
		"nec-",
		"netfront",
		"newgen",
		"nexian",
		"nf-browser",
		"nintendo",
		"nitro",
		"nokia",
		"nook",
		"novarra",
		"obigo",
		"palm",
		"panasonic",
		"pantech",
		"philips",
		"phone",
		"pg-",
		"playstation",
		"pocket",
		"pt-",
		"qc-",
		"qtek",
		"rover",
		"sagem",
		"sama",
		"samu",
		"sanyo",
		"samsung",
		"sch-",
		"scooter",
		"sec-",
		"sendo",
		"sgh-",
		"sharp",
		"siemens",
		"sie-",
		"softbank",
		"sony",
		"spice",
		"sprint",
		"spv",
		"symbian",
		"tablet",
		"talkabout",
		"tcl-",
		"teleca",
		"telit",
		"tianyu",
		"tim-",
		"toshiba",
		"tsm",
		"up.browser",
		"utec",
		"utstar",
		"verykool",
		"virgin",
		"vk-",
		"voda",
		"voxtel",
		"vx",
		"wap",
		"wellco",
		"wig browser",
		"wii",
		"windows ce",
		"wireless",
		"xda",
		"xde",
		"zte"
	);

	// Pre-set $is_mobile to false.

	$is_mobile = false;

	// Cycle through the list in $mobile_agents to see if any of them
	// appear in $user_agent.

	foreach ($mobile_agents as $device) {

		// Check each element in $mobile_agents to see if it appears in
		// $user_agent.  If it does, set $is_mobile to true.

		if (stristr($user_agent, $device)) {

			$is_mobile = true;

			// break out of the foreach, we don't need to test
			// any more once we get a true value.

			break;
		}
	}

	return $is_mobile;
}

if(!function_exists('can_user')) {
	function can_user($cap)
	{
		if(is_user_logged_in()) {
			return current_user_can($cap);
		}
		else {
			$nl = get_option('cropnote_settings');
			$nl = $nl['anonymous_permissions'];
			return $nl[$cap];
		}
	}
}

//add_action('wp_head', 'load_image_annotation_js');
add_action('wp_enqueue_scripts', 'load_jquery_js');
add_action('wp_head', 'load_image_annotation_js');

//register for activate/deactivate/uninstall
register_activation_hook( __FILE__, 'cropnote_activate');
register_deactivation_hook( __FILE__, 'cropnote_deactivate');
register_uninstall_hook( __FILE__, 'cropnote_uninstall');

//*************** Admin function ***************
function cropnote_admin() {
	include('cropnote-admin.php');
}

function cropnote_ajax() {
	include_once('cropnote-ajax.php');
}

function cropnote_admin_actions() {
	add_menu_page('Cropnote', 'Cropnote', 'manage_options', 'cropnote', 'cropnote_admin', plugins_url('icon.png',__FILE__));
}

function cropnote_activate() {
	global $wpdb;
	$table_name = $wpdb->prefix . "cropnote";

	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql = "CREATE TABLE IF NOT EXISTS `".$table_name."` (
			`note_ID` int(11) NOT NULL AUTO_INCREMENT,
			`note_img_ID` varchar(30) NOT NULL,
			`note_comment_ID` int(11) NOT NULL,
			`note_author` int(11) NOT NULL,
			`note_email` varchar(100) NOT NULL,
			`note_top` int(11) NOT NULL,
			`note_left` int(11) NOT NULL,
			`note_width` int(11) NOT NULL,
			`note_height` int(11) NOT NULL,
			`image_width` int(11) NOT NULL,
			`image_height` int(11) NOT NULL,
			`note_text` text NOT NULL,
			`note_text_ID` varchar(100) NOT NULL,
			`note_editable` tinyint(1) NOT NULL,
			`note_approved` varchar(20) DEFAULT '1',
			`note_date` datetime NOT NULL,
			PRIMARY KEY (`note_ID`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	//setup roles.  everyone can read, contributors+ can add coments, authors+ can do so without approval, editors+ can edit/delete, admin+ can add html comments
	global $wp_roles;
	$role = $wp_roles->get_role('administrator');
	$role->add_cap('cropnote_add');
	$role->add_cap('cropnote_add_autoapprove');
	$role->add_cap('cropnote_add_html');
	$role->add_cap('cropnote_edit_all');
	$role->add_cap('cropnote_edit_own');
	$role = $wp_roles->get_role('editor');
	$role->add_cap('cropnote_add');
	$role->add_cap('cropnote_add_autoapprove');
	$role->add_cap('cropnote_edit_all');
	$role->add_cap('cropnote_edit_own');
	$role = $wp_roles->get_role('author');
	$role->add_cap('cropnote_add');
	$role->add_cap('cropnote_add_autoapprove');
	$role->add_cap('cropnote_edit_own');
	$role = $wp_roles->get_role('contributor');
	$role->add_cap('cropnote_add');
	$role = $wp_roles->get_role('subscriber');
	
    $roles = $wp_roles->get_names();
	foreach($roles as $r => $name) {
		$role = $wp_roles->get_role($r);
		$role->add_cap('cropnote_read');								
	}
	
	//setup initial settings, don't update if the setting already exists
	$options = get_option('cropnote_settings');
	if($options === false) $options = array(
		'selector' => '#pp_full_res',
		'enabled' => true,
		'gravatar' => false,
		'author' => false,
		'automatic_id' => true,
		'automatic_post_id' => true,
		'clicks' => 'mobile',
		'action_message' => 'Click to load notes.',
		'use_action_message' => 'mobile',
		'anonymous_permissions' => array(
			'cropnote_read' => true,
			'cropnote_add' => false,
			'cropnote_add_html' => false,
			'cropnote_add_autoapprove' => false,
			'cropnote_edit_all' => false,
			'cropnote_edit_own' => false
			),
		'prettyPhoto' => array(
			'rel' => 'lightbox',
			'theme' => 'light_square',
			'custom' => 'social_tools: false, showTitle: true, overlay_gallery: false'
			)
		);
	else if (is_null($options['prettyPhoto'])) $options['prettyPhoto'] = array(
		'rel' => 'lightbox',
		'theme' => 'light_square',
		'custom' => 'social_tools: false, showTitle: true, overlay_gallery: false'
		);
	update_option('cropnote_settings', $options);
}

function cropnote_deactivate() {
}

function cropnote_uninstall() {
	global $wpdb;
	$table_name = $wpdb->prefix . "cropnote";
	
	//remove images from database
	$wpdb->query("drop table ".$table_name);
	
	//remove capabilities
	global $wp_roles;
	$roles = $wp_roles->get_names();
	foreach($roles as $role => $name) {
		$role = $wp_roles->get_role($role);
		$role->remove_cap('cropnote_read');
		$role->remove_cap('cropnote_add');
		$role->remove_cap('cropnote_add_html');
		$role->remove_cap('cropnote_add_autoapprove');
		$role->remove_cap('cropnote_edit_all');
		$role->remove_cap('cropnote_edit_own');
	}

	//remove settings
	delete_option('cropnote_settings');
}

if (is_admin()) {
	add_action('admin_menu', 'cropnote_admin_actions'); //hook admin menu creation
}
add_action('wp_ajax_nopriv_cropnote', 'cropnote_ajax'); //hook ajax requests
add_action('wp_ajax_cropnote', 'cropnote_ajax');
add_action('plugin_activated', 'cn_save_error');
function cn_save_error() {
	//update_option('cropnote_activation_error', ob_get_contents());
	file_put_contents('cn.activate.err.txt', ob_get_contents());
}
?>