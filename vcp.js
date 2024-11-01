function vcp_btn_clickers(){
	jQuery("#vcp-comment_display-btn").click(function(){vcp_focus_on("vcp-cover-comments-display");vcp_toggle_comment_display(true);});
	jQuery("#vcp-fullscreen-btn").click(function(){vcp_fullscreen();});
	jQuery("#vcp-refresh-btn").click(function(){vcp_refresh_comments();});
	jQuery("#vcp-comments_src-btn").click(function(){vcp_src_link();});
	jQuery("#vcp-more-btn").click(function(){vcp_more_menu();});
	jQuery("#vcp-comment-setup-toggle").click(function(){vcp_toggle_comment_display(false);});
	jQuery("#vcp-comment-view-toggle").click(function(){vcp_toggle_comment_display(false);});
	jQuery("#vcp-comment-add-setup").click(function(){vcp_comment_add_display();});
	jQuery("#vcp-comment-remove-setup").click(function(){vcp_comment_remove_display();});
	jQuery("#vcp-comment-display-toggle-1").click(function(){vcp_comment_set_index(this);});
	jQuery("#vcp-comment-view-fullscreen").click(function(){vcp_fullscreen_comment_display();});
	jQuery("#vcp-comment-view-next").click(function(){vcp_comment_next();});
	jQuery("#vcp-comment-view-previous").click(function(){vcp_comment_previous();});
	jQuery("#vcp-gpcomment-display-refresh").click((function(){
		jQuery("#vcp-comments-setup-stream").attr("src","");
		setTimeout(function(){vcp_refresh_gpcomments_commentsdisplay()}, 10);
	}));

	//jQuery("#vcp-comment-display-toggle-1").click(function(){vcp_toggle_comment_display(false);});
}


function vcp_resize_comments (){
	if(jQuery("#vcp-comments") == null){
		return;
	}
	var h_spacing = 15;
	//var v_spacing = 0;
	var max_height = '1200px';
	vcp_resize_video(false);
	var vcp_comments = jQuery("#vcp-comments").empty();
	var gplus_width = vcp_get_gpcomments_width();

	/*var min_width = 350;
	var stack = false;
	if (gplus_width <= min_width) {
		var vcp = jQuery("#vcp");
		gplus_width = vcp.width();
		stack = true;
	}*/

	var def_src = jQuery("[name='vcp-def-src-link']").val();

	var stack = vcp_stack_check();
	//alert(stack);
	if (stack){
		var vcp = jQuery("#vcp");
		gplus_width = vcp.width();	
	}

	var gplus_scroll = jQuery("#vcp-comments-scroll");
	gplus_scroll.width(gplus_width);
	gplus_scroll.css('height', max_height);
	if (stack == true){
		// mobile version for small screens
		gplus_scroll.css('max-height', max_height);
		vcp_resize_video(true);
		jQuery("#vcp-info").addClass("vcp-v-collapsable");
		jQuery(".vcp-collapse-bar").removeClass("vcp-v-collapsed");
	}
	else{
		// normal desktop version
		jQuery("#vcp-info").removeClass("vcp-v-collapsable").removeClass("vcp-v-collapsed");
		jQuery(".vcp-collapse-bar").addClass("vcp-v-collapsed");
	}
	gplus_width = gplus_width - h_spacing;
	vcp_gplus_comments (gplus_width, 'vcp-comments');
	/*def_src = vcp_vcp_get_gpcomments_width_change(def_src, gplus_width);
	if (def_src != false) {
		var iframe = vcp_comments.find("iframe");
		iframe.attr('src', def_src)
		iframe.attr('style', `position: static; top: 0px; width: `+gplus_width+`px; margin: 0px; border-style: none; left: 0px; visibility: visible; height:`+max_height+`;`)
	}*/
	if (jQuery("#vcp").hasClass("vcp-fullscreen")){
		vcp_info_box_scroll(true);
		vcp_set_comments_height(true);
	}
	else{
		vcp_info_box_scroll(false);
		vcp_set_comments_height(false);
	}
	vcp_size_social_bar(gplus_width);
}

function vcp_stack_check (){
	var gplus_width = vcp_get_gpcomments_width();
	var video_w = jQuery("#vcp-video-size").outerWidth();
	if (video_w <= 350) {
		var vcp = jQuery("#vcp");
		return true;
	}
	return false;
}

function vcp_size_social_bar (in_width){
	var bar = jQuery("#vcp-social-bar");
	var mar_l = parseInt(bar.css("margin-left"));
	var mar_r = parseInt(bar.css("margin-right"));
	var pad_l = parseInt(bar.css("padding-left"));
	var pad_r = parseInt(bar.css("padding-right"));
	bar.width(in_width - (mar_l + mar_r) - (pad_l + pad_r));
}

function vcp_get_gpcomments_width (){
	var main_width_px = jQuery("#vcp").outerWidth();
	//alert('main');
	//alert(main_width_px);
	var video_width_px = jQuery("#vcp-video-size").outerWidth();
	//alert('video');
	//alert(video_width_px);
	var comments_width_px = (main_width_px - video_width_px - 3);
	return comments_width_px;
}

function vcp_vcp_get_gpcomments_width_change (src_url, width){
	if (src_url.indexOf("&width=") == -1) {
		return false;
	}
	parts = src_url.split("&width=")
	prefix = parts[0];
	suffix = parts[1].substring(parts[1].indexOf("&"));
	return prefix + '&width=' + width + suffix;
}

function vcp_resize_video (full) {
	if (full == true){
		jQuery("#vcp-video-size").addClass("vcp-mobile");
	}
	else {
		jQuery("#vcp-video-size").removeClass("vcp-mobile");
	}
}

function vcp_set_comments_height (max) {
	var gplus_scroll = jQuery("#vcp-comments-scroll");
	if (max == false){
		var video_height = jQuery("#vcp-video-size").height() - 2;
		gplus_scroll.css('max-height', video_height.toString() +'px');
	}
	else {
		gplus_scroll.css('max-height', '100%');
	}
}

function vcp_gplus_comments (in_width, parent_id){
		gapi.comments.render(parent_id, {
    	href: window.location,
    	width: in_width,
    	first_party_property: 'BLOGGER',
    	view_type: 'FILTERED_POSTMOD'
	});
}

function vcp_mobile_comments () {
	var h_spacing = 15;
	var v_spacing = 0;
	var min_width = 350;
	var max_height = '1200px';
	//vcp_resize_video(true);
	var vcp_comments = jQuery("#vcp-comments").empty();
	var vcp = jQuery("#vcp");
	gplus_width = vcp.width();
	var stack = false;
	var gplus_scroll = jQuery("#vcp-comments-scroll");
	gplus_scroll.width(gplus_width);
	gplus_scroll.css('max-height', max_height);
	vcp_resize_video(true);
	vcp_gplus_comments (gplus_width - h_spacing, 'vcp-comments');
}

function vcp_collapse_bar_toggle (elem) {
	var parent = elem.parentNode;
	var collapse_div = jQuery("#"+parent.id).find(".vcp-v-collapsable");
	//alert (collapse_div);
	collapse_div.toggleClass("vcp-v-collapsed");
}

function vcp_fullscreen (elem) {
	jQuery("#vcp").toggleClass("vcp-fullscreen");
	vcp_resize_comments ();
	vcp_fullscreen_toggle_icon();
	vcp_options_buttons_margin();
}

function vcp_fullscreen_toggle_icon (){
	var name = "fullscreen";
	if (jQuery("#vcp").hasClass("vcp-fullscreen")){
		name = "partialscreen";
		jQuery("body").addClass("noscroll");
	}
	else{
		jQuery("body").removeClass("noscroll");
	}
	var icon_path = jQuery("span[name='"+name+"']");
	var src = icon_path.attr("src");
	var alt = icon_path.attr("alt");
	var btn = jQuery("#vcp-fullscreen-btn");
	btn.attr("src", src);
	btn.attr("alt", alt);
}

function vcp_refresh_comments (){
	vcp_resize_comments();
}

function vcp_more_menu (){
	alert("as more functions and options are added, this will open a menu to access them.");
}

function vcp_src_link (){
	var src = jQuery("#vcp-comments").find("iframe").attr("src");
	var text = "The link below will allow you to display this exact comments stream anywhere you can put HTML code. (try pasting it directly into the URL to see exactly what it does).";
	prompt (text, vcp_get_src_link());
}

function vcp_get_src_link (){
	return jQuery("#vcp-comments").find("iframe").attr("src");
}

function vcp_src_link_autosave (tries){
	var src = vcp_get_src_link();
	var input = jQuery("#vcp-src-comments-hidden");
	if(typeof src == 'undefined'){
		setTimeout(function(){
			if(--tries) vcp_src_link_autosave(tries);
		},2200);
	}
	else{
		jQuery("#vcp-src-comments-hidden").val(src);
		vcp_save_src_link();
	}
	if (tries <= 1){
		jQuery("#vcp-src-comments-hidden").val("false");
		alert("there may be a problem loading the comments stream from google's server. It is taking much longer to get a reponse than usual.");
	}
}

function vcp_info_box_scroll (fullscreen){
	var vcp_info_box = jQuery("#vcp-info-box");
	var info_box_height = (jQuery(window).height() - jQuery("#vcp-video").outerHeight());
	if (fullscreen == true){
		vcp_info_box.addClass("vcp-scroll");
		vcp_info_box.css('max-height', info_box_height.toString() + 'px');
	}
	else {
		jQuery("#vcp-info-box").removeClass("vcp-scroll");
		vcp_info_box.css('max-height', '');
	}
}

function vcp_options_buttons_margin (){
	var btnsDiv = jQuery("#vcp-option-buttons");
	var mainDiv = jQuery("#vcp-info");
	var margin = 0;
	if (vcp_stack_check()) {
		margin = jQuery("#vcp-info-box").outerWidth() - btnsDiv.outerWidth() - 1;
	}
	else{
		margin = mainDiv.width() - btnsDiv.width() - 18;
	}
	btnsDiv.css("margin-left", margin);
}

function vcp_gen_shortcode() {
	var attr_div = jQuery("#vcp-written-attr-list");
	var attrs = [];
	attr_div.children().each(function (){
		var id = "#" + jQuery(this).attr("label");
		var val = jQuery(id).val();
		//alert(val);
		var info = [jQuery(this).attr("name"), val];
		attrs.push(info);
	});
	var shortcode = `[vcp `;
	attrs.forEach(function (item, index){
		if(item[1] != ''){
			shortcode = shortcode + `${item[0]}="${item[1].replace(/ /g,"~")}" `;
		}
	});

	var shortcode = shortcode +`]`;
	jQuery("#shortcode-box").html(shortcode);
}

function vcp_move_comments() {
	var comments = jQuery("#vcp-comments-scroll");
	var vcp = jQuery("#vcp");
	comments.appendTo(vcp);
}

function vcp_match_iframe (){
	var iframe = jQuery("#vcp-info-box-frame");
	var doc_body = iframe.contents().find("html");
	iframe.height(doc_body.outerHeight(true));
}

function vcp_match_header (iframe_id){
	var doc = document.getElementById("vcp-info-box-frame").contentWindow.document;
	var head = doc.getElementsByTagName('head')[0];
	var main_head = document.head;
	head.innerHTML = main_head.innerHTML;

	var iframe = jQuery("#"+iframe_id);
	var html = iframe.contents().find("html");
	html.attr('style','margin-top:0px !important');
	var content_height = html.outerHeight();
	jQuery("#vcp-info-box-frame").style("height", content_height);
	alert(content_height);
}

function vcp_iframe_body_styles (iframe_id, in_styles){
	var iframe = jQuery("#"+iframe_id);
	var body = iframe.contents().find("body");
	var style = "";
	in_styles.forEach(function (item, index){
		style += style+item+"; ";
	});
	body.attr('style', style);
}

function vcp_color_preview_set (){
	var hex = jQuery("#vcp-color-id").val();
	jQuery("#vcp-color-preview").css("background-color", hex);
}

function vcp_comment_preview_change(){
	var val = jQuery("#vcp-src-links-dd").val();
	var src_url = jQuery("#vcp-src-links-dd").find("option:selected").text();
	var iframe = jQuery("#vcp-src-comments-preview");
	iframe.attr("src", val);
	var span_src = jQuery("#vcp-src-link-src-box");
	var span_url = jQuery("#vcp-src-link-url-box");
	span_src.html(val);
	span_url.html(src_url);
}

function vcp_save_src_link (){
	var src = vcp_get_src_link();
	var post_id = jQuery("#vcp-post-id").val();
	var data = {
	    action: 'vcp_save_src',
		vcp_src_link: [post_id, src, window.location.href],
	    success: function(){
	    	var btn = jQuery("#vcp-comments_src-btn");
	    	if (typeof btn != 'undefined'){
	    		btn.attr("style", "float: right; background-color: rgb(100,255,80) !important; border-color: rgb(50, 120, 40);");
	    		//btn.css("background-color", "rgb(100,255,80)");
	    		//btn.css("border-color", "rgb(50,120,40)");
	    	}
	    }
	};
	jQuery.post(vcp_ajaxurl, data, function(){});
}

function vcp_save_def_src_link(){
	var src = vcp_get_src_link();
	var post_id = jQuery("#vcp-post-id").val();
		var data = {
	    action: 'vcp_save_def_src',
		vcp_src_link: [post_id, src, window.location.href],
	    success: function(){alert('saved')}
	};
	jQuery.post(vcp_ajaxurl, data, function(){});
}


function vcp_focus_on (id){
	var cover = jQuery("#"+id);
	cover.removeClass("vcp-v-collapsed");

	vcp_resize_cover();
	jQuery("body").addClass("noscroll");
}

function vcp_focus_off (id){
	var cover = jQuery("#"+id);
	cover.addClass("vcp-v-collapsed");
	jQuery("body").removeClass("noscroll");
}

function vcp_resize_cover(){
	jQuery(".vcp-fixed-c").each(function(){
		var elem = jQuery(this);
		elem.css("margin-left", String(-1*(elem.width()/2))+'px' );
		elem.css("margin-top", String(-1*(elem.height()/2))+'px' );
	});
	jQuery(".vcp-fixed-h").each(function(){
		var elem = jQuery(this);
		elem.css("margin-left", String(-1*(elem.width()/2))+'px' );
		//elem.css("margin-top", String(-1*(elem.height()/2))+'px' );
	});
	jQuery(".vcp-fixed-v").each(function(){
		var elem = jQuery(this);
		//elem.css("margin-left", String(-1*(elem.width()/2))+'px' );
		elem.css("margin-top", String(-1*(elem.height()/2))+'px' );
	});
}

jQuery(window).load(vcp_resize_cover());
window.addEventListener('resize', vcp_resize_cover);

function vcp_toggle_comment_display(def){
	var h = 635;
	var w = 800;

	var frame = jQuery("#vcp-comments-display");
	frame.removeClass("vcp-fullscreen");
	var gp_comment_inputs = jQuery("#vcp-comment-setup-left");
	var gp_comment_stream = jQuery("#vcp-comments-setup-stream-div");
	var gp_comment_frame = jQuery("#vcp-comments-setup-stream"); //iframe inside gp_comment_stream (div)
	var setup = jQuery("#vcp-comment-setup");
	var view = jQuery("#vcp-comment-view");
	if(setup.hasClass("vcp-v-collapsed") || def == true){
		// opening setup
		frame.css("height", String(h)+"px");
		frame.css("width",  String(w)+"px");
		gp_comment_inputs.css("height", String(h)+"px");
		gp_comment_inputs.css("width", String((w/2) + 10)+"px");
		gp_comment_stream.css("height", String(h-5)+"px");
		gp_comment_stream.css("width", String((w/2) - 30)+"px");
		gp_comment_frame.css("width", String((w/2) - 0)+"px");
		setup.removeClass("vcp-v-collapsed");
		view.addClass("vcp-v-collapsed");
		frame.css('background-color', 'white');
		frame.css('background-image', ``);
	}
	else{
		// opening view
		vcp_populate_comment_display();
		frame.css("height", "75%");
		frame.css("width", "75%");
		view.removeClass("vcp-v-collapsed");
		setup.addClass("vcp-v-collapsed");
		vcp_v_align_comment();
	}
	var iframe = jQuery("#vcp-comments-setup-stream");
	iframe.width((w/2) - 30)
	iframe.attr("src", vcp_vcp_get_gpcomments_width_change(vcp_get_src_link(), ((w/2) - 46)));
	vcp_resize_cover();
}

function vcp_refresh_gpcomments_commentsdisplay(){
	var w = 800;
	//var def_src = jQuery("[name='vcp-def-src-link']").val();
	var iframe = jQuery("#vcp-comments-setup-stream");
	iframe.attr("src", vcp_vcp_get_gpcomments_width_change(vcp_get_src_link(), ((w/2) - 46)));
}

function vcp_populate_comment_display(){
	var view = jQuery("#vcp-comment-view");
	var form = jQuery("#vcp-comment-input-"+vcp_comment_get_index());
	var frame = jQuery("#vcp-comments-display");

	var title_o = view.find("h1");
	var message_o = view.find("p");
	var posted_o = view.find("span");

	var title_i = form.find(`[name="vcp-gp-title"]`);
	var message_i = form.find(`[name="vcp-gp-content"]`);
	var posted_i = form.find(`[name="vcp-gp-name"]`);

	if(title_i.val() == ""){title_o.css("display", "none");}
	else{title_o.css("display", "block");}
	if(message_i.val() == ""){message_o.parent().css("display", "none");}
	else{message_o.parent().css("display", "block");}
	if(posted_i.val() == ""){posted_o.parent().css("display", "none");}
	else{posted_o.parent().css("display", "block");}

	title_o.html(title_i.val());
	message_o.html(message_i.val());
	posted_o.html("posted by : "+posted_i.val());

	var color = form.find(`[name="vcp-gp-font-color"]`).val();
	var font = form.find(`[name="vcp-gp-style"]`).val();
	var size = form.find(`[name="vcp-gp-size"]`).val();
	title_o.css('font-family', font);
	message_o.css('font-family', font);
	posted_o.css('font-family', font);
	title_o.css('color', color);
	message_o.css('color', color);
	posted_o.css('color', color);
	title_o.css('font-size', (size*0.5).toString()+"%");
	message_o.css('font-size', size.toString()+"%");
	posted_o.css('font-size', (size*0.5).toString()+"%");
	var background = form.find(`[name="vcp-gp-bk"]`).val();
	if(background.substring(0,1) == '-'){
		background = background.substring(1,background.length);
		if (!vcp_isValidHex(background)) {
			frame.css('background-image', `URL(`+background+`)`);
		}
		else{
			background = background.replace("#", "")
			background = "#"+background;
			frame.css('background-image', ``);
			frame.css('background-color', background);
		}
	}
	else{
		frame.css('background-image', ``);
		frame.css('background-color', background);
	}
}

function vcp_isValidHex(hex) { 
	hex = hex.replace("#","");
	return /^[0-9A-F]{6}$/i.test(hex);
}

function vcp_comment_add_display(){
	var input_frame = jQuery("#vcp-comment-input");
	var c = parseInt(input_frame.find(".vcp-count").length);
	var btn_frame = jQuery("#vcp-comment-display-index-btns");
	if (c >= 10){
		alert("maximum of 10");
		return;
	}
	//get default values
	var form = jQuery("#vcp-comment-input-"+vcp_comment_get_index());
	var title_i = form.find(`[name="vcp-gp-title"]`).val();
	var style_i = form.find(`[name="vcp-gp-style"]`).val();
	var font_color_i = form.find(`[name="vcp-gp-font-color"]`).val();
	var bkg_i = form.find(`[name="vcp-gp-bk"]`).val();

	input_frame.children().each(function(i, val){
		jQuery(val).addClass("vcp-v-collapsed");
	});

	var frame = jQuery("#vcp-comments-display");
	var input_html = jQuery("#vcp-comment-input-1");
	input_html = input_html.clone().appendTo(input_frame);
	input_html.attr("id", "vcp-comment-input-"+String(c+1)+"");

	var btn_html = jQuery("#vcp-comment-display-toggle-1");
	btn_html = btn_html.clone().appendTo(btn_frame);
	btn_html.attr("id", "vcp-comment-display-toggle-"+String(c+1)+"");
	btn_html.html(String(c+1));

	vcp_comment_set_index((c+1));
	jQuery("#vcp-comment-display-toggle-"+parseInt(c+1)).click(function(){vcp_comment_set_index(this);});
	var form = jQuery("#vcp-comment-input-"+vcp_comment_get_index());
	var view = jQuery("#vcp-comment-view");
	form.find(`[name="vcp-gp-title"]`).val("");
	form.find(`[name="vcp-gp-content"]`).val("");
	form.find(`[name="vcp-gp-name"]`).val("");
	form.find(`[name="vcp-gp-style"]`).val(style_i);
	form.find(`[name="vcp-gp-font-color"]`).val(font_color_i);
	form.find(`[name="vcp-gp-bk"]`).val(bkg_i);
}

function vcp_comment_remove_display(){
	if(vcp_comment_setup_count() <= 1){
		alert("only 1 left");
		return;
	}
	var input_frame = jQuery("#vcp-comment-input");
	var btn_frame = jQuery("#vcp-comment-display-index-btns");
	var index = vcp_comment_get_index();
	var cur_input = jQuery("#vcp-comment-input-"+parseInt(index));
	var cur_btn = btn_frame.find("#vcp-comment-display-toggle-"+parseInt(index));

	cur_input.remove();
	cur_btn.remove();

	btn_frame.children().each(function(i, val){
		//alert(val);
		jQuery(val).text(i+1);
		jQuery(val).attr("id","vcp-comment-display-toggle-"+(i+1));
	});
	input_frame.children().each(function(i, val){
		jQuery(val).attr("id","vcp-comment-input-"+(i+1));
	});
	index = Math.min(index, vcp_comment_setup_count());
	vcp_comment_set_index(index);
}

function vcp_comment_setup_count(){
	var input_frame = jQuery("#vcp-comment-setup");
	return input_frame.find(".vcp-count").length;
}

function vcp_comment_get_index(){
	var btn_frame = jQuery("#vcp-comment-display-index-btns");
	var btn = btn_frame.find("button[selected]");
	return parseInt(btn.html());
}

function vcp_comment_set_index(index){
	if(index !== parseInt(index, 10)){
		index = parseInt(jQuery(index).text());
	}

	var input_frame = jQuery("#vcp-comment-input");
	var btn_frame = jQuery("#vcp-comment-display-index-btns");
	var btn = btn_frame.find("button[selected]");
	btn.removeAttr("selected");
	btn.attr("style", 'width:30px; margin:0 3px;');
	input_frame.children().each(function(i, val){
		jQuery(val).addClass("vcp-v-collapsed");
	});

	btn = btn_frame.find("#vcp-comment-display-toggle-"+parseInt(index));
	btn.attr("selected", " ");
	btn.attr("style", 'width:30px; margin:0 3px; background-color:rgb(221, 245,221) !important');
	jQuery("#vcp-comment-input-"+parseInt(index)).removeClass("vcp-v-collapsed");
}

function vcp_comment_next(){
	vcp_comment_iter(true);
}

function vcp_comment_previous(){
	vcp_comment_iter(false);
}

function vcp_comment_iter(next){
	var index = vcp_comment_get_index();
	if(next == true){
		if(index < 10 && index < vcp_comment_setup_count()){
			index++;
		}
		else{alert("last : " + String(index));}
	}
	else{
		if(index > 1){
			index--;
		}
		else{alert("first : " + String(index));}
	}
	vcp_comment_set_index(index);
	vcp_populate_comment_display();
	vcp_v_align_comment();
}

function vcp_fullscreen_comment_display(){
	var comment_display_div = jQuery("#vcp-comments-display");
	comment_display_div.toggleClass("vcp-fullscreen");
	vcp_v_align_comment();
}

function vcp_v_align_comment(){
	var margin = jQuery("#vcp-comments-display").outerHeight();
	var max_height = margin - jQuery("#vcp-comment-view-btns").outerHeight() - 12;
	var view = jQuery("#vcp-comment-view");
	view.css("max-height", margin);
	margin = (margin - view.outerHeight())/2 - 10;
	if(margin < 15){
		margin = 15;
	}
	view.css("margin-top", margin);
	view.css("max-height", max_height);
}

function vcp_revert_Link(){
	var video_field = jQuery("#video-link");
	var link = jQuery("#vcp-saved-video-link").val();
	video_field.val(link);
	video_field.text(link);
	vcp_video_preview_update();
}

function vcp_revert_tutorial(){
	var video_field = jQuery("#video-link");
	var tutorial_video = 'https://www.youtube.com/embed/sRTFpW4SM5U'
	video_field.val(tutorial_video);
	video_field.text(tutorial_video);
	vcp_video_preview_update();
}

function vcp_video_preview_update(){
	var iframe = jQuery("#vcp-video-preview");
	var link_elem = jQuery("#video-link");
	var link = link_elem.val();
	if (link_elem.val().indexOf('iframe') !== -1) {
		var parts = link_elem.val().split('src="');
		var cropped = parts[1].split('"')[0];
		link = cropped;
	}
	else if(link_elem.val().indexOf('https://youtu.be/') !== -1){
		fixed = link_elem.val().replace('https://youtu.be/', 'https://www.youtube.com/embed/');
		link = fixed;
	}
	link = link.replace(/\b(%)\b/gi, '~');
	if(link.indexOf('youtube.com') !== -1){
		link = link.replace('watch?v=', 'embed/');
	}
	//alert(link);
	link_elem.val(link);
	//alert(link);
	iframe.attr('src', link.replace(/\b(~)\b/gi, '%'));
	//alert('done');
}