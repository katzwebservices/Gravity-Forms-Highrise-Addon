<?php
/*
Plugin Name: Gravity Forms Highrise Add-On
Description: Integrates Gravity Forms with Highrise allowing form submissions to be automatically sent to your Highrise account
Version: 2.6.1
Author: Katz Web Services, Inc.
Author URI: http://www.katzwebservices.com

------------------------------------------------------------------------
Copyright 2013 Katz Web Services, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

add_action('init',  array('GFHighrise', 'init'));

class GFHighrise {

	private static $name = "Gravity Forms Highrise Add-On";
	private static $path = "gravity-forms-highrise/highrise.php";
	private static $url = "http://www.gravityforms.com";
	private static $slug = "gravity-forms-highrise";
	private static $version = "2.6.1";
	private static $min_gravityforms_version = "1.3.9";

    //Plugin starting point. Will load appropriate files
    public static function init(){
	    global $pagenow;

	    if($pagenow === 'plugins.php') {
			add_action("admin_notices", array('GFHighrise', 'is_gravity_forms_installed'), 10);
		}

		if(self::is_gravity_forms_installed(false, false) !== 1){
			add_action('after_plugin_row_' . self::$path, array('GFHighrise', 'plugin_row') );
           return;
        }

        if(is_admin()){

            //creates a new Settings page on Gravity Forms' settings screen
            if(self::has_access("gravityforms_highrise")){
            	RGForms::add_settings_page("Highrise", array("GFHighrise", "settings_page"), "");
            }
        }

        //creates the subnav left menu
        add_filter("gform_addon_navigation", array('GFHighrise', 'create_menu'), 20);

        if(self::is_highrise_page()){

            //enqueueing sack for AJAX requests
            wp_enqueue_script(array("sack", "jquery-ui-tooltip"));
            wp_enqueue_style('gravityforms-admin', GFCommon::get_base_url().'/css/admin.css');
         }
         else if(in_array(RG_CURRENT_PAGE, array("admin-ajax.php"))){

            add_action('wp_ajax_rg_update_feed_active', array('GFHighrise', 'update_feed_active'));
            add_action('wp_ajax_gf_select_highrise_form', array('GFHighrise', 'select_highrise_form'));

        } elseif(in_array(RG_CURRENT_PAGE, array('admin.php'))) {
        	add_action('admin_head', array('GFHighrise', 'show_highrise_status'));
        }
        else{
             //handling post submission.
            add_action("gform_entry_created", array('GFHighrise', 'push'), 10, 2);
        }

        add_action("gform_properties_settings", array('GFHighrise', 'add_form_option_js'), 800);


		add_filter('gform_tooltips', array('GFHighrise', 'add_form_option_tooltip'));

		add_filter("gform_confirmation", array('GFHighrise', 'confirmation_error'));

		add_action('gform_entry_info', array('GFHighrise', 'entry_info_link_to_highrise'), 10, 2);
    }

    public static function is_gravity_forms_installed($asd = '', $echo = true) {
		global $pagenow, $page; $message = '';

		$installed = 0;
		$name = self::$name;
		if(!class_exists('RGForms')) {
			if(file_exists(WP_PLUGIN_DIR.'/gravityforms/gravityforms.php')) {
				$installed = 2;
				$message .= __(sprintf('%sGravity Forms is installed but not active. %sActivate Gravity Forms%s to use the %s plugin.%s', '<p>', '<strong><a href="'.wp_nonce_url(admin_url('plugins.php?action=activate&plugin=gravityforms/gravityforms.php'), 'activate-plugin_gravityforms/gravityforms.php').'">', '</a></strong>', $name,'</p>'), 'gravity-forms-highrise');
			} else {
				$installed = 0;
				$message .= <<<EOD
<p><a href="http://katz.si/gravityforms?con=banner" title="Gravity Forms Contact Form Plugin for WordPress"><img src="http://gravityforms.s3.amazonaws.com/banners/728x90.gif" alt="Gravity Forms Plugin for WordPress" width="728" height="90" style="border:none;" /></a></p>
		<h3><a href="http://katz.si/gravityforms" target="_blank">Gravity Forms</a> is required for the $name</h3>
		<p>You do not have the Gravity Forms plugin installed. <a href="http://katz.si/gravityforms">Get Gravity Forms</a> today.</p>
EOD;
			}

			if(!empty($message) && $echo) {
				echo '<div id="message" class="updated">'.$message.'</div>';
			}
		} else {
			$installed = 1;
		}
		return $installed;
	}

	public static function plugin_row(){
        if(!self::is_gravityforms_supported()){
            $message = sprintf(__("%sGravity Forms%s is required. %sPurchase it today!%s"), "<a href='http://katz.si/gravityforms'>", "</a>", "<a href='http://katz.si/gravityforms'>", "</a>");
            self::display_plugin_message($message, true);
        }
    }

    public static function display_plugin_message($message, $is_error = false){
    	$style = '';
        if($is_error)
            $style = 'style="background-color: #ffebe8;"';

        echo '</tr><tr class="plugin-update-tr"><td colspan="5" class="plugin-update"><div class="update-message" ' . $style . '>' . $message . '</div></td>';
    }

	public static function show_highrise_status() {
		global $pagenow;

		if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'gf_edit_forms' && !isset($_REQUEST['id'])) {
			$activeforms = array();
        	$forms = RGFormsModel::get_forms();
        	if(!is_array($forms)) { return; }
        	foreach($forms as $form) {
        		$form = RGFormsModel::get_form_meta($form->id);
        		if(is_array($form) && !empty($form['enableHighrise'])) {
        			$activeforms[] = $form['id'];
        		}
        	}

        	if(!empty($activeforms)) {
?>
<style>
	td a.row-title span.highrise_enabled {
		position: absolute;
		background: url('<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>/highrise-icon.gif') right top no-repeat;
		height: 16px;
		width: 16px;
		margin-left: 10px;
	}
</style>
<script>
	jQuery(document).ready(function($) {
		$activeforms = $.parseJSON('<?php echo json_encode($activeforms); ?>');

		$('table tbody.user-list tr').each(function() {
			// Get the ID of the row
			id = parseInt($('td.column-id', $(this)).text());

			// If the row is in the $activeforms array, add the icon
			if($activeforms.indexOf(id) >= 0) {
				$('td a.row-title', $(this)).append('<span class="highrise_enabled" title="Highrise is Enabled for this Form"></span>');
			}
		});
	});
</script>
<?php
			}
		}
	}

	public static function confirmation_error($confirmation, $form = '', $lead = '', $ajax ='' ) {

		if(current_user_can('administrator') && !empty($_REQUEST['highriseErrorMessage'])) {
			$confirmation .= sprintf(__('%sThe entry was not added to Highrise because: %s%s%s. %sYou are only being shown this because you are an administrator. Other users will not see this message.%s%s', 'gravity-forms-highrise'), '<div class="error" style="text-align:center; color:#790000; font-size:14px; line-height:1.5em; margin-bottom:16px;background-color:#FFDFDF; margin-bottom:6px!important; padding:6px 6px 4px 6px!important; border:1px dotted #C89797">', '<strong>', esc_html(trim(rtrim($_REQUEST['highriseErrorMessage']))), '</strong>', '<br /><em>', '</em>', '</div>');
		}
		return $confirmation;
	}

	public static function add_form_option_tooltip($tooltips) {
		$tooltips["form_highrise"] = "<h6>" . __("Enable Highrise Integration", "gravity-forms-highrise") . "</h6>" . __("Check this box to integrate this form with Highrise. When an user submits the form, the data will be added to Highrise.", "gravity-forms-highrise");
		return $tooltips;
	}


	public static function add_form_option_js() {

		ob_start();
			gform_tooltip("form_highrise");
			$tooltip = ob_get_contents();
		ob_end_clean();
		$tooltip = trim(rtrim($tooltip)).' ';
	?>
<style>
	#gform_title .highrise,
	#gform_enable_highrise_label {
		float:right;
		background: url('<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)); ?>/highrise-icon.gif') right top no-repeat;
		height: 16px;
		width: 16px;
		cursor: help;
	}
	#gform_enable_highrise_label {
		float: none;
		width: auto;
		background-position: left top;
		padding-left: 18px;
		cursor:default;
	}
</style>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		var highriseCheckbox = "<h4 class='gf_settings_subgroup_title'>Highrise</h4><input type='checkbox' id='gform_enable_highrise' /> <label for='gform_enable_highrise' id='gform_enable_highrise_label'><?php _e("Enable Highrise integration", "gravity-forms-highrise"); echo ' '.$tooltip; ?></label>";

		if($('#gform_enable_highrise').length === 0) {
			$('.gform_panel_form_settings #gform_custom_settings').append(highriseCheckbox);
		}

		if($().prop) {
			$("#gform_enable_highrise").prop("checked", form.enableHighrise ? true : false);
		} else {
			$("#gform_enable_highrise").attr("checked", form.enableHighrise ? true : false);
		}

		$("#gform_enable_highrise").live('click change ready', function() {

			var checked = $(this).is(":checked")

			form.enableHighrise = checked;

			if(checked) {
				$("#gform_title").append('<span class="highrise" title="<?php _e("Highrise integration is enabled.", "gravity-forms-highrise") ?>"></span>');
			} else {
				$("#gform_title .highrise").remove();
			}
		}).trigger('ready');

		$('.tooltip_form_highrise').tooltip({
			// Use the tooltip attribute of the element for the content
	        content: $('.tooltip_form_highrise').attr('title'),
	        show: { delay: 200 },
	        hide: { delay: 200, effect: 'fade' }
	    });
	});
</script><?php
	}

    //Returns true if the current page is an Feed pages. Returns false if not
    private static function is_highrise_page(){
    	if(empty($_GET["page"])) { return false; }
        $current_page = trim(strtolower($_GET["page"]));
        $highrise_pages = array("gf_highrise");

        return in_array($current_page, $highrise_pages);
    }

    //Creates Highrise left nav menu under Forms
    public static function create_menu($menus){

        // Adding submenu if user has access
		$permission = self::has_access("gravityforms_highrise");
		if(!empty($permission)) {
			$menus[] = array("name" => "gf_highrise", "label" => __("Highrise", "gravityformshighrise"), "callback" =>  array("GFHighrise", "highrise_page"), "permission" => $permission);
		}
	    return $menus;
    }

    public static function settings_page(){
		$message = $validimage = false;
        if(!empty($_POST["uninstall"])){
            check_admin_referer("uninstall", "gf_highrise_uninstall");
            self::uninstall();

            ?>
            <div class="updated fade" style="padding:20px;"><?php _e(sprintf("Gravity Forms Highrise Add-On have been successfully uninstalled. It can be re-activated from the %splugins page%s.", "<a href='plugins.php'>","</a>"), "gravityformshighrise")?></div>
            <?php
            return;
        }
        else if(!empty($_POST["gf_highrise_submit"])){
            check_admin_referer("update", "gf_highrise_update");
            $settings = array("url" => stripslashes($_POST["gf_highrise_url"]), "token" => stripslashes($_POST["gf_highrise_token"]));
            update_option("gf_highrise_settings", $settings);
        }
        else{
            $settings = get_option("gf_highrise_settings");
        }

        $api = self::get_api();

        if($api){
            $message = $api->testAccount($settings);
			if ( $message == 'Valid Highrise URL and API Token.' ) {
				$class = "updated";
				$validimage = '<img src="'.GFCommon::get_base_url().'/images/tick.png"/>';
				$valid = true;
			} else {
				$class = "error";
				$valid = false;
				$validimage = '<img src="'.GFCommon::get_base_url().'/images/cross.png"/>';
			}
		}
        else if(!empty($settings["url"]) || !empty($settings["token"])){
            $message = "<p>Invalid Highrise URL and/or API Token. Please try another combination.</p>";
            $class = "error";
            $valid = false;
            $validimage = '<img src="'.GFCommon::get_base_url().'/images/cross.png"/>';
        }

        ?>
        <style>
            .ul-square li { list-style: square!important; }
            .ol-decimal li { list-style: decimal!important; }
        </style>
		<div class="wrap">
			<h2><?php _e('Gravity Forms Highrise Add-on Settings'); ?></h2>
		<?php if($message) {
				echo "<div class='fade below-h2 {$class}'>".wpautop($message)."</div>";
			} ?>

        <form method="post" action="">
            <?php wp_nonce_field("update", "gf_highrise_update") ?>
            <h3><?php _e("Highrise Account Information", "gravityformshighrise") ?></h3>
            <p style="text-align: left;">
                <?php _e(sprintf("If you don't have a Highrise account, you can %ssign up for one here%s", "<a href='http://highrisehq.com/' target='_blank'>" , "</a>"), "gravityformshighrise") ?>
            </p>

			<table class="form-table">
                <tr>
                    <th scope="row"><label for="gf_highrise_url"><?php _e("Highrise URL", "gravityformshighrise"); ?></label> </th>
                    <td><input type="text" size="75" id="gf_highrise_url" name="gf_highrise_url" value="<?php echo esc_attr($settings["url"]) ?>"/> <?php echo $validimage; ?> <br/> Your Highrise URL, e.g. http://yourcompany.highrisehq.com</td>
                </tr>
                <tr>
                    <th scope="row"><label for="gf_highrise_token"><?php _e("API Token", "gravityformshighrise"); ?></label> </th>
                    <td><input type="text" size="75" id="gf_highrise_token" name="gf_highrise_token" value="<?php echo esc_attr($settings["token"]) ?>"/> <?php echo $validimage; ?> <br/> Your Highrise API token can be found in Highrise under Account &amp; settings > My info in the tab named "API token".</td>
                </tr>
                <tr>
                    <td colspan="2" ><input type="submit" name="gf_highrise_submit" class="button-primary" value="<?php _e("Save Settings", "gravityformshighrise") ?>" /></td>
                </tr>

            </table>
            <div>

            </div>
        </form>

	<?php if($valid) { ?>
		<div class="hr-divider"></div>

		<h3>Usage Instructions</h3>

		<div class="delete-alert alert_gray">
			<div class="wp-caption" style="float:right; width:235px; margin:20px;"><img src="<?php echo self::get_base_url(); ?>/settings.jpg" /><small>How the form appears on the Form Settings page</small></div>
			<h4>To integrate a form with Highrise:</h4>
			<ol class="ol-decimal">
				<li>Edit the form you would like to integrate (choose from the <a href="<?php _e(admin_url('admin.php?page=gf_edit_forms')); ?>">Edit Forms page</a>).</li>
				<li>Click "Form Settings"</li>
				<li><strong>Check the box "Enable Highrise integration"</strong></li>
				<li>Save the form</li>
			</ol>
			<p>Note: <strong>Form entries must have First &amp; Last Names</strong> for data to be saved to Highrise.</p>
		</div>


        <h4>Form Fields</h4>
        <p>Fields will be automatically mapped by Highrise using the default Gravity Forms labels. If you change the labels of your fields, make sure to use the following keywords in the label to match and send data to Highrise.</p>
        <p>Text in <span class="description">gray italics</span> is the Parameter name for the field. Use this text to set the "Parameter Name" for each field (in the field settings under Advanced >  Allow field to be populated dynamically > Parameter Name)</p>

        <ul class="ul-square">
        	<li><code>name</code> (use to auto-split names into First Name and Last Name fields) <span class="description">BothNames</span></li>
            <li><code>first name</code> <span class="description">sFirstName</span></li>
            <li><code>last name</code>  <span class="description">sLastName</span></li>
            <li><code>company</code> <span class="description">sCompany</span></li>
            <li><code>email</code> <span class="description">sEmail</span></li>
            <li><code>phone</code> <span class="description">sPhone</span></li>
            <li><code>mobile</code> <span class="description">sMobile</span></li>
            <li><code>fax</code> <span class="description">sFax</span></li>
            <li><code>address</code> <span class="description">sAddress</span></li>
            <li><code>city</code> <span class="description">sCity</span></li>
            <li><code>country</code> <span class="description">sCountry</span></li>
            <li><code>zip</code> <span class="description">sZip</span></li>
            <li><code>website</code> <span class="description">sZip</span></li>
            <li><code>twitter</code> <span class="description">sTwitter</span></li>
            <li><code>zip</code> <span class="description">sZip</span></li>
            <li><code>subject</code> <span class="description">sTitle</span></li>
            <li><code>tags</code> (comma-separated) <span class="description">sFirstName</span></li>
            <li><code>question</code>, <code>message</code>, or <code>comments</code> for Notes  <span class="description">sNotes</span></li>
            <li><code>background</code>, <code>staff_comment</code> <span class="description">sBackground</span></li>
            <li>Anything not recognized by the list will be added to Staff Comments</li>
        </ul>

		<h4>Adding Tags</h4>
		<p>To add tags for a specific form create a hidden field and label the field <code>tags</code>. Then, under "Advanced", add the list of tags you would like to add. Seperate multiple tags with commas.</p>

        <form action="" method="post">
            <?php wp_nonce_field("uninstall", "gf_highrise_uninstall") ?>
            <?php if(GFCommon::current_user_can_any("gravityforms_highrise_uninstall")){ ?>
                <div class="hr-divider"></div>

                <h3><?php _e("Uninstall Highrise Add-On", "gravityformshighrise") ?></h3>
                <div class="delete-alert alert_red">
                	<h3><?php _e('Warning', 'gravityformshighrise'); ?></h3>
                	<p><?php _e("This operation deletes ALL Highrise Feeds. ", "gravityformshighrise") ?></p>
                    <?php
                    $uninstall_button = '<input type="submit" name="uninstall" value="' . __("Uninstall Highrise Add-On", "gravityformshighrise") . '" class="button" onclick="return confirm(\'' . __("Warning! ALL Highrise Feeds will be deleted. This cannot be undone. \'OK\' to delete, \'Cancel\' to stop", "gravityformshighrise") . '\');"/>';
                    echo apply_filters("gform_highrise_uninstall_button", $uninstall_button);
                    ?>
                </div>
            <?php } ?>
        </form>
        <?php } // end if($api) ?>
        </div>
        <?php
    }

    public static function highrise_page(){
        if(isset($_GET["view"]) && $_GET["view"] == "edit") {
            self::edit_page($_GET["id"]);
        } else {
			self::settings_page();
		}
    }

    private static function get_api(){
    	$api = false;
        if(!class_exists("HighriseAPI"))
            require_once("api/HighriseAPI.php");

        //global highrise settings
        $settings = get_option("gf_highrise_settings");
        if(!empty($settings["url"]) && !empty($settings["token"])){
            $api = new HighriseAPI($settings["url"], $settings["token"]);
        }
        return $api;
    }


    public static function push($entry, $form_meta = array()){

	   //GET API for push { pushContact()  }
	    $api = self::get_api();
			if(!$api)
				return;

		$data = array();
		$tags = array();
		$is_tags = false;

		$data = $api->_prepareRequest($data);
		$formid = $form_meta['id'];
		$highrise = false;

		// Form Form Settings > Advanced > Enable Highrise
		if(!empty($form_meta['enableHighrise'])) { $highrise = true; }
	   //displaying all submitted fields
		foreach($form_meta["fields"] as $field){

		   if( is_array($field["inputs"]) ){

			   //handling multi-input fields such as name and address
			   foreach($field["inputs"] as $input){
			   		$value = isset($_POST["input_" . str_replace('.', '_', $input["id"])]) ? stripslashes($_POST["input_" . str_replace('.', '_', $input["id"])]) : '';
				   $label = isset($field['sTwitter']) ? $field['sTwitter'] : false;
				   $label = empty($label) ? self::getLabel($input["label"], $field, $input) : $label;
				   if(!$label) { $label = self::getLabel($field['label'], $field, $input); }
				   if(!$label) { $label = 'sBackground'; }

				   if ($label == 'sBothName') {
					    $names = explode(" ", $value);
				   		$data['sFirstName'] = $names[0];
				   		$data['sLastName'] = $names[1];
				   } else if ($label == 'sNotes') {
				   	   $message = 'true';
					   $data['sNotes'] .= "\n".$value."\n";
				   } else if ($label == 'sTags' ) {
			   	   		$is_tags = 'true';
				   		$tags[] = explode(",", $value);
			   	   } else if (trim(strtolower($label)) == 'highrise' ) {
			   	   		$highrise = $value ;
			   	   } else if($label == 'sStreet') {
			   			$data[$label] .= $value."\n";
			   	   } else {
					   $data[$label] = $value;
				   }
			   }
		   } else {
		   	   // Simple text or textarea
			   $value = @$_POST["input_" . $field["id"]];
			   if(is_array($value)) { $value = implode(',',$value); }
			   $value = stripslashes($value);
			   $label = isset($field['sTwitter']) ? $field['sTwitter'] : false;
			   $label = empty($label) ? self::getLabel($field["label"], $field) : $label;

			   if ($label == 'sBothName') {
					$names = explode(" ", $value);
					$data['sFirstName'] = $names[0];
					$data['sLastName'] = $names[1];
			   } else if ($label == 'sNotes') {
			   	   $message = 'true';
				   $data['sNotes'] .= "\n".$value."\n";
			   } else if ($label == 'sTags' ) {
			   	   		$is_tags = 'true';
				   		$tags[] = explode(",", $value) ;
			   } else if (trim(strtolower($label)) == 'highrise' ) {
			   	   		$highrise = $value ;
			   } else if($label == 'sStreet') {
			   		$data[$label] .= $value."\n";
			   } else if ($label == 'sEmail' || $label == 'sPhone' || $label == 'sFax' || $label == 'sMobile' || $label == 'sWebsite') {
					$data[$label][esc_html($field["label"])] = $value; // allow more than one email or phone
			   } else {
					$data[$label] = $value ;
			   }
		   }
	   }

	   // Process uploads

	  	if(!empty($_FILES)) {
			foreach(@$_FILES AS $key => $file) {
				if($file['size'] > 0) {
					$fileInfo = RGFormsModel::get_file_upload_path($formid, $file['name']);
					$data['sNotes'] .= "\n".'File: '.$fileInfo['url']; // add it to our note
				}
			}
		}

		if(!empty($_POST['gform_uploaded_files'])) {
			$files = GFCommon::json_decode(stripslashes(RGForms::post("gform_uploaded_files")));
			if(!is_array($files))
	            $files = array();

			if(!empty($files)) {
				foreach($files as $key => $file) {
					$fileInfo = RGFormsModel::get_file_upload_path($formid, $file);
					$data['sNotes'] .= "\n".'File: '.RGFormsModel::get_file_upload_path($formid, $fileInfo['url']); // add it to our note
				}
			}
		}

		foreach($data as $key => $value) {
			if(is_array($value)) {
				foreach($value as $k => $v) {
					if(!is_array($v)) {
						$value[$k] = trim(rtrim($v));
					}
				}
				$data[$key] = $value;
			} else {
				$data[$key] = trim(rtrim($value));
			}
		}

		/**/
	   if (isset($highrise)) {

		   $api->getPerson($data);

		   $return = $api->pushContact($data);

		   // If there's an error for some reason (there were some weird 404 issues...)
		   // then do another API call.
		   if($api->getPersonId() < 0) {
			   	$api->getPerson($data);
		   }

		   if (isset($message) && $message == 'true') {
			   $return .= $api->pushNote($data);
		   }
		   if ( $is_tags == 'true') {
			   $data['sTags'] = $tags;
			   $return .= $api->pushTags($data);
		   }

		   $personID = (string)$api->getPersonId();
		   gform_update_meta($entry['id'], 'highrise_id', $personID);

		   if(empty($api->errorMsg)) {
			   self::add_note($entry["id"], sprintf(__('Successfully added to Highrise with ID #%s . View entry at %s', 'gravity-forms-highrise'), $personID, self::getLinkToPerson($personID)));
			} else {
				self::add_note($entry["id"], sprintf(__('Errors when adding to Highrise: %s', 'gravity-forms-highrise'), $api->errorMsg));
			}

		} else {
			$return = '';
		}

	   return $return;
    }

    function getLinkToPerson($personID) {
    	$settings = get_option('gf_highrise_settings');
    	return trailingslashit( $settings['url'] ).'people/'.$personID;
    }

    /**
     * Add note to GF Entry
     * @param int $id   Entry ID
     * @param string $note Note text
     */
    private function add_note($id, $note) {

        if(!apply_filters('gravityforms_highrise_add_notes_to_entries', true)) { return; }

        RGFormsModel::add_note($id, 0, __('Gravity Forms Highrise Add-on'), $note);
    }

    /**
     * Link to the person in highrise
     * @param  int $form_id Gravity Forms Form ID
     * @param  array $lead    Lead array
     */
    function entry_info_link_to_highrise($form_id, $lead) {
        $highrise_id = gform_get_meta($lead['id'], 'highrise_id');
        $settings = get_option('gf_highrise_settings');
        if(!empty($highrise_id)) {
        	echo sprintf(__('Highrise User ID: <a href="%s">%s</a><br /><br />', 'gravity-forms-highrise'), self::getLinkToPerson($highrise_id), $highrise_id);
        }
    }

	public static function getLabel($temp, $field = '', $input = false){
		$label = false;

		if($input && isset($input['id'])) {
			$id = $input['id'];
		} else {
			$id = $field['id'];
		}

		$type = $field['type'];

		switch($type) {

			case 'name':
				if($field['nameFormat'] == 'simple') {
					$label = 'sBothName';
				} else {
					if(strpos($id, '.2')) {
						$label = 'salutation'; // 'Prefix'
					} else if(strpos($id, '.3')) {
						$label = 'sFirstName';
					} else if(strpos($id, '.6')) {
						$label = 'sLastName';
					} else if(strpos($id, '.8')) {
						$label = 'suffix'; // Suffix
					}
				}
				break;
			case 'address':
				if(strpos($id, '.1') || strpos($id, '.2')) {
					$label = 'sStreet'; // 'Prefix'
				} else if(strpos($id, '.3')) {
					$label = 'sCity';
				} else if(strpos($id, '.4')) {
					$label = 'sState'; // Suffix
				} else if(strpos($id, '.5')) {
					$label = 'sZip'; // Suffix
				} else if(strpos($id, '.6')) {
					$label = 'sCountry'; // Suffix
				}
				break;
			case 'email':
				$label = 'sEmail';
				break;
		}

		if($label) {
			return $label;
		}

		$the_label = strtolower($temp);
		$field['inputName'] = isset($field['inputName']) ? $field['inputName'] : '';

		if($the_label == 'tags' || strtolower($field['inputName']) == 'tags' || strtolower($field['inputName']) == 'stags') {
			$label = 'sTags';
		} else if ($type == 'name' && (strpos($the_label, 'first') !== false || ( strpos($the_label,"name") !== false && strpos($the_label,"first") !== false)) || strtolower($field['inputName']) == 'sfirstname') {
			$label = 'sFirstName';
		} else if ($type == 'name' && ( strpos( $the_label,"last") !== false || ( strpos( $the_label,"name") !== false && strpos($the_label,"last") !== false) ) || strtolower($field['inputName']) == 'slastname') {
			$label = 'sLastName';
		} else if ( strpos( $the_label,"name") !== false && $type == 'name' || strtolower($field['inputName']) == 'bothnames') {
			$label = 'sBothName';
		} else if ( strpos( $the_label,"company") !== false  || strtolower($field['inputName']) == 'scompany') {
			$label = 'sCompany';
		} else if ( strpos( $the_label,"email") !== false || strpos( $the_label,"e-mail") !== false || $type == 'email' || strtolower($field['inputName']) == 'semail') {
			$label = 'sEmail';
		} else if ( strpos( $the_label,"mobile") !== false || strpos( $the_label,"cell") !== false  || strtolower($field['inputName']) == 'smobile') {
			$label = 'sMobile';
		} else if ( strpos( $the_label,"fax") !== false || strtolower($field['inputName']) == 'sfax') {
			$label = 'sFax';
		} else if ( strpos( $the_label,"phone") !== false || $type == 'phone' || strtolower($field['inputName']) == 'sphone') {
			$label = 'sPhone';
		} else if ( strpos( $the_label,"city") !== false  || strtolower($field['inputName']) == 'scity') {
			$label = 'sCity';
		} else if ( strpos( $the_label,"country") !== false  || strtolower($field['inputName']) == 'scountry') {
			$label = 'sCountry';
		} else if ( strpos( $the_label,"state") !== false  || strtolower($field['inputName']) == 'sstate') {
			$label = 'sState';
		} else if ( strpos( $the_label,"zip") !== false  || strtolower($field['inputName']) == 'szip') {
			$label = 'sZip';
		} else if ( strpos( $the_label,"street") !== false || strpos( $the_label,"address") !== false  || strtolower($field['inputName']) == 'sstreet') {
			$label = 'sStreet';
		} else if ( strpos( $the_label,"website") !== false || strpos( $the_label,"web site") !== false || strpos( $the_label,"web") !== false ||  strpos( $the_label,"url") !== false || strtolower($field['inputName']) == 'swebsite') {
			$label = 'sWebsite';
		} else if ( strpos( $the_label,"highrise") !== false  || strtolower($field['inputName']) == 'highrise') {
			$label = 'highrise';
		} else if ( strpos( $the_label,"twitter") !== false  || strtolower($field['inputName']) == 'stwitter') {
			$label = 'sTwitter';
		} else if ( strpos( $the_label,"title") !== false && strpos( $the_label,"untitled") === false || strtolower($field['inputName']) == 'stitle') {
			$label = 'sTitle';
		} else if ( strpos( $the_label,"question") !== false || strpos( $the_label,"message") !== false || strpos( $the_label,"comments") !== false || strpos( $the_label,"description") !== false || strtolower($field['inputName']) == 'snotes') {
			$label = 'sNotes';
		} else if ( strpos( $the_label,"staff_comment") !== false || strpos( $the_label,"background") !== false  || strtolower($field['inputName']) == 'sbackground') {
			$label = 'sBackground';
		} else {
			$label = $temp;
		}

		return $label;
    }

    public static function disable_highrise(){
        delete_option("gf_highrise_settings");
    }

    public static function uninstall(){

        if(!GFHighrise::has_access("gravityforms_highrise_uninstall"))
            (__("You don't have adequate permission to uninstall Highrise Add-On.", "gravityformshighrise"));

        //removing options
        delete_option("gf_highrise_settings");

        //Deactivating plugin
        $plugin = "gravityformshighrise/highrise.php";
        deactivate_plugins($plugin);
        update_option('recently_activated', array($plugin => time()) + (array)get_option('recently_activated'));
    }

    private static function is_gravityforms_supported(){
        if(class_exists("GFCommon")){
            $is_correct_version = version_compare(GFCommon::$version, self::$min_gravityforms_version, ">=");
            return $is_correct_version;
        }
        else{
            return false;
        }
    }

	protected static function has_access($required_permission){
        $has_members_plugin = function_exists('members_get_capabilities');
        $has_access = $has_members_plugin ? current_user_can($required_permission) : current_user_can("level_7");
        if($has_access)
            return $has_members_plugin ? $required_permission : "level_7";
        else
            return false;
    }

    //Returns the url of the plugin's root folder
    protected function get_base_url(){
        return plugins_url(null, __FILE__);
    }

    //Returns the physical path of the plugin's root folder
    protected function get_base_path(){
        $folder = basename(dirname(__FILE__));
        return WP_PLUGIN_DIR . "/" . $folder;
    }


}
