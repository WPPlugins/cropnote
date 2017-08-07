<?php
$nonce = $_REQUEST['ajaxNonce'];
if( !wp_verify_nonce($nonce, 'cropnote-ajax-nonce') ) {
	die('You do not have permission for that request.');
}

$action = isset($_REQUEST['cropnote_action']) ? trim($_REQUEST['cropnote_action']) : '';
$options = get_option('cropnote_settings');

define('cn_table', 'cropnote');

if($action == "get") {
	getResults();	
} else if($action == "save") {
	getSave();	
} else if($action == "delete") {
	getDelete();	
}
exit;

function getSave() {
	//save image note
	$imgID = isset($_REQUEST['imgid']) ? trim($_REQUEST['imgid']) : '';	
	$postID = isset($_REQUEST['postid']) ? trim($_REQUEST['postid']) : 0;	
	
	//get data from jQuery
	$data = array(
		$_GET["top"],
		$_GET["left"],
		$_GET["width"],
		$_GET["height"],
		html2txt($_GET["text"]),
		$_GET["id"],
		$_GET["author"],
		$_GET["email"],
		$_GET["imageWidth"],
		$_GET["imageHeight"]
	);	
	
	global $wpdb;
	$nonce = wp_create_nonce('cropnote-ajax-nonce');
	$table_name = $wpdb->prefix . cn_table;
	if(can_user('cropnote_add') || can_user('cropnote_add_html') || can_user('cropnote_add_autoapprove')) {
		if($data[5] != "new") { //if image note is not new will delete the old image note
			$author = $wpdb->get_var($wpdb->prepare("SELECT note_author FROM %s WHERE note_img_ID = '%s' and note_text_ID='%s'", $table_name, $imgID, $data[5]) );
	
			if( can_user('cropnote_edit_all') || (can_user('cropnote_edit_own') && wp_get_current_user()->ID == $author) ) {
				//find the old image note
				//delete image note
				$wpdb->query($wpdb->prepare("DELETE FROM %s WHERE note_img_ID='%s' and note_text_ID='%s'", $table_name, $imgID, $data[5]) );
			}	else {
				echo '{"ajaxNonce": "'.$nonce.'"}';
				return;
			}	
		} else {
			//if image note is new
			$comment_post_ID = $postID;		
			$comment_author       = ( isset($_GET['author']) )  ? trim(strip_tags($_GET['author'])) : null;
			$comment_author_email = ( isset($_GET['email']) )   ? trim($_GET['email']) : null;
			$comment_author_url   = ( isset($_GET['url']) )     ? trim($_GET['url']) : null;
			$comment_content      = $data[4];
			
			//If the user is logged in, get author name and author email
			$user = wp_get_current_user();
			if ( $user->ID ) {
				if ( empty( $user->display_name ) )
					$user->display_name=$user->user_login;
				$comment_author       = $user->ID;
				$comment_author_email = $wpdb->escape($user->user_email);
				$comment_author_url   = $wpdb->escape($user->user_url);
				/*if ( current_user_can('unfiltered_html') ) {
					if ( wp_create_nonce('unfiltered-html-comment_' . $comment_post_ID) != $_POST['_wp_unfiltered_html_comment'] ) {
						kses_remove_filters();
						kses_init_filters();
					}
				}*/
			}
			else $comment_author = 69;		
		}
		
	 	$wpdb->query($wpdb->prepare("INSERT INTO `".$table_name."`
										(
											`note_img_ID`,
											`note_comment_ID`,
											`note_author`,
											`note_email`,
											`note_top`,
											`note_left`,
											`note_width`,
											`note_height`,
											`image_width`,
											`image_height`,
											`note_text`,
											`note_text_ID`,
											`note_editable`,
											`note_approved`,
											`note_date`
										)
										VALUES (
										%s,
										%s,
										%d,
										%s,
										%d,
										%d,
										%d,
										%d,
										%d,
										%d,
										%s,
										%s,
										%d,
										%d,", 
										$imgID,
										$comment_id,
										$comment_author,
										$comment_author_email,
										$data[0],
										$data[1],
										$data[2],
										$data[3],
										$data[8],
										$data[9],
										$data[4],
										"id_".md5($data[4]),
										1,
										can_user('cropnote_add_autoapprove') ? 1 : 0).'now())');

	}
	//output JSON array
	echo '{ "annotation_id": "id_'.md5($data[4]).'", "ajaxNonce": "'.$nonce.'" }';
}

//delete image note
function getDelete() {
	global $wpdb;
	$nonce = wp_create_nonce('cropnote-ajax-nonce');
	$table_name = $wpdb->prefix . cn_table;
	$qsType = isset($_REQUEST['imgid']) ? trim($_REQUEST['imgid']) : '';
	$author = $wpdb->get_var($wpdb->prepare("SELECT note_author FROM %s WHERE note_img_ID = '%s' and note_text_ID='%s'", $table_name, $qsType, $_GET["id"]) );
	
	if( can_user('cropnote_edit_all') || (can_user('cropnote_edit_own') && wp_get_current_user()->ID == $author) ) {
		//delete note
		$wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE note_img_ID=%s and note_text_ID=%s", $qsType, $_GET["id"]) );
	}
	else die('Failed to delete note.');
	echo '{"ajaxNonce": "'.$nonce.'"}';
}

//get image note
function getResults() {
	//create table at fisrt
	$qsType = isset($_REQUEST['imgid']) ? trim($_REQUEST['imgid']) : '';
	
	global $wpdb;
	$table_name = $wpdb->prefix . cn_table;
	$sql = $wpdb->prepare("SELECT * FROM $table_name WHERE note_img_ID = '%s' ", $qsType);
	$result = $wpdb->get_results($sql);
	
	//output JSON array
	global $options;
	$nonce = wp_create_nonce('cropnote-ajax-nonce');
	echo '{"ajaxNonce": "'.$nonce.'", "notes": ';
	echo "[";
	if( can_user('cropnote_read')) {
		$next = "";
		$numItems = count($result);
		$i = 0;
		foreach ($result as $topten) {
			if($topten->note_approved == 1) {
				//add gravatar
				echo $next;
				$notetext = txt2html($topten->note_text);
				$author = get_user_by('id', $topten->note_author)->display_name;
				$out = "";
				$out .= "{\"top\": ".(int)$topten->note_top.", \"left\": ".(int)$topten->note_left.", \"width\": ".(int)$topten->note_width.", \"height\": ".(int)$topten->note_height;
				$out .= ", \"imageWidth\": ".(int)$topten->image_width.", \"imageHeight\": ".(int)$topten->image_height.", \"text\": \"".$notetext;
				$out .= "\", \"id\": \"".$topten->note_text_ID."\", \"editable\": true";
				
				if( $options['author']) {
					$out .=", \"author\": \"<div class='image-annotate-author'>";
					if( $options['gravatar'] ) {
						$out .= get_avatar($topten->note_email, 20, $defaultgravatar)." ".$author;
					} else {
						$out .= $author;
					}
					$out .= "</div>\"";
				}
 				$out .= "}";
				echo $out;
			} else {
				$next = "";
			}
			$i++;
			if($i != $numItems) {
				$next = ",";
			}
		}
	}
	echo "]}";
}

function html2txt($text) {
	$search = array ('@<script[^>]*?>.*?</script>@si',
			 '@<[\/\!]*?[^<>]*?>@si',
			 '@([\r\n])[\s]+@',
			 '@&(quot|#34);@i',
			 '@&(lt|#60);@i',
			 '@&(gt|#62);@i',
			 '@&(nbsp|#160);@i',
			 '@&#(\d+);@e');		

	$replace = array ('',
			 '',
			 '\1',
			 '"',
			 '<',
			 '>',
			 ' ',
			 'chr(\1)');
	
	$string = trim(preg_replace($search, $replace, $text));
	$newstring = str_replace(array("\r\n", "\r", "\n"), ' ', $string);
	return $newstring;
}

function txt2html( $string )
{
  $string = str_replace ( '\\', '', $string );
  $string = str_replace ( '"', '\"', $string );
  $string = str_replace(array("\r\n", "\r", "\n"), '\\n', $string);
  return $string;
}

if(!function_exists('can_user')) {
	function can_user($cap)
	{
		if(is_user_logged_in()) {
			return current_user_can($cap);
		}
		else {
			global $options;
			$nl = $options['anonymous_permissions'];
			return $nl[$cap];
		}
	}
}
?>