<?php //tab settings 
	$tab = isset($_REQUEST['tab']) ? trim($_REQUEST['tab']) : 'settings';
	global $wpdb;
	$table_name = $wpdb->prefix . "cropnote";
?>
<style>
	div.pagination {
		padding: 3px;
		margin: 3px;
		text-align:center;
		font-family:Tahoma,Helvetica,sans-serif;
		font-size:.85em;
	}
	
	div.pagination a {
		border: 1px solid #ccdbe4;
		margin-right:3px;
		padding:2px 8px;

		background-position:bottom;
		text-decoration: none;

		color: #0061de;		
	}
	div.pagination a:hover, div.pagination a:active {
		border: 1px solid #2b55af;
		background-image:none;
		background-color:#3666d4;
		color: #fff;
	}
	div.pagination span.current {
		margin-right:3px;
		padding:2px 6px;
		
		font-weight: bold;
		color: #000;
	}
	div.pagination span.disabled {
		display:none;
	}
	div.pagination a.next{
		border:2px solid #ccdbe4;
		margin:0 0 0 10px;
	}
	div.pagination a.next:hover{
		border:2px solid #2b55af;
	}
	div.pagination a.prev{
		border:2px solid #ccdbe4;
		margin:0 10px 0 0;
	}
	div.pagination a.prev:hover{
		border:2px solid #2b55af;
	}
	#footer {
		bottom: auto;
	}

</style>

<div class="wrap">
<?php  echo "<h2>" . __( 'Cropnote Settings', 'cropnote_translations' ) . "</h2>"; ?>
Cropnote based on Demon Image Annotation: <a href="http://www.superwhite.cc" target="_blank">http://www.superwhite.cc</a><br />
<h2>
<a href="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>&tab=settings" class="nav-tab<?php $tab == 'settings' ? print " nav-tab-active" : '' ?>">Settings</a>
<a href="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>&tab=imagenotes" class="nav-tab<?php $tab == 'imagenotes' ? print " nav-tab-active" : ''; ?>">Image Notes</a>
<a href="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>&tab=permissions" class="nav-tab<?php $tab == 'permissions' ? print " nav-tab-active" : ''; ?>">Permissions</a>
</h2>
</div>
<?php if($tab == 'settings') {
		
		//admin settings
		if($_POST['cn_hidden'] == 'Y') {
			if (!wp_verify_nonce($_POST['_wpnonce'], 'cropnote_settings')) die('Security violation detected when trying to update cropnote settings.');
			
			$options = get_option('cropnote_settings');
			//post content wrapper
			$options['selector'] = $_POST['cn_selector'];
			
			//plugin status
			$options['enabled'] = $_POST['cn_enabled'];
			
			//image note gravatar
			$options['gravatar'] = $_POST['cn_gravatar'];
			
			//image note author
			$options['author'] = $_POST['cn_author'];
			
			//auto insert image id
			$options['automatic_id'] = $_POST['cn_automatic_id'];
			
			//post ID
			$options['automatic_post_id'] = $_POST['cn_automatic_post_id'];
			
			//disable hover for mobile
			$options['clicks'] = $_POST['cn_clicks'];
									
			//action message
			$options['action_message'] = $_POST['cn_action_message'];
			$options['use_action_message'] = $_POST['cn_use_action_message'];
			
			//action message
			$options['action_message'] = $_POST['cn_action_message'];
			$options['use_action_message'] = $_POST['cn_use_action_message'];
			
			//action message
			$options['prettyPhoto']['theme'] = $_POST['cn_pp_theme'];
			$options['prettyPhoto']['rel'] = $_POST['cn_pp_rel'];
			$options['prettyPhoto']['custom'] = $_POST['cn_pp_custom'];
			
			update_option('cropnote_settings', $options);
						?>
			<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
			<?php
		} 
			//Normal page display
			$options = get_option('cropnote_settings');
			$cn_selector = $options['selector'];
			$cn_enabled = $options['enabled'];
			$cn_gravatar = $options['gravatar'];
			$cn_author = $options['author'];
			$cn_automatic_id = $options['automatic_id'];
			$cn_automatic_post_id = $options['automatic_post_id'];
			$cn_clicks = $options['clicks'];
			$cn_action_message = $options['action_message'];
			$cn_use_action_message = $options['use_action_message'];
			$cn_prettyPhoto = $options['prettyPhoto'];
	?>
    
    <div class="wrap">
    <form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="cn_hidden" value="Y">        
        <?php wp_nonce_field('cropnote_settings') ?>
        <?php    echo "<h4>" . __( 'Cropnote Settings', 'cropnote_translations' ) . "</h4>"; ?>
        <table class="form-table" width="100%">
            <tr>
                <th>
                    <label><?php _e("Cropnote Status: " ); ?></label>
                </th>
             	<td>
              		<label><input name="cn_enabled" type="radio" value="true" <?php if ($cn_enabled) { echo 'checked="true"'; } ?> />Enabled</label> 
              		<label><input name="cn_enabled" type="radio" value="false" <?php if (!$cn_enabled) { echo 'checked="true"'; } ?> />Disabled</label>
                    <br />
                    <em>Disabling the plugin will hide notes but keep your settings intact.  This has a similar effect to deactivating the plugin.</em>
             	</td>
            </tr>
            
            <tr>
                <th>
                    <label><?php _e("jQuery Selector: " ); ?></label>
                </th>
                <td>
                    <input type="text" name="cn_selector" value="<?php echo $cn_selector; ?>" size="40"><br />
                    <span style="color:#C00">#IMPORTANT</span><br />
                    <em>jQuery selector describing where the images you want to annotate appear in your markup.<br />
                    #pp_full_res works with prettyPhoto and is the default.<br />
                    (Leaving it empty will cropnote all images.)<br /><br />
                    For more information on jQuery selectors, visit <a href="http://api.jquery.com/category/selectors/" target="_blank">http://api.jquery.com/category/selectors/</a></em>
                </td>
            </tr>
            
            <tr>
                <th>
                    <label><?php _e("Generate image ID automatically: " ); ?></label>
                </th>
              <td>
              		<label><input name="cn_automatic_id" type="radio" value="true" <?php if ($cn_automatic_id) { echo 'checked="true"'; } ?> />Enabled</label> 
              		<label><input name="cn_automatic_id" type="radio" value="false" <?php if (!$cn_automatic_id) { echo 'checked="true"'; } ?> />Disabled</label>
                    <br />
                    <em>Automatically generate the unique image ID (needed to store the notes) from the image's src attribute.<br />
                    Only disable this if you want to add the id attribute manually to your images, eg.</em><br />
                    <code>&lt;img id="img-4774005463" src="http://farm5.static.flickr.com/4121/4774005463_3837b6de44_o.jpg" /&gt;</code><br />
              </td>
            </tr>
            
            <tr>
                <th>
                    <label><?php _e("Unique notes per post: " ); ?></label>
                </th>
              <td>
              		<label><input name="cn_automatic_post_id" type="radio" value="true" <?php if ($cn_automatic_post_id) { echo 'checked="true"'; } ?> />Enabled</label> 
              		<label><input name="cn_automatic_post_id" type="radio" value="false" <?php if (!$cn_automatic_post_id) { echo 'checked="true"'; } ?> />Disabled</label>
                    <br />
                    <em>Store notes uniquely so you can have different notes on the same image in different posts/pages.<br />
                    Disabling this option will cause all notes for a certain image to show up on that image regardless of post.</em><br />
              </td>
            </tr>
                        
            <tr>
                <th>
                    <label><?php _e("Show note author info: " ); ?></label>
                </th>
              <td>
              		<label><input name="cn_author" type="radio" value="true" <?php if ($cn_author) { echo 'checked="true"'; } ?> />Enabled</label> 
              		<label><input name="cn_author" type="radio" value="false" <?php if (!$cn_author) { echo 'checked="true"'; } ?> />Disabled</label>
                    <br />
                    <em>Show the name of a note's author when displaying the note.</em><br />
              </td>
            </tr>
            
            <tr>
                <th>
                    <label><?php _e("Show note author gravatar: " ); ?></label>
                </th>
              <td>
              		<label><input name="cn_gravatar" type="radio" value="true" <?php if ($cn_gravatar) { echo 'checked="true"'; } ?> />Enabled</label> 
              		<label><input name="cn_gravatar" type="radio" value="false" <?php if (!$cn_gravatar) { echo 'checked="true"'; } ?> />Disabled</label>
                    <br />
                    <em>Show the author's gravatar when displaying the note.  This has no effect if author info is disabled.</em><br />
              </td>
            </tr>

            <tr>
                <th>
                    <label><?php _e("Use clicks instead of hover: " ); ?></label>
                </th>
              <td>
              		<label><input name="cn_clicks" type="radio" value="mobile" <?php if ($cn_clicks == 'mobile') { echo 'checked="true"'; } ?> />Only for mobile devices</label> 
              		<label><input name="cn_clicks" type="radio" value="true" <?php if ($cn_clicks === true) { echo 'checked="true"'; } ?> />Always</label>
              		<label><input name="cn_clicks" type="radio" value="false" <?php if ($cn_clicks === false) { echo 'checked="true"'; } ?> />Never</label>
                    <br />
                    <em>Show notes when the image is clicked, instead of when the mouse is over the image.</em><br />
              </td>
            </tr>

            <tr>
                <th>
                    <label><?php _e("Action message: " ); ?></label>
                </th>
              <td>
                    <input type="text" name="cn_action_message" value="<?php echo $cn_action_message; ?>" size="40"><br />
                    <em>Add a message above the image.  Especially useful when hover is disabled.</em><br />
                    <label><?php _e("Use action message: " ); ?></label>
              		<label><input name="cn_use_action_message" type="radio" value="mobile" <?php if ($cn_use_action_message == 'mobile') { echo 'checked="true"'; } ?> />Only for mobile devices</label> 
              		<label><input name="cn_use_action_message" type="radio" value="true" <?php if ($cn_use_action_message === true) { echo 'checked="true"'; } ?> />Always</label>
              		<label><input name="cn_use_action_message" type="radio" value="false" <?php if ($cn_use_action_message === false) { echo 'checked="true"'; } ?> />Never</label>
              </td>
            </tr>

            <tr>
                <th>
                    <label><?php _e("prettyPhoto selector: " ); ?></label>
                </th>
              <td>
                    <input type="text" name="cn_pp_rel" value="<?php echo $cn_prettyPhoto['rel']; ?>" size="40"><br />
                    <em>The selector to use for calling prettyPhoto.  This is often 'lightbox' or 'prettyPhoto'.  You can find it by viewing the source of a WordPress page with a prettyPhoto popup and looking for the a tag.  It's the value of the rel attribute.</em><br />
              </td>
            </tr>
            <tr>
                <th>
                    <label><?php _e("prettyPhoto theme: " ); ?></label>
                </th>
              <td>
              		<label><input name="cn_pp_theme" type="radio" value="pp_default" <?php if ($cn_prettyPhoto['theme'] == 'pp_default') { echo 'checked="true"'; } ?> />prettyPhoto default</label> 
              		<label><input name="cn_pp_theme" type="radio" value="light_rounded" <?php if ($cn_prettyPhoto['theme'] == 'light_rounded') { echo 'checked="true"'; } ?> />light rounded</label> 
              		<label><input name="cn_pp_theme" type="radio" value="dark_rounded" <?php if ($cn_prettyPhoto['theme'] == 'dark_rounded') { echo 'checked="true"'; } ?> />dark rounded</label> 
              		<label><input name="cn_pp_theme" type="radio" value="light_square" <?php if ($cn_prettyPhoto['theme'] == 'light_square') { echo 'checked="true"'; } ?> />light square</label> 
              		<label><input name="cn_pp_theme" type="radio" value="dark_square" <?php if ($cn_prettyPhoto['theme'] == 'dark_square') { echo 'checked="true"'; } ?> />dark square</label> 
              		<label><input name="cn_pp_theme" type="radio" value="facebook" <?php if ($cn_prettyPhoto['theme'] == 'facebook') { echo 'checked="true"'; } ?> />facebook</label> <br />
              </td>
            </tr>
            <tr>
                <th>
                    <label><?php _e("prettyPhoto custom options: " ); ?></label>
                </th>
              <td>
                    <input type="text" name="cn_pp_custom" value="<?php echo $cn_prettyPhoto['custom']; ?>" size="40"><br />
                    <em>For advanced users.  The custom options to use for calling prettyPhoto.  This should be a list like:</em><br /> 
                    <code>option: value, option2: value2</code><br />
                    <em>Below is a list of available options for prettyPhoto (not all combinations have been tested with cropnote)</em><br />
                    <pre>
animation_speed: 'fast', /* fast/slow/normal */
ajaxcallback: function() {},
slideshow: 5000, /* false OR interval time in ms */
autoplay_slideshow: false, /* true/false */
opacity: 0.80, /* Value between 0 and 1 */
show_title: true, /* true/false */
allow_resize: true, /* Resize the photos bigger than viewport. true/false */
allow_expand: true, /* Allow the user to expand a resized image. true/false */
default_width: 500,
default_height: 344,
counter_separator_label: '/', /* The separator for the gallery counter 1 "of" 2 */
theme: 'pp_default', /* light_rounded / dark_rounded / light_square / dark_square / facebook */
horizontal_padding: 20, /* The padding on each side of the picture */
hideflash: false, /* Hides all the flash object on a page, set to TRUE if flash appears over prettyPhoto */
wmode: 'opaque', /* Set the flash wmode attribute */
autoplay: true, /* Automatically start videos: True/False */
modal: false, /* If set to true, only the close button will close the window */
deeplinking: true, /* Allow prettyPhoto to update the url to enable deeplinking. */
overlay_gallery: true, /* If set to true, a gallery will overlay the fullscreen image on mouse over */
overlay_gallery_max: 30, /* Maximum number of pictures in the overlay gallery */
keyboard_shortcuts: true, /* Set to false if you open forms inside prettyPhoto */
changepicturecallback: function(){}, /* Called everytime an item is shown/changed */
callback: function(){}, /* Called when prettyPhoto is closed */
ie6_fallback: true,
                    </pre>
              </td>
            </tr>

        </table><br /><br />
            
        <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'cropnote_translations' ) ?>" />
        </p><br />
        </form>
    </div>
    
<?php } else if($tab == 'imagenotes') { 
	//image notes		
?>
    <div class="wrap">  
        <?php 
		//image notes selected remove
        if (isset($_POST['remove_selected_notes'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'imagenotesaction')) die('Remove selected security violated');
			
			if($_POST['s'] != '') {
				$query = "delete from ".$table_name." where note_ID in (" . implode(',', $_POST['s']) . ")";
				$wpdb->query($query); ?>
				<div class="updated"><p><strong><?php _e('Selected Notes Removed.' ); ?></strong></p></div>
            <?php }
        }?>
        
        <?php 
		//image notes selected unapprove
        if (isset($_POST['unapprove_selected_notes'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'imagenotesaction')) die('Unapprove selected security violated');
			
			if($_POST['s'] != '') {
				$query = "UPDATE `".$table_name."` SET
						`note_approved` = '0'
						where note_ID = '".$_POST['note_id']."'		
					";
				$wpdb->query($query); ?>
				<div class="updated"><p><strong><?php _e('Selected Notes Unapproved.' ); ?></strong></p></div>
            <?php }
        }?>
        
        <?php 
		//image notes selected approve
        if (isset($_POST['approve_selected_notes'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'imagenotesaction')) die('Approve selected security violated');
			
			if($_POST['s'] != '') {
				$query = "UPDATE `".$table_name."` SET
						`note_approved` = '1'
						where note_ID = '".$_POST['note_id']."'		
					";
				$wpdb->query($query); ?>
				<div class="updated"><p><strong><?php _e('Selected Notes Approved.' ); ?></strong></p></div>
            <?php }
        }?>
        
        <?php //image notes single remove
        if (isset($_POST['remove_single_note'])) {            
            if (!wp_verify_nonce($_POST['_wpnonce'], 'imagenotesaction')) die('Remove single security violated');
			if($_POST['remove_single_note'] == "yes") {
                $query = "delete from ".$table_name." where note_ID in (" .$_POST['note_id']. ")";
                $wpdb->query($query) ; ?>
                <div class="updated"><p><strong><?php _e('Note Removed.' ); ?></strong></p></div>
            <?php
            }
        }?>
        
         <?php //edit image note
        if (isset($_POST['edit_single_note'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'imagenotesaction')) die('Edit security violated');
            if($_POST['edit_single_note'] == "yes") {
				$query = "SELECT * from ".$table_name." where note_ID in (" .$_POST['note_id']. ")";
                $result = $wpdb->get_results($query);
                echo "<h4>" . __( 'Edit Image Note', 'cropnote_translations' ) . "</h4>";
                ?>
                <form name="dia_update_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                <input type="hidden" name="update_single_note" value="yes">
                <?php wp_nonce_field('imagenotesactionupdate') ?>
                
                <?php
                foreach ($result as $r) {
					echo '<table class="widefat" width="500px">';
					echo '<thead><tr>';
					echo '<th width="150">'.$r->note_img_ID.'<input type="hidden" name="note_text_old" value="'.$r->note_text.'"><input type="hidden" name="note_ID" value="'.$r->note_ID.'" /><input type="hidden" name="note_comment_ID" value="'.$r->note_comment_ID.'" /></th>';
					echo '<th></th>';
					echo '</tr></thead>';
					echo '<tbody>';
                    echo '<tr>';
                    echo '<td>Author</td>';
                    echo '<td><input name="note_author" type="text" size="40" value="'.$r->note_author.'" /></td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>Email</td>';
                    echo '<td><input name="note_email" type="text" size="40" value="'.$r->note_email.'" /></td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>Top</td>';
                    echo '<td><input name="note_top" type="text" size="5" value="'.$r->note_top.'" /></td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>Left</td>';
                    echo '<td><input name="note_left" type="text" size="5" value="'.$r->note_left.'" /></td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>Width</td>';
                    echo '<td><input name="note_width" type="text" size="5" value="'.$r->note_width.'" /></td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>Height</td>';
                    echo '<td><input name="note_height" type="text" size="5" value="'.$r->note_height.'" /></td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>Image Width</td>';
                    echo '<td><input name="image_width" type="text" size="5" value="'.$r->image_width.'" /></td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>Image Height</td>';
                    echo '<td><input name="image_height" type="text" size="5" value="'.$r->image_height.'" /></td>';
                    echo '</tr>';
					echo '<tr>';
                    echo '<td>Text</td>';
                    echo '<td><textarea name="note_text" cols="32" rows="5">'.$r->note_text.'</textarea></td>';
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td></td>';
                    echo '<td><input type="submit" name="update" value="update" /><input type="button" name="cancel" value="cancel" onClick="javascript: cancelUpdate();" /></td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo "</table>";
                ?></form><?php
            ?>
            <?php
            }
        } ?>
        
        <?php //update image note
        if (isset($_POST['update_single_note'])) {
			if (!wp_verify_nonce($_POST['_wpnonce'], 'imagenotesactionupdate')) die('Update security violated');	
            if($_POST['update_single_note'] == "yes") {
                $imgid = $_POST['note_ID'];
                $commentid = $_POST['note_comment_ID'];
				$note_text_old = $_POST['note_text_old'];
                $query = "UPDATE `".$table_name."` SET
                                    `note_author` = '".$_POST['note_author']."',
                                    `note_email` = '".$_POST['note_email']."',
                                    `note_top` = '".$_POST['note_top']."',
                                    `note_left` = '".$_POST['note_left']."',
                                    `note_width` = '".$_POST['note_width']."',
                                    `note_height` = '".$_POST['note_height']."',
                                    `image_width` = '".$_POST['image_width']."',
                                    `image_height` = '".$_POST['image_height']."',
                                    `note_text` = '".$_POST['note_text']."'	
                                    where note_ID = '".$imgid."'		
                                ";
                $wpdb->query($query);
            ?><div class="updated"><p><strong><?php _e('Image note saved.' ); ?></strong></p></div>
            <?php }
        } ?>
        
        <?php //update comment status
        if (isset($_POST['update_comment_status'])) {
			if (!wp_verify_nonce($_POST['_wpnonce'], 'imagenotesaction')) die('Update comment security violated');	
            if($_POST['update_comment_status'] == "yes") {
				if($_POST['note_comment_status'] == "1") {
					$query = "UPDATE `".$table_name."` SET
								`note_approved` = '1'
								where note_ID = '".$_POST['note_id']."'		
							";
							$wpdb->query($query);
					?><div class="updated"><p><strong><?php _e('Note Comment approved.' ); ?></strong></p></div>
					<?php 
				} else {
					$query = "UPDATE `".$table_name."` SET
								`note_approved` = '0'
								where note_ID = '".$_POST['note_id']."'		
							";
							$wpdb->query($query);
					?><div class="updated"><p><strong><?php _e('Note Comment unapproved.' ); ?></strong></p></div>
					<?php 
				}
       		 }
        } ?>
        
        <?php echo "<h4>" . __( 'Image Notes', 'cropnote_translations' ) . "</h4>"; ?>
        <script language="javascript">
		function deleteRecord(recID) {
			var docForm = document.imagenotes;
			document.getElementById("note_id").value = recID;
			document.getElementById("remove_single_note").value = "yes";		
			docForm.submit();
		}
		
		function editRecord(recID) {
			var docForm = document.imagenotes;
			document.getElementById("note_id").value = recID;
			document.getElementById("edit_single_note").value = "yes";		
			docForm.submit();
		}
		
		function cancelUpdate() {
			window.location.href=window.location.href;
		}
		
		function updateComment(recID, commentID, status) {
			var docForm = document.imagenotes;
			document.getElementById("note_id").value = recID;
			document.getElementById("note_comment_id").value = commentID;
			document.getElementById("note_comment_status").value = status;
			document.getElementById("update_comment_status").value = "yes";		
			docForm.submit();
		}
		</script>
		
        <form name="imagenotes" action="" method="post">
            <input type="hidden" name="remove_single_note" id="remove_single_note" value="" />
            <input type="hidden" name="edit_single_note" id="edit_single_note" value="" />
            <input type="hidden" name="update_comment_status" id="update_comment_status" value="" />
            <input type="hidden" name="note_comment_status" id="note_comment_status" value="" />
            <input type="hidden" name="note_id" id="note_id" value="" />
            <input type="hidden" name="note_comment_id" id="note_comment_id" value="" />
            
            <?php wp_nonce_field('imagenotesaction') ?>
            
            <?php
            require_once("pagination.class.php");
            $items = mysql_num_rows(mysql_query("SELECT * FROM ".$table_name.";")); // number of total rows in the database
			$items > 0 ? print '<input type="submit" name="approve_selected_notes" value="Approve Selected"/>' : '';
			$items > 0 ? print '<input type="submit" name="unapprove_selected_notes" value="Unapprove Selected"/>' : '';
			$items > 0 ? print '<input type="submit" name="remove_selected_notes" value="Remove Selected"/>' : '';
			
            if($items > 0) {
                    $p = new pagination;
                    $p->items($items);
                    $p->limit(10); // Limit entries per page
                    $p->target("options-general.php?page=cropnote&tab=imagenotes");
                    $p->currentPage($_GET[$p->paging]); // Gets and validates the current page
                    $p->calculate(); // Calculates what to show
                    $p->parameterName('paging');
                    $p->adjacents(1); //No. of page away from the current page
             
                    if(!isset($_GET['paging'])) {
                        $p->page = 1;
                    } else {
                        $p->page = $_GET['paging'];
                    }
             
                    //Query for limit paging
                    $limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;             
            } else {
                //echo "No Record Found";
            } ?>
            
			<?php
                // sending query
                $sql = "SELECT * FROM ".$table_name." ORDER BY note_ID DESC ".$limit;
                $result = $wpdb->get_results($sql);
                
                echo '<table class="widefat">';
                echo '<thead><tr>';
                echo '<th>ID</th>';
                echo '<th>IMG ID</th>';
                echo '<th>Comment ID</th>';
                echo '<th>Author</th>';
                echo '<th>Email</th>';
                echo '<th>Top</th>';
                echo '<th>Left</th>';
                echo '<th>Width</th>';
                echo '<th>Height</th>';
                echo '<th>Image Width</th>';
                echo '<th>Image Height</th>';
                echo '<th width="200">Text</th>';
                echo '<th>Date</th>';
                echo '<th>Action</th>';
                echo '</tr></thead>';
                
                echo '<tbody>';
                foreach ($result as $r) {
                    echo '<tr>';
                        echo '<td><input type="checkbox" name="s[]" value="'.$r->note_ID.'" /></td>';
                        echo '<td>'.$r->note_img_ID.'</td>';
                        echo '<td>'.$r->note_comment_ID.'</td>';
                        echo '<td>'.$r->note_author.'</td>';
                        echo '<td>'.$r->note_email.'</td>';
                        echo '<td>'.$r->note_top.'</td>';
                        echo '<td>'.$r->note_left.'</td>';
                        echo '<td>'.$r->note_width.'</td>';
                        echo '<td>'.$r->note_height.'</td>';
                        echo '<td>'.$r->image_width.'</td>';
                        echo '<td>'.$r->image_height.'</td>';
                        echo '<td>'.$r->note_text.'</td>';
                        echo '<td>'.$r->note_date.'</td>';
                        echo '<td>';
						if($r->note_approved == 1) {
							echo '<input type="button" name="unapprove" value="unapprove" onClick="javascript: updateComment('.$r->note_ID.','. $r->note_comment_ID . ',0);" />';
						} else {
							echo '<input type="button" name="approve" value="approve" onClick="javascript: updateComment('.$r->note_ID.','. $r->note_comment_ID . ',1);" />';
						}
						echo '<input type="button" name="edit" value="edit" onClick="javascript: editRecord(' . $r->note_ID . ');" /><input type="button" name="remove" value="Remove" onClick="javascript: deleteRecord(' . $r->note_ID . ');" /></td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo "</table>";
				$items == 0 ? print "No notes found yet.  Go make some!" : '';
				$items > 0 ? print '<input type="submit" name="approve_selected_notes" value="Approve Selected"/>' : '';
				$items > 0 ? print '<input type="submit" name="unapprove_selected_notes" value="Unapprove Selected"/>' : '';
                $items > 0 ? print '<input type="submit" name="remove_selected_notes" value="Remove Selected"/>' : '';
                ?>
            <?php $items > 0 ? print $p->show() : '';  // Echo out the list of paging. ?>
        </form>
    </div>
<?php } else { //permission settings ?>
    <div class="wrap">  
        <?php 
		global $wp_roles;
		$roles = $wp_roles->get_names();
        if ($_POST['cn_hidden'] == 'Y') {
        	if (!wp_verify_nonce($_POST['_wpnonce'], 'cropnote_permissions')) die('Security violation detected when trying to update cropnote permissions.');
        	$i = 0;
			foreach($roles as $role => $name) {
				$role = $wp_roles->get_role($role);
				$role->remove_cap('cropnote_read'); if(isset($_POST['read_'.$i])) {$role->add_cap('cropnote_read');}
				$role->remove_cap('cropnote_add'); if(isset($_POST['add_'.$i])) {$role->add_cap('cropnote_add');}
				$role->remove_cap('cropnote_add_html'); if(isset($_POST['add_html_'.$i])) {$role->add_cap('cropnote_add_html');}
				$role->remove_cap('cropnote_add_autoapprove'); if(isset($_POST['add_autoapprove_'.$i])) {$role->add_cap('cropnote_add_autoapprove');}
				$role->remove_cap('cropnote_edit_all'); if(isset($_POST['edit_all_'.$i])) {$role->add_cap('cropnote_edit_all');}
				$role->remove_cap('cropnote_edit_own'); if(isset($_POST['edit_own_'.$i])) {$role->add_cap('cropnote_edit_own');}
				$i++;
	        } 
			$options = get_option('cropnote_settings');
			$role = $options['anonymous_permissions'];
			$role['cropnote_read'] = false; if(isset($_POST['read_'.$i])) {$role['cropnote_read'] = true;}
			$role['cropnote_add'] = false; if(isset($_POST['add_'.$i])) {$role['cropnote_add'] = true;}
			$role['cropnote_add_html'] = false; if(isset($_POST['add_html_'.$i])) {$role['cropnote_add_html'] = true;}
			$role['cropnote_add_autoapprove'] = false; if(isset($_POST['add_autoapprove_'.$i])) {$role['cropnote_add_autoapprove'] = true;}
			$role['cropnote_edit_all'] = false; if(isset($_POST['edit_all_'.$i])) {$role['cropnote_edit_all'] = true;}
			$role['cropnote_edit_own'] = false; if(isset($_POST['edit_own_'.$i])) {$role['cropnote_edit_own'] = true;}
			$options['anonymous_permissions'] = $role;
			update_option('cropnote_settings', $options);
	        ?>
			<div class="updated"><p><strong><?php _e('Permissions saved.' ); ?></strong></p></div>
		<?php } ?>
		<form name="cropnote_permissions" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	        <input type="hidden" name="cn_hidden" value="Y">        
			<?php wp_nonce_field('cropnote_permissions') ?>
	        <?php    echo "<h4>" . __( 'Cropnote Permissions', 'cropnote_translations' ) . "</h4>"; ?>
	        <table class="widefat" width="100%">
				<thead>
					<tr>
						<td>Role</td>
						<td>Read Notes</td>
						<td>Add Notes</td>
						<td>Add Notes with HTML</td>
						<td>Added Notes Autoapprove</td>
						<td>Edit/Delete Own Notes</td>
						<td>Edit/Delete Other's Notes</td>
					</tr>
				</thead>
				<?php $i = 0; foreach($roles as $role => $name) {
					$role = $wp_roles->get_role($role); ?>
					<tr>
						<td><?php echo $name; ?></td>
						<td><input type="checkbox" name="read_<?php echo $i; ?>"<?php if ($role->has_cap('cropnote_read')) echo 'checked="checked"'; ?> /></td>
						<td><input type="checkbox" name="add_<?php echo $i; ?>"<?php if ($role->has_cap('cropnote_add')) echo 'checked="checked"'; ?> /></td>
						<td><input type="checkbox" name="add_html_<?php echo $i; ?>"<?php if ($role->has_cap('cropnote_add_html')) echo 'checked="checked"'; ?> /></td>
						<td><input type="checkbox" name="add_autoapprove_<?php echo $i; ?>"<?php if ($role->has_cap('cropnote_add_autoapprove')) echo 'checked="checked"'; ?> /></td>
						<td><input type="checkbox" name="edit_own_<?php echo $i; ?>"<?php if ($role->has_cap('cropnote_edit_own')) echo 'checked="checked"'; ?> /></td>
						<td><input type="checkbox" name="edit_all_<?php echo $i; ?>"<?php if ($role->has_cap('cropnote_edit_all')) echo 'checked="checked"'; ?> /></td>
					</tr>
				<?php $i++; } ?>
					<tr>
						<td><?php echo 'Anonymous User'; $role = get_option('cropnote_settings'); $role = $role['anonymous_permissions']; ?></td>
						<td><input type="checkbox" name="read_<?php echo $i; ?>"<?php if ($role['cropnote_read']) echo 'checked="checked"'; ?> /></td>
						<td><input type="checkbox" name="add_<?php echo $i; ?>"<?php if ($role['cropnote_add']) echo 'checked="checked"'; ?> /></td>
						<td><input type="checkbox" name="add_html_<?php echo $i; ?>"<?php if ($role['cropnote_add_html']) echo 'checked="checked"'; ?> /></td>
						<td><input type="checkbox" name="add_autoapprove_<?php echo $i; ?>"<?php if ($role['cropnote_add_autoapprove']) echo 'checked="checked"'; ?> /></td>
						<td><input type="checkbox" name="edit_own_<?php echo $i; ?>"<?php if ($role['cropnote_edit_own']) echo 'checked="checked"'; ?> /></td>
						<td><input type="checkbox" name="edit_all_<?php echo $i; ?>"<?php if ($role['cropnote_edit_all']) echo 'checked="checked"'; ?> /></td>
					</tr>
			</table><br />
	        <p class="submit">
	        <input type="submit" name="Submit" value="<?php _e('Update Permissions', 'cropnote_translations' ) ?>" />
	        </p>
		</form>
<?php }?></div>