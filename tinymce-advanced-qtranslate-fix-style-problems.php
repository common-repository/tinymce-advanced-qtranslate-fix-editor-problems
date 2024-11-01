<?php
/*
Plugin Name: TinyMCE Advanced qTranslate fix editor problems
Plugin URI: http://www.blackbam.at/blackbams-blog/2012/01/28/wordpress-3-3-tinymce-advanced-qtranslate-fix-style-problems/
Description: Remove line breaks and whitespace, enable custom stylesheets and multiple textareas. Fully customizable.
Version: 1.0.0
Author: David Stöckl
Author URI: http://www.blackbam.at/
 * 
Released and distributed under the GPLv2.
 * 
*/

/********** Administration ******************/
register_activation_hook(__FILE__,"tqfep_activate");

function tqfep_activate() {
	add_option('tqfep_styles',"");
	add_option('tqfep_fix_pre',1);
	add_option('tqfep_fix_post',1);
	add_option('tqfep_encoding','UTF-8');
	add_option('tqfep_custom_styles',';My Class=myclass;');
	register_uninstall_hook(__FILE__,"tqfep_uninstall");
}

function tqfep_uninstall() {
	// delete all options, tables, ...
	delete_option('tqfep_styles');
	delete_option('tqfep_fix_pre');
	delete_option('tqfep_fix_post');
	delete_option('tqfep_encoding');
	delete_option('tqfep_custom_styles');
}

function tqfepUpdateCheckbox($option) {
	update_option($option,intval($_POST[$option]));
}

function tqfepUpdateSelect($option,$allowed) {
	if(in_array($_POST[$option],$allowed)) {
		update_option($option,$_POST[$option]);
	}
}


// add the options page on initialization
add_action('admin_menu','tqfep_admin');

// add the actual options page
function tqfep_admin() {
	add_options_page('Fix TQ Editor','Fix TQ Editor','manage_options',__FILE__,'tqfep_backend_page');
}

// add the options page
function tqfep_backend_page() { ?>
	<div class="wrap">
		<div><?php screen_icon('options-general'); ?></div>
		<h2>Settings: TinyMCE Advanced qTranslate fix editor problems</h2>
		<?php
		if(isset($_POST['tqfep_update']) && $_POST['tqfep_update']!="") {
			
			tqfepUpdateCheckbox('tqfep_fix_pre');
			tqfepUpdateCheckbox('tqfep_fix_post');
			
			tqfepUpdateSelect('tqfep_styles',array('','usual','theme_stylesheet','force_custom'));
			
			update_option('tqfep_encoding',$_POST['tqfep_encoding']);
			update_option('tqfep_custom_styles',$_POST['tqfep_custom_styles']);
			?>
			<div id="message" class="updated">Settings saved successfully</div>
		<?php }
		?>
		<form name="tqfep_update" method="post" action="">
			<div>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e('Sanitize content before editing?','ultrasimpleshop'); ?></th>
						<td>
							<input type="checkbox" name="tqfep_fix_pre" value="1" <?php if(get_option('tqfep_fix_pre')==1) {?>checked="checked" <?php } ?> />
						</td>
						<td class="description"><?php _e('Remove blank lines before editing content.','ultrasimpleshop'); ?></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Sanitize content after editing?','ultrasimpleshop'); ?></th>
						<td>
							<input type="checkbox" name="tqfep_fix_post" value="1" <?php if(get_option('tqfep_fix_post')==1) {?>checked="checked" <?php } ?> />
						</td>
						<td class="description"><?php _e('Remove blank lines after editing content.','ultrasimpleshop'); ?></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Encoding','ultrasimpleshop'); ?></th>
						<td>
							<input type="text" size="8" name="tqfep_encoding" value="<?php echo get_option('tqfep_encoding'); ?>" />
						</td>
						<td class="description"><?php _e('Usually "UTF-8"','ultrasimpleshop'); ?></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Fix TinyMCE Advanced custom stylesheets','ultrasimpleshop'); ?></th>
						<td>
							<select name="tqfep_styles">
								<option value="" <?php if(get_option('tqfep_styles')=='') {?>selected="selected"<?php } ?>><?php _e('Nothing to do','ultrasimpleshop'); ?></option>
								<option value="theme_stylesheet" <?php if(get_option('tqfep_styles')=='theme_stylesheet') {?>selected="selected"<?php } ?>><?php _e('TRY to embed theme stylesheet','ultrasimpleshop'); ?></option>
								<option value="force_custom" <?php if(get_option('tqfep_styles')=='force_custom') {?>selected="selected"<?php } ?>><?php _e('Add custom editor stylesheets dirctly','ultrasimpleshop'); ?></option>
							</select>
						</td>
						<td class="description"><?php _e('If you cannot add custom styles using "editor-style.css" via TinyMCE Advanced, you can try one of these options to add custom stylesheets.','ultrasimpleshop'); ?></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Custom Stylesheets String','ultrasimpleshop'); ?></th>
						<td>
							<input type="text" size="40" name="tqfep_custom_styles" value="<?php echo get_option('tqfep_custom_styles'); ?>" />
						</td>
						<td class="description"><?php _e('Use: ";Name of Field=CSS-class;. Works only if custom editor stylesheets are selected."','ultrasimpleshop'); ?></td>
					</tr>
				</table>
				<p></p>
				<p><input type="hidden" name="tqfep_update" value="true" />
				<input type="submit" name="Save" value="Save Settings" class="button-primary" /></p>
			</div>
		</form>
	</div>
<?php }


 
// second possibilty - styles inside editor
if(get_option('tqfep_styles')=="theme_stylesheet") {
	add_filter( 'mce_css', 'tinymce_css',5);
}
 
function ses_tinymce_css($wp) {
        $wp .= ',' . get_bloginfo('stylesheet_url');
        return $wp;
}
 
/**
 * The hard way - if other possibilities do not work
 */

if(get_option('tqfep_styles')=="force_custom") {
	add_filter('tiny_mce_before_init', 'cisStyles', 5);
}
	
function cisStyles($initialArray) {
	$modifiedArray = $initialArray;
 
	$modifiedArray['theme_advanced_styles'] .= get_option('tqfep_custom_styles');
	//strip first and last character if it matches ";"
	$modifiedArray['theme_advanced_styles'] = trim($modifiedArray['theme_advanced_styles'], ';');
	return $modifiedArray;
}
 
/** TinyMCE Advanced and qTranslate - fix editing bugs */

if(get_option('tqfep_fix_pre')==1) {
	add_filter('htmledit_pre', 'fix_p_around_languagetag');
	add_filter('richedit_pre', 'fix_p_around_languagetag');
}

if(get_option('tqfep_fix_post')==1) {
	add_filter('content_save_pre','fix_p_after_edit');
}

function fix_p_around_languagetag ($content = '') {
	$content = html_entity_decode($content);
	$content = str_replace("<p><!--", "<!--", $content);
	$content = str_replace("--></p>", "-->", $content);
	$content = preg_replace("/(-->)(\s|&nbsp;)*<p>(\s|&nbsp;)*(<\/p>)(\s|&nbsp;)*/","-->",$content); 
	$content = htmlentities($content,ENT_COMPAT | ENT_HTML401,get_option('tqfep_encoding'));
	return $content;
}

function fix_p_after_edit ($content) {
	$content = str_replace("<p><!--", "<!--", $content);
	$content = str_replace("--></p>", "-->", $content);
	$content = preg_replace("/(-->)(\s|&nbsp;)*<p>(\s|&nbsp;)*(<\/p>)(\s|&nbsp;)*/","-->",$content); 
	return $content;
} 


?>