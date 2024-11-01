<?php

function get_vcp_comment_display(){
	$display = '<div id="vcp-cover-comments-display" class="black-cover vcp-v-collapsed">';
		$display .= '<div class="black-cover" onclick="vcp_focus_off(`vcp-cover-comments-display`)"></div>';
		//$display .= '<div class="vcp-noclick">';
		$display .= '<div id="vcp-comments-display" class="vcp-fixed-c">';
			$display .= '<a class="vcp-floater vcp-noclick" style="bottom:-50px; color:white;">click background to close.</a>';
			//$display .= '<iframe id="vcp-comment-display-frame" src="'.plugin_dir_url(__FILE__).'vcp-comments-display.php'.'"></iframe>';
			$display .= get_vcp_comment_setup_HTML();
			$display .= get_vcp_comment_display_HTML();
		$display .= '</div>';
		//$display .= '</div>';
	$display .= '</div>';
	return $display;
}

function get_vcp_comment_setup_HTML(){
	$labels_style = ' style="margin:4px 0px 1px 0px; display: inline-block;" ';
	$from_gplus_link = '<form style="margin:auto; width:320px; opacity:0.5;" class="vcp-line-b vcp-line-t vcp-noclick">
				<label for="vcp-gp-link" '.$labels_style.'>g+ link : </label>
				<input style="width:250px;" type="text" name="vcp-gp-link"></input>
			</form>
			<div style="margin: 8px auto; width:150px; opacity:0.5;"><button class="vcp-button vcp-button-disable" style="width:100%;" type="button">from link above?</button></div>';
	$from_gplus_link = '<div style="height:10px;"></div>'; // clear from gplus link
	// font color
	$font_colors = array('black','white','darkgray','lightgray');
	for ($i=0; $i < count($font_colors); $i++) { 
		$font_colors[$i] = '<option value="'.$font_colors[$i].'">'.$font_colors[$i].'</option>';
	}
	$font_colors = implode('', $font_colors);
	$font_style = array(
			'Helvetica,Ariel,sans-serif',
			'Times New Roman, Georgia, Serif',
			//"'Fredoka One', cursive",
			//"'Russo One', sans-serif",
			//"'Hammersmith One', sans-serif",
			//"'Timmana', sans-serif",
			//"'Michroma', sans-serif",
			//"'Viga', sans-serif",
			//"'Asul', sans-serif",
			//"'Righteous', cursive",
			//"'Londrina Shadow', cursive",
			//"'Bowlby One SC', cursive"
		);
	$custom_fonts = get_option(vcp_wpset_gfonts."_css");
	$custom_fonts = str_replace("font-family: ", "", $custom_fonts);
	$custom_fonts = str_replace("&apos", "'", $custom_fonts);
	$custom_fonts = explode(";", $custom_fonts);
	$font_style = array_merge($font_style, $custom_fonts);
	for ($i=0; $i < count($font_style); $i++) { 
		$font_style[$i] = '<option value="'.$font_style[$i].'">'.$font_style[$i].'</option>';
	}
	$font_style = implode('', $font_style);
	// background images
	$background_options = '';
	foreach (array('white', 'GhostWhite', 'gray', 'black') as $key => $color) {
		//break;//prevents these from being added
		$background_options .= '<option value="'.$color.'">'.$color.'</option>';
	}
	$vcp_sub_page = new vcp_sub_page();
	for ($i=0; $i < vcp_num_of_background_images; $i++) { 
		$value = get_option(vcp_wpset_bkimage.$i);
		$title = get_option(vcp_wpset_bk_img_titles.$i);
		if($value != ''){
			$background_options .= '<option value="-'.$value.'">'.$title.'</option>';
		}
	}
	$h1Styles = ' style="margin-top: 20px !important; margin-bottom: 10px !important; width:100% !important; text-align: center !important; display:block !important"; font-size: 36px !important;';
	$inputStyles = 'style="padding: 3px !important; margin:0px 0px 4px 0px; !important; border-radius:1px !important; width:100% !important; resize: none !important; box-sizing: border-box !important; height:24px !important; border-color:rgb(150, 150, 150) !important;" ';
	$textareaStyles = str_replace("height:24px", "height:100px", $inputStyles);

	return'
	<div id="vcp-comment-setup" class="cssdefault vcp-v-collapsed cf vcp-abs-size">
		<div id="vcp-comment-setup-left" style="float:left; padding:0px 9px" class="cf vcp-abs-size">
			<h1 class="cssdefault"'.$h1Styles.'>Comment viewer</h1>
			'.$from_gplus_link.'
			<div id="vcp-comment-input">
				<div id="vcp-comment-input-1" class="vcp-count">
					<form style="margin:12px auto; padding:10px; width:320px;" class="vcp-line-b vcp-line-t">
						<label for="vcp-gp-title" '.$labels_style.' class="cssdefault">Title : </label>
						<input '.$inputStyles.' class="cssdefault vcp-border-transparent" style="width:100%;" type="text" name="vcp-gp-title"></input>
						<br>
						<label for="vcp-gp-name" '.$labels_style.' class="cssdefault">Posted by : </label>
						<input '.$inputStyles.' class="cssdefault vcp-border-transparent" style="width:100%;" type="text" name="vcp-gp-name"></input>
						<br>
						<label for="vcp-gp-content" '.$labels_style.' class="cssdefault">Message : </label>
						<textarea class="cssdefault vcp-border-transparent" '.$textareaStyles.' type="text" cols="40" rows="6" name="vcp-gp-content"></textarea>
						<label for "vcp-gp-style" '.$labels_style.' class="cssdefault">Font:</label>
						<div>
						<select class="cssdefault vcp-border-transparent" name="vcp-gp-style" '.$inputStyles.'>
							'.$font_style.'
						</select>
						<select class="cssdefault vcp-border-transparent" name="vcp-gp-font-color" '.$inputStyles.'>
							'.$font_colors.'
						</select>
							<select class="cssdefault vcp-border-transparent" name="vcp-gp-size" '.$inputStyles.'>
								<option value = "100">small</options>
								<option value = "200" selected="selected">Medium</options>
								<option value = "300">large</options>
								<option value = "400">x-large</options>
							</select>
						</div>
						<label for "vcp-gp-bk" '.$labels_style.'>background:</label>
						<select class="cssdefault vcp-border-transparent" name="vcp-gp-bk" '.$inputStyles.'>
							'.$background_options.'
						</select>
					</form>
				</div>
			</div>
			<div style="margin: 8px auto; width:150px">
				<button class="cssdefault vcp-button vcp-button-text" id="vcp-comment-setup-toggle" style="width:100%;" type="button">display</button></div>
			<div style="margin: 0px auto; width:200px" class="cf">
				<button class="cssdefault vcp-button vcp-button-text" id="vcp-comment-remove-setup" style="width:40%; float:left; background-color:rgb(245, 221,221) !important;" type="button">remove</button>
				<button class="cssdefault vcp-button vcp-button-text" id="vcp-comment-add-setup" style="width:40%; float:right; background-color:rgb(221, 245,221) !important;" type="button">add</button>
			</div>
			<div id="vcp-comment-display-index-btns" style="">
				<button selected class="cssdefault vcp-button vcp-button-text" id="vcp-comment-display-toggle-1" style="width:30px; margin:0 3px;background-color:rgb(221, 245,221) !important" type="button">1</button>
			</div>
		</div>
		'.get_vcp_comment_display_iframe_HTML().'
	</div>';
}

function vcp_get_googlefonts_aslist(){
	$ref = get_option(vcp_wpset_gfonts);
}


function get_vcp_comment_display_HTML(){
	$btn_classes = 'class="vcp-button vcp-light-gray vcp-btn-sm"';
	$icons = array(
		vcp_get_svg_img_src("Previous"),
		vcp_get_svg_img_src("Comment_Display"),
		vcp_get_svg_img_src("Fullscreen"),
		vcp_get_svg_img_src("Next")
	);

	return'
	<div id="vcp-comment-view">
		<h1 style="width:100%;text-align: center; display:block">"Title"</h1>
		<div style="background-color:rgba(240,240,240,0.4); margin:32px" class="vcp-line">
			<p style="font-size:xx-large; text-align:center;">"Message"</p>
		</div>
		<div style="width:100%; padding: 12px 64px; float:right;" class="cf"><span style="float:right; font-size:150%;">posted by : "poster name"</span></div>
		<div id="vcp-comment-view-btns" style="position:absolute; bottom:12px; width:100%;">
			<div style="margin: 18px auto; width:75%">
				<img title="previous comment" style="float:left;" '.$icons[0].$btn_classes.' id="vcp-comment-view-previous"></img>
				<img title="back to edits" style="float:left;" '.$icons[1].$btn_classes.' id="vcp-comment-view-toggle"></img>
				<img title="fullscreen" style="float:left;" '.$icons[2].$btn_classes.' id="vcp-comment-view-fullscreen"></img>
				<img title="next comment" style="float:left;" '.$icons[3].$btn_classes.' id="vcp-comment-view-next"></img>
			</div>
		</div>
	</div>';
}

function get_vcp_comment_display_iframe_HTML(){
	$styles = 'float:right; margin: 0px; border-style: none; overflow: hidden;';
	$iframe_styles = 'top: 0px; margin: 0px; border-style: none; left: 0px; visibility: visible;';
	$iframe_attrs = 'ng-non-bindable="" frameborder="0" hspace="0" marginheight="0" marginwidth="0" scrolling="auto" style="'.$iframe_styles.'" tabindex="0" vspace="0" width="100%" height="100%"';
	$src = '';
	$icon = vcp_get_svg_img_src('Refresh');
	return'
	<div id="vcp-comments-setup-stream-div" style="'.$styles.'" class="cf vcp-abs-size">
		<img title="Refresh comment display stream." style="position:absolute; /*left:394px;*/ bottom:6px;" id="vcp-gpcomment-display-refresh" class="vcp-button vcp-light-gray vcp-btn-sm" '.$icon.'></img>
		<iframe '.$iframe_attrs.' class="vscroll" id="vcp-comments-setup-stream" src="'.$src.'""></iframe>
	</div>
	';
}





?>
