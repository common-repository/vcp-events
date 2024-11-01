<?php
/*
Plugin Name: VCP Events
Description: Add a google plus comment stream next to a your livestream or video.
Version: 1.0
Author:Kyle Peeters
*/

/*

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNUv2 General Public License, version 2
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
//namespace vcpplugin;
if ( ! defined('ABSPATH')){
	exit;
}

function vcp_ajaxurl() {
	$html = '<script type="text/javascript">';
	$html .= 'var vcp_ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '"';
	$html .= '</script>';
	echo $html;
}
add_action('wp_head','vcp_ajaxurl');

include(plugin_dir_path(__FILE__).'kjp_custom_posts.php');
require(plugin_dir_path(__FILE__).'vcp_src_saver.php');

class VCP_info_const{
	const video_link = 'Link to the video. if the video still does not appear, try using an embeded version of the link.';
	const split = 'The video to comment ratio. Defaults to 65 meaning the video will use 65% of the alloted width and the comments fill the remaining 35%.';
	const info_content = 'the text ( or HTML) that will appear in the area directly below the video. You can recycle the content from existing VCP posts by selecting them from thier title using this dropdown menu. This area is collapsable by the user clicking the small bar at the top of it, when on the display page.';
	const theme_css = 'Allow the active theme styling (css) to style the area below video?';
	const vcp_color = 'background color. the right most box displayed the current set color inside the black border. Accepts hex color values. Click on the color preview box if color does not update. Warning: hex values always start with #, so make sure to have one at the begining.';
	const public_comment_display = 'Will public users beable to access the comment display fucntion. There is a button to the left of the refresh button (you will always see if it you are logged into wordpress as a user who has permission to publish posts) which will allow you to access this menu. This menu is purely cosmetic for having a nice display of comments you wish to highlight.';
	const manual_src_link = 'This should usually be left blank. It is for recovering comment streams when they are lost, or moving comment stream from another website. Specifically this changes the exact google+ comment stream to use. Warning: the plugin tracks the most recent comment stream and will remember it, so it will use it as the default from then on. It is highly sugdested that you make a copy of the "current src link" before changing this.';
}

class VCP_videolink extends kjp_custom_metabox{
	protected $default_video = 'https://www.youtube.com/embed/sRTFpW4SM5U';
	/*const vcp_post_hintbox = array(
	'video-link' => 'Link to the video. if the video still does not appear, try using an embeded version of the link.',
	'split' => 'The video to comment ratio. Defaults to 65 meaning the video will use 65% of the alloted width and the comments fill the remaining 35%.',
	'info-content' => 'the text ( or HTML) that will appear in the area directly below the video. You can recycle the content from existing VCP posts by selecting them from thier title using this dropdown menu. This area is collapsable by the user clicking the small bar at the top of it, when on the display page.',
	'theme-css' => 'Allow the active theme styling (css) to style the area below video?',
	'vcp-color' => 'background color. the right most box displayed the current set color inside the black border. Accepts hex color values. Click on the color preview box if color does not update. Warning: hex values always start with #, so make sure to have one at the begining.',
	'public-comment-display' => 'Will public users beable to access the comment display fucntion. There is a button to the left of the refresh button (you will always see if it you are logged into wordpress as a user who has permission to publish posts) which will allow you to access this menu. This menu is purely cosmetic for having a nice display of comments you wish to highlight.',
	'manual-src-link' => 'This should usually be left blank. It is for recovering comment streams when they are lost, or moving comment stream from another website. Specifically this changes the exact google+ comment stream to use. Warning: the plugin tracks the most recent comment stream and will remember it, so it will use it as the default from then on. It is highly sugdested that you make a copy of the "current src link" before changing this.'
	);*/

	public function setup(){
		$this->post_type = array('VCP');
		$this->name = 'VCP video settings';
		$this->label = 'VCP video settings';
		$video_link = array(
			'name' => 'video-link',
			'type' => 'text',
			'styles' => 'float:left; width:70%;',
			'value' => $this->default_video,
			'icon' => 'Link',
			'placeholder' => 'Video Link *',
			'on_change' => 'vcp_video_preview_update();'
		);
		$this->add_input_field($video_link);
		// =============================
		$split = array(
			'name' => 'vcp-split',
			'label' => 'video width percent',
			'type' => 'number',
			'placeholder' => '65',
			'value' => '65',
			'icon' => 'Link',
			'styles' => 'float:left; margin-right:15px;',
			'input_extras' => 'min="25" max="100"',
			'on_change' => 'split_preview();',
			'hintbox' => VCP_info_const::split
		);
		$this->add_input_field($split);
		// =============================
		$theme_css = array(
			'name' => 'vcp-theme-css',
			'id' => 'vcp_theme_css',
			'values' => array(true, false),
			'labels' => array('Inherit styling', 'No styling'),
			'styles' => 'float:left; margin-right:15px; margin-left:12px; padding:6px; border-left-style:solid; border-width:1px; border-color:rgba(0,0,0,0.3); opacity:0.4;',
			'classes' => 'vcp-button-disable',
			'hintbox' => VCP_info_const::theme_css
		);
		$this->add_input_field($theme_css);
		// =============================
		$info_content = array(
			'name' => 'vcp-info-content',
			'icon' => 'InfoBoxIcon',
			'title' => 'Info Box',
			'values' => array(),
			'labels' => array('Use Below'),
			'id' => 'info_content',
			'styles' => 'float:left; width:49%;box-sizing: border-box; height:38px !important; font-size:32px !important;',
			'label' => 'Info box from',
			'hintbox' => VCP_info_const::info_content,
			'on_change' => 'vcp_infobox_hide(`'.'kjp_editor_vcp_info'.'`);'
		);
		$this->add_input_field($info_content);
		// =============================
		$color = array(
			'name' => 'vcp-color',
			'label' => 'Hex',
			'icon' => 'Link',
			'placeholder' => 'Background (url/hex)',
			'type' => 'text',
			'value' => '#fff',
			'styles' => 'float:left; width:49%;box-sizing: border-box; height:38px !important;',
			'on_change' => 'vcp_color_preview_set();',
			'hintbox' => VCP_info_const::vcp_color,
			'input_extras' => 'style="width:94px; height:28px;"'
		);
		$this->add_input_field($color);
		// =============================
		$comment_tracker = array(
			'name' => 'vcp-public-comment-display',
			'label' => 'allow public comment display',
			'title' => 'Comment Display',
			'icon' => 'Comment_Display',
			'values' => array(false, true),
			'labels' => array('Private', 'Public'),
			'styles' => 'float:right; margin-right:5px; width:49%;box-sizing: border-box; height:38px !important; font-size:24px !important;',
			'hintbox' => VCP_info_const::public_comment_display
		);
		$this->add_input_field($comment_tracker);
		// =============================
		$fullscreen = array(
			'name' => 'vcp-fullscreen-comment-display',
			'label' => 'Allow Fullscreen',
			'title' => 'Allow Fullscreen',
			'icon' => 'Fullscreen',
			'values' => array(true, false),
			'labels' => array('Yes', 'No'),
			'styles' => 'float:right; margin-right:5px; width:49%;box-sizing: border-box; height:38px !important; font-size:24px !important;',
			'hintbox' => VCP_info_const::public_comment_display
		);
		$this->add_input_field($fullscreen);
		// =============================
		$comment_src = array(
			'name' => 'manual-src-link',
			'type' => 'text',
			'styles' => 'float:left; margin-right:5px; margin-left:12px; padding:6px; border-left-style:solid; border-width:1px; border-color:rgba(0,0,0,0.3;)',
			'input_extras' => ' style="min-width:40px;max-width:320px;width:65%"',
			'hintbox' => VCP_info_const::manual_src_link,
			'sanitize' => false
		);
		$this->add_input_field($comment_src);
		// =============================
		$this->add_wp_editor_field('vcp-info');// wp_content editor
	}

	public function get_vcp_posts ($args = array()){
		$args = array(
            'post_type' => 'VCP'
        );
        $vcp_posts = array();
        $query = new WP_Query($args);
        if ($query->have_posts()){
            while($query->have_posts()){
                $query->the_post();
                $vcp_posts[] = $query->post;
            }
            wp_reset_postdata();
        }
        return $vcp_posts;
	}

	public function script_HTML (){
		?>
		<script>
		function vcp_infobox_hide (target_ID){
			var cur_id = jQuery("#post_ID").val();
			var cur_editor = jQuery("#"+target_ID);
			var select = jQuery("#info_content").find('option:selected').val();
			if (select == cur_id){
				cur_editor.removeClass("vcp-v-collapsed");
			}
			else {
				cur_editor.addClass("vcp-v-collapsed");
			}
		}
		function split_preview (){
			var video_prev = jQuery("#vcp-split-box-left");
			var video_prev_right = jQuery("#vcp-split-box-right");
			var split = jQuery("#vcp-split-id").val();
			video_prev.css("width", split+"%");
			video_prev.css("height", (180*(split * 0.01))+"px");
			split = -1*(split-100);
			video_prev_right.css("width", split+"%");
		}
		function vcp_video_preview_update(){
			var link = jQuery("#video-link-id").val();
			link = link.replace(`watch?v=`, `embed/`);
			link = link.split("&t")[0];
			var preview_iframe = jQuery("#vcp-video-preview");
			preview_iframe.attr("src", link);
		}
		</script>
		<?php
	}

	public function render_HTML($post){
		$meta = $this->stored_meta['video_link'];
		?>
		<div class="vcp-setting-sect cf">
			<input type="hidden" id="vcp-saved-video-link" value="<?php echo esc_attr($meta[0]); ?>">
			<?php
			$videolink_popup = new kjp_popup(array(
				'title' => 'Video Link',
				'message' => 'Paste the embeded link from any video you want to have displayed here. Click the "Update Preview" to see a preview of the link. You can use the reset button to return to the most recently saved link if needed. If the video is not working please be sure to double check you are using an embeded link and not a standard URL. Embeded links often start with "<iframe.. with information here ..>"',
				'styles' => 'margin-left:-12px;',
				'popup_width' => '350px'
			));
			$reset_popup = new kjp_popup(array(
				'title' => 'Reset Link',
				'message' => 'You may change the video link back to the most recently saved one by clicking the "Link" button, and you can reset the link to the VCP tutorial video by clicking "Tutorial".',
				'styles' => 'margin-left:-12px;',
				'popup_width' => '350px',
				'buttons' => array(array('Link','vcp_revert_Link'), array('Tutorial','vcp_revert_tutorial'))
			));
			$pagecode_popup = new kjp_popup(array(
				'title' => 'Page Code',
				'message' => 'Paste this code into any page or post you want to display the VCP post in. Warning: you cannot have more than one VCP post on the same page at this time.',
				'styles' => 'margin-left:-12px;',
				'popup_width' => '350px',
				'textfield' => $this->get_shortcode($post)
			));
			$background_color_popup = new kjp_popup(array(
				'title' => 'Background Color',
				'message' => 'This will determine the background for the info box area (the area directly below the video where the title is displayed). Enter in a link to an image or a hex color value.',
				'styles' => 'margin-left:-12px;',
				'popup_width' => '350px'
			));
			$comment_display_popup = new kjp_popup(array(
				'title' => 'Comment Display Accessibility',
				'font_size' => '20px',
				'message' => 'Determines whether normal users can use the comment display feature (public). If set to private only users who are logged into your wordpress admin panel will see this option.',
				'styles' => 'margin-left:-12px;',
				'popup_width' => '350px'
			));
			$info_box_popup = new kjp_popup(array(
				'title' => 'Info Box Content',
				'message' => 'The Info Box (the area directly below the video), can have unique content by selecting "Use this post" and filling in the standard wordpress content below, or you can use an existing info box\'s content by selecting it by title in the dropdown.',
				'styles' => 'margin-left:-12px;',
				'popup_width' => '350px'
			));
			$fullscreen_popup = new kjp_popup(array(
				'title' => 'Allow Fullscreen',
				'message' => 'Unfortunately some themes will prevent the fullscreen feature from actually moving everyhing to the front of your page as well as some other potential problems. Should you have problems with the way fullscreen interacts on your site you can disable it here.',
				'styles' => 'margin-left:-12px;',
				'popup_width' => '350px'
			));
			$split_popup = new kjp_popup(array(
				'title' => 'Ratio',
				'message' => 'This is what percentage of the total width the video will use and leave over for the comment section. There is a minimum and maximum size for the video and comments, and if the value is too high or low this can result in the layout defaulting to the stacked layout. The stacked layout has the comment stream below the video. The default amount is 65. ',
				'styles' => 'margin-left:-12px;',
				'popup_width' => '350px'
			));
			?>
			<div class="cf" style="min-width:430px;width:110%;margin:2px 0px 15px -5%;height:54px;box-shadow:0px 4px 12px lightgray inset;padding:5px 2px 5px 5%;">
				<img style="height:44px; float:left; margin:0px 4px;" src="<?php echo vcp_get_icon('VCPlogo').'.png'; ?>">
				<img style="height:44px; float:left; margin:0px 12px 0px 4px;" src="<?php echo vcp_get_icon('GplusLogo').'.png'; ?>">
				<h1 style="font-family: Calibri; font-weight:500; font-size:42px;">Video Comments Plus</h1>
			</div>
			<div class="nice-field-row cf">
				<?php $this->nice_text_field('video-link', $videolink_popup->get_open_class());?>
				<div id="vcp-video-link-buttons" style="width:316px; float:right;">
					<button style="width:110px; float:right;" name="vcp-getpagecode" type="button" class="button button-primary button-large <?php echo $pagecode_popup->get_open_class(); ?>" id="vcp-getpagecode"> Get Page Code </button>
					<button style="width:64px; float:right;" name="vcp-reset" type="button" class="button button-primary button-large <?php echo $reset_popup->get_open_class(); ?>" id="vcp-reset"> Reset </button>
					<button style="width:118px; float:right;" name="vcp-update-video" type="button" class="button button-primary button-large" id="vcp-update-video"> Update Preview </button>
					<script type="text/javascript">
						jQuery(window).load(function(){
							jQuery("#vcp-update-video").click(function(){vcp_video_preview_update();});
							vcp_video_preview_update();
						});
					</script>
				</div>
			</div>
			<div style="margin:12px 0px; overflow-y:hidden; width:100%;" class="cf">
				<div class="cf" style="float:left; margin: 0px; width:60%;">
				<?php
				$this->basic_video(array(
					'link' => esc_attr($meta[0]),
					'width' => '100%',
					'style' => 'float:left; margin: 0px;',
					'id' => 'vcp-video-preview',
					));
				?>
				<div style="float:left; width:100%;">
					<?php echo $this->info_box_preview($post->ID); ?>
				</div>
				</div>
				<img style="width:38%; float:right;" src="<?php echo vcp_get_icon('vcp_comments_preview').'.png'; ?>" />
			</div>
			<div class="nice-field-row cf">
				<?php 
				$this->nice_text_field('vcp-color', $background_color_popup->get_open_class());
				$this->nice_dropdown('vcp-public-comment-display', $comment_display_popup->get_open_class());
				?>
			</div>
			<div class="nice-field-row cf">
				<?php 
				$vcp_posts = $this->get_vcp_posts();
				$vcp_ids = array($post->ID);
				$vcp_titles = array('Use this post');
				foreach ($vcp_posts as $key => $vcp) {
					if ($post->ID != $vcp->ID){
						$vcp_ids[] = $vcp->ID;
						$vcp_titles[] = $vcp->post_title;
					}
				}
				$this->input_args['vcp-info-content']['labels'] = $vcp_titles; 
				$this->input_args['vcp-info-content']['values'] = $vcp_ids;
				$this->nice_dropdown('vcp-info-content', $info_box_popup->get_open_class());
				$this->nice_dropdown('vcp-fullscreen-comment-display', $fullscreen_popup->get_open_class());
				?>
			</div>
			<div class="nice-field-row cf">
				<?php $this->nice_number_field('vcp-split', $split_popup->get_open_class()) ?>
			</div>
			<div>
				<?php $this->wp_editor($post, 'vcp-info'); ?>
			</div>
			<?php $this->run_vcp_js_field_resizing(); ?>
			<?php $this->run_js_on_resize(); ?>
			<?php $this->run_js_on_ending(); ?>
		</div><?php
	}

	public function info_box_preview($post_id){
		$info_title = '<h1>'.get_post($post_id)->post_title.'</h1>';
		$meta = get_post_meta($post_id);
		$id_content = $meta['vcp_info_content'][0];
		$info_content = get_post_meta($id_content, 'vcp_info');
		$info_content = apply_filters('the_content', $info_content[0]);
		//$inherit_header = ($this->meta['vcp_theme_css'][0] == true) ? 'vcp_match_header("vcp-info-box-frame");' : '' ;
		return $info_title.$info_content;
	}

	public function run_vcp_js_field_resizing (){
		?>
		<script type="text/javascript">
			function vcp_on_resize(c,t){onresize=function(){clearTimeout(t);t=setTimeout(c,100)};return c};  
			function field_resizes(){
				var input_field_div = jQuery("#video-link-div");
				var btns_div = jQuery("#vcp-video-link-buttons");
				var vid_div = input_field_div.parent();
				var mainWidth = jQuery("#video-link-div").parent().width();
				w = vid_div.width() - 320;
				input_field_div.width(w);
			}
			jQuery(window).load(field_resizes());
			window.addEventListener('resize', field_resizes);
		</script>
		<?php
	}

	public function render_HTML0 ($post){
		
		$this->script_HTML();
		
		$section_args = array(
			'start' => true,
			'class' => 'vcp-setting-sect cf'
			);
		$this->contain($section_args);
		$this->basic_field('video-link');
		$this->basic_video(array(
			'link' => 'https://www.youtube.com/watch?v=-VVVQ4rmEyU',
			'width' => '60%',
			'style' => 'float:right',
			'id' => 'vcp-video-preview',
		));
		echo "<script>vcp_video_preview_update();</script>";
		
		$this->contain(array('start' => true, 'class' => 'vcp-setting-sect cf', 'style' => 'float:left;min-width:40px;max-width:450px;width:35%;border-style:solid;background-color:rgb(240,240,240); padding:15px;'));
		$this->basic_field('vcp-split');
			echo($this->get_split_box());
		$this->contain();
		$this->contain();
		
		$this->contain($section_args);
		
		
			$this->contain(array('start' => true, 'style' => 'margin-bottom:25px;'));
				$this->basic_field('vcp-color');
				$preview_color = get_post_meta($post->ID, 'vcp_color', true);
				$preview_color = str_replace('#', '', $preview_color);
				if (ctype_xdigit($preview_color)) {
					$preview_color = '#'.$preview_color;
				} 
				else {
					echo '<script>alert(\'The background color is not currently a hex value. Currently set to "'.$preview_color.'", Hex example: "f0f0f0"\');</script>';
					$preview_color = '#f0f0f0';
				}
				echo('<div id="vcp-color-preview" onclick="vcp_color_preview_set()" style="background-color:'.$preview_color.'"></div>');
				
				
				$this->basic_dropdown('vcp-public-comment-display');
				//$this->basic_field('manual-src-link');
				$def_src_link = get_post_meta($post->ID, 'vcp_comment_source_link', true);
				if (is_string($def_src_link)) {
					if ($def_src_link == "") {
						$def_src_link = "load a page containing the shortcode to generate.";
					}
				}
				else{
					$def_src_link = "load a page containing the shortcode to generate.";
				}
				echo '<div style="max-height:80px; max-width:300px; float:left; overflow:hidden;"><h3>Manual src link (triple click below):</h3>
						<p>'.$def_src_link.'</p>
					</div>';
				//echo '</div>';
				
				$vcp_posts = $this->get_vcp_posts();
				$vcp_ids = array($post->ID);
				$vcp_titles = array('Use this post');
				foreach ($vcp_posts as $key => $vcp) {
					if ($post->ID != $vcp->ID){
						$vcp_ids[] = $vcp->ID;
						$vcp_titles[] = $vcp->post_title;
					}
				}
			$this->contain();
		$this->contain();
		$this->contain($section_args);
			$this->contain(array('start' => true, 'style' => 'display:block; margin:0px 0px 70px 0px'));
				$this->basic_dropdown('vcp-info-content', $vcp_ids, $vcp_titles);
			$this->contain();
			$this->wp_editor($post, 'vcp-info');
		$this->contain();
		echo('<script>vcp_infobox_hide("kjp_editor_vcp_info"); split_preview();</script>');
		
        $instructions = '<legend>Hit Update, then copy the shortcode below "[vcp id=a-number]" and paste it into a page:</legend>';
		$fieldset = '<fieldset name="vcp-shortcode-field" style="margin-top:15px; padding:15px; background-color:rgb(220,220,220);">';
		echo $fieldset.$instructions.'<span>'.$this->get_shortcode($post).'</span></fieldset>';
	}

	function get_split_box (){
		$box = '<div id="vcp-split-box">';
			$box .= '<div id="vcp-split-box-left">';
			$box .= '</div>';
			$box .= '<div id="vcp-split-box-right">';
			$box .= '</div>';
		$box .= '</div>';
		$box .= '<div style="width:100px">';
			$box .= '<span class="vcp-key-element" style="border-color:red;"> video </span>';
			$box .= '<span class="vcp-key-element" style="border-color:blue;"> comments </span>';
		$box .= '</div>';
		return $box;
	}

	function get_shortcode ($post){
		return '[vcp id='.$post->ID.']';
	}
}
class VCP_src_links extends kjp_custom_metabox{
	public function setup(){
		$this->post_type = array('VCP');
		$this->name = 'VCP comments source links';
		$this->label = 'VCP comments source links';
	}

	public function render_HTML ($post){
		$options = '';
		//$download_xml = plugin_dir_url(__FILE__).'vcp-download-xml.php';
		//$db_btn = '<button type="button"  class="button button-primary" onclick="location.href=\''.$download_xml.'\'">get xml database</button>';
		$sources = vcp_db_get_urls($post->ID);
		if (count($sources) == 0){
			echo '<h2>No pages have loaded this yet. Load a page with video comments plus\'s shotcode (while logged into wordpress), and a comments stream will appear here for preivew and backup purposes. While on the page you will know if the link was saved successfully because the left most button (near full screen and refresh) will turn green (this can take up to 20 seconds on a slow connection).</h2>';
			echo '<p>The purpose of the links is, so you can put the comments on another page. It will work anywhere that accespts HTML code. You can still download a backup of your comment source links below.</p>';
			
			return;
		}
		$sel = 'selected';
		$def = array();
		foreach ($sources as $key => $value) {
			$options .= '<option '.$sel.' value="'.$value[1].'">'.$value[0].'</option>';
			if ($sel != '') {
				$def = array($value[0], $value[1]);
			}
			$sel = '';
		}
		?>
		<div class="cf">
			<div id="vcp-src-links">
				<select name="vcp-src-links-dd" id="vcp-src-links-dd" onchange="vcp_comment_preview_change();">
					<?php echo $options ?>
				</select>
				<fieldset id="vcp-src-link-url" name="vcp-src-link-url" class="vcp-src-fieldset">
					<legend for="vcp-src-link-url"> the url the comments stream is from.</legend>
					<div style="width:500px; max-width:100%; word-wrap: break-word;">
						<span id="vcp-src-link-url-box" name="vcp-src-link-src-url"><?php echo $def[0]; ?></span>
					</div>
				</fieldset>
				<fieldset id="vcp-src-link-src" name="vcp-src-link-src" class="vcp-src-fieldset">
					<legend for="vcp-src-link-src"> the url of the comments stream.</legend>
					<div style="width:500px; max-width:100%; word-wrap: break-word;">
						<span id="vcp-src-link-src-box" name="vcp-src-link-src-box" style="max-width:100%;"><?php echo $def[1]; ?></span>
					</div>
				</fieldset>
			</div>
			<div id="vcp-src-link-comments" style="width:50%">
				<?php echo '<iframe id="vcp-src-comments-preview" src="'.$def[1].'"></iframe>'; ?>
			</div>
			<?php echo $db_btn; ?>
		</div>
		<?php
	}
}

// ======================================== end metaboxes ========================================================

class VCP_Post extends kjp_custom_post{
	function setup (){
		$this->singular = 'VCP';
		$this->plural = 'Video Comments Plus';
		$this->args['menu_icon'] = 'dashicons-playlist-video';
		$this->args['menu_position'] = 58;
		$this->supports = array_diff($this->supports, array('editor', 'custom-fields'));
		$this->meta_boxes[] = new VCP_videolink();
		//$this->meta_boxes[] = new VCP_src_links(); depriciated.
	}
}


if( is_admin() ){
    $vcp_post_type = new VCP_Post();
}







function vcp_enqueue_scripts(){
	wp_enqueue_style( 'vcp-css', plugins_url( "vcp.css", __FILE__));
	wp_enqueue_script( 'vcp-js', plugins_url( "vcp.js", __FILE__));
}
add_action('wp_enqueue_scripts', 'vcp_enqueue_scripts');
add_action('admin_head', 'vcp_enqueue_scripts');


function vcp_get_icon ($name) {
	//return plugins_url().'/Video-Comments-Plus/icons/'.$name;
	return plugins_url('icons/'.$name, __FILE__);
}

function vcp_get_svg_img_src ($icon_name){
	$svg = vcp_get_icon($icon_name.'.svg');
	$png = vcp_get_icon($icon_name.'.png');
	return "src=\"$svg\" alt=\"$png\"";
}


include(plugin_dir_path(__FILE__).'vcp_options_menu.php');
include(plugin_dir_path(__FILE__).'vcp-shortcode.php');
include(plugin_dir_path(__FILE__).'vcp-comments-display.php');


class vcp_attr{
	
	protected $field_classes = 'regular-text';

	protected $name;
	protected $label;
	protected $field_type;
	protected $def_value;
	protected $field_attrs;
	
	public function __construct ($name, $label, $field_type = 'text', $def_value = ''){
		$this->name = $name;
		$this->label = $label;
		$this->field_type = $field_type;
		$this->def_value = $def_value;
		//$this->field_attrs = $field_attrs;
	}
	
	public function get_name (){return $this->name;}
	public function get_label (){return $this->label;}
	public function get_field_type (){return $this->field_type;}
	public function get_def_value (){return $this->def_value;}

	
	public function get_safe_name (){
		return str_replace(' ', '_', $this->label);
	}
	public function get_field (){
		//if ($this->get_field_type() == 'dropdown'){return $this->get_dropdown();}
		$t = array(
            '<tr>',
            '<th>',
            '</th>',
            '<td>',
            '</td>',
            '</tr>',
        );
        $safe_name = $this->get_safe_name();
        $name = $this->get_name();
        $label = $this->get_label();
        $field_type = $this->get_field_type();
        $def_value = $this->get_def_value();
        $field_classes = $this->field_classes;
        $attrs = '';
        for ($i=0; $i < count($this->field_attrs); $i++) {
	        	$attrs .= ' '.$this->field_attrs[$i].' ';
	    }
        $label = "<label for=\"$safe_name\">$label : </label>";
        $input_field = '';
        if($this->get_field_type() == 'dropdown'){
        	$input_field = $this->get_dropdown();
    	}
    	else if($this->get_field_type() != 'dropdown'){
    		$input_field = "<input id=\"$safe_name\" name=\"$name\" $attrs type=\"$field_type\" value=\"$def_value\" class=\"$field_classes\">";
    	}
        return $t[0].$t[1].$label.$t[2].$t[3].$input_field.$t[4].$t[5];
	}

	public function set_field_attrs ($attrs){
		$this->field_attrs = $attrs;
	}

	protected $dropdown_id;
	protected $dropdown_item_ids;
	protected $dropdown_item_labels;
	public function set_dropdown($dropdown_id, $dropdown_item_ids, $dropdown_item_labels){
		$this->dropdown_id = $dropdown_id;
		$this->dropdown_item_ids = $dropdown_item_ids;
		$this->dropdown_item_labels = $dropdown_item_labels;
	}
	public function get_dropdown(){
		$id = $this->get_safe_name();
        $dropdown = "<select id=\"$id\">";
		for ($i=0; $i < count($this->dropdown_item_ids); $i++) { 
            $id =$this->dropdown_item_ids[$i];
            $label = $this->dropdown_item_labels[$i];
            $dropdown .= "<option value=\"$id\">$label</option>";
        }
        $dropdown .= '</select>';

        return $dropdown;
	}
}


class vcp_options 
{
	//protected $default_video = 'https://www.youtube.com/embed/sRTFpW4SM5U';
	public $options = array();

	public function gen_options (){
		$this->options = array (
			new vcp_attr('src', 'video link', 'text', '', array()),
			new vcp_attr('split', 'video width percentage', 'number', '65'),
			new vcp_attr('post', 'info post', 'dropdown'),
			new vcp_attr('t', 'title', 'text'),
			new vcp_attr('d', 'description', 'text'),
			new vcp_attr('di', 'image link', 'text'),
			new vcp_attr('id', 'vcp post id', 'number')
		);
		$split_field_index = $this->get_vcp_attr_index('post');
		$this->options[$split_field_index]->set_field_attrs(array('min="25"', 'max="100"'));
		$this->set_dropdowns_options();

	}

	public function set_dropdowns_options (){
		$posts_ids = array('');
		$posts_titles = array('none');
		$vcp_posts = $this->get_vcp_WP_posts();
		for ($i=0; $i < count($vcp_posts); $i++) {
			$posts_ids[] = $vcp_posts[$i]->ID;
			$posts_titles[] = $vcp_posts[$i]->post_title;
		}
		$post_field_index = $this->get_vcp_attr_index('post');
		$this->options[$post_field_index]->set_dropdown("vcp-content-post", $posts_ids, $posts_titles);
	}

	public function get_vcp_WP_posts (){
        $args = array(
            'post_type' => 'post',
            'category_name' => 'vcp'
        );
        $vcp_posts = array();
        $query = new WP_Query($args);
        if ($query->have_posts()){
            while($query->have_posts()){
                $query->the_post();
                $vcp_posts[] = $query->post;
            }
            wp_reset_postdata();
        }
        return $vcp_posts;
	}

	public function get_vcp_attr($name){
		for ($i=0; $i < count($this->options); $i++) { 
			if ($this->options[$i]->get_name() == $name){
				return $this->options[$i];
			}
		}
	}

	public function get_vcp_attr_index($name){
		for ($i=0; $i < count($this->options); $i++) { 
			if ($this->options[$i]->get_name() == $name){
				return $i;
			}
		}
	}

	public function get_html_attr_list (){
		$attr_list = "<div id=\"vcp-written-attr-list\" style=\"display:none;\">";
		for ($i=0; $i < count($this->options); $i++) { 
			$name = $this->options[$i]->get_name();
			$label = $this->options[$i]->get_safe_name();
			$attr_list .= "<p name=\"$name\" label=\"$label\"></p>";
		}
		$attr_list .= '</div>';
		return $attr_list;
	}


}?>