<?php
//namespace vcpplugin;

function vcp_shortcode($atts){
	$vcp = new VCP_shortcode($atts);
    return $vcp->video_comments();
}

add_shortcode($pre.'vcp', 'vcp_shortcode');

class VCP_shortcode extends vcp_options
{
	protected $atts;
	protected $id;
	protected $meta;
	protected $video_link;
	protected $title;
	protected $default_video = 'https://www.youtube.com/embed/sRTFpW4SM5U';
	public $is_mobile;
	protected $loaded_api;


	public function __construct($atts = array()) {
        $this->gen_options();
		$this->atts = $atts;
		$this->clean_atts();
		$this->id = $this->atts['id'];
		$this->meta = get_post_meta($this->id);
		$this->video_link = $this->embed_link_fix();
		$this->title = get_post($this->id)->post_title;
		$this->embed_link_fix();
		$this->loaded_api = false;
	}

	public function get_apis (){
		if ($this->loaded_api){
			return '';
		}
		else{
			$this->loaded_api = true;
			return '<script src="https://apis.google.com/js/plusone.js"></script>';
		}
	}

	protected function clean_atts (){
		$code_atts = array();
		for ($i=0; $i < count($this->options); $i++) {
			$cur = $this->options[$i];
			$code_atts[$cur->get_name()] = $cur->get_def_value();
		}
		$this->atts = shortcode_atts($code_atts, $this->atts);
		$this->is_mobile = wp_is_mobile();
	}

	protected function embed_link_fix (){
		$link = $this->meta['video_link'][0];
		if (strpos($link, 'youtube.com')) {
			$link = str_replace('watch?v=', 'embed/', $link);
		}
		$link = str_replace('~', '%', $link);

		return $link;
		
		$link = $this->meta['video_link'][0];
		if (strpos($link, 'www.youtube.com') !== FALSE){
			$link = str_replace('watch?v=', 'embed/', $this->meta['video_link'][0]);
			$link = preg_split('&t', $link)[0];
		}
		
		return $link;
	}

	protected function get_collapse_bar_HTML($is_bottom = false){
		$bar_class = ($this->is_mobile) ? ' vcp-mobile ' : '' ;
		$is_bottom = ($is_bottom) ? ' vcp-bottom ' : '' ;

		$bar = '<div class="vcp-collapse-bar'.$is_bottom.$bar_class.'" onclick="vcp_collapse_bar_toggle(this)">';
		$bar .= '</div>';
		return $bar;
	}

	public function get_video_html (){
		$mobile_class = ($this->is_mobile) ? 'class="vcp-v-collapsable"' : '';

		$video_link = '<div id="vcp-video" class="cf">';
		$video_link .= '<iframe src="';
		$video_link .= $this->video_link;
		$video_link .= '" frameborder="0" allowfullscreen></iframe>';
		$video_link .= '</div>';
		return $video_link;
	}

	protected function get_info_box_html() {
		$bk_color = $this->meta['vcp_color'];
		if (is_array($bk_color)){
			$bk_color = implode("", $bk_color);
		}
		$bk_color = str_replace('#', '', $bk_color);
		if (ctype_xdigit($bk_color)) {$bk_color = '#'.$bk_color;} 
		else {$bk_color = '#f0f0f0';}
		$mobile_class = ($this->is_mobile) ? 'class="vcp-v-collapsable"' : '';
		$info_box = '<div id="vcp-info-box">';
		$info_box .= $this->get_collapse_bar_HTML();
		$info_box .= '<div id="vcp-info" '.$mobile_class.' style="background-color:'.$bk_color.'">';
		$info_box .= '<div style="width:100%; overflow:hidden;" class="cf">';
			$info_box .= $this->get_option_buttons_html();// draw last and absolute location? -- no
			$info_box .= $this->get_infobox_content();
		$info_box .= '</div>';
		$info_box .= '</div>';
		$info_box .= '</div>';
		return $info_box;
	}

	protected function get_infobox_content(){
		$info_title = '<h1 style="margin:4px;">'.$this->title.'</h1>';
		$id_content = $this->meta['vcp_info_content'][0];
		$info_content = get_post_meta($id_content, 'vcp_info');
		$info_content = apply_filters('the_content', $info_content[0]);
		$inherit_header = ($this->meta['vcp_theme_css'][0] == true) ? 'vcp_match_header("vcp-info-box-frame");' : '' ;
		return $info_title.$info_content;
	}

	protected function get_option_buttons_html (){
		$btns_html = "<div id=\"vcp-option-buttons\" class=\"cf\">";
		$btn_classes = 'class="vcp-button vcp-light-gray vcp-btn-sm"';
		$btn_styles = 'style="float:right;"';
		$fullscreen_spans = array(
			'<span name="fullscreen" '.vcp_get_svg_img_src('Fullscreen').'></span>',
			'<span name="partialscreen" '.vcp_get_svg_img_src('Partialscreen').'></span>'
		);
		$btns = array(
			array('title'=>'Refresh', 'desc'=>'title="Refresh comments section"')
		);
		if(((string)$this->meta['vcp_fullscreen_comment_display'][0] == "1")){
			$btns[] = array('title'=>'Fullscreen', 'desc'=>'title="Fullscreen video and comments."');
		}
		/*if (current_user_can( 'publish_posts' )){
			$btns[] = array('title'=>'Comments_Src', 'desc'=>'title="get the source link for this comment stream"');
		}*/
		if(current_user_can( 'publish_posts' ) || ((string)$this->meta['vcp_public_comment_display'][0] == "1")){
			$btns[] = array('title'=>'Comment_Display', 'desc'=>'title="A nice display for sharing comments."');
		}

		for ($i=0; $i < count($btns); $i++) { 
			$icon = vcp_get_svg_img_src($btns[$i]['title']);
			$id = ' id="vcp-'.strtolower($btns[$i]['title']).'-btn" ';
			$btns_html .= '<img '.$btns[$i]['desc'].' '.$icon.$id.$btn_classes.$btn_styles.'>';
			$btns_html .= $btns[$i]['content'];
			$btns_html .= '</img>';
		}
		$btns_html .= '<input name="vcp-src-comments-hidden" id="vcp-src-comments-hidden" type="hidden" value=""></input>';
		$btns_html .= '<input name="vcp-post-id" id="vcp-post-id" type="hidden" value="'.$this->id.'"></input>';
		if(current_user_can( 'publish_posts' ) || ((string)$this->meta['vcp_public_comment_display'][0] == "1")){
			$btns_html .= get_vcp_comment_display();
		}
		$btns_html .= '<script>jQuery(window).load(vcp_btn_clickers);</script>';
		$btns_html .= '</div>';
		$btns_html .= '<script>vcp_options_buttons_margin();</script>';
		return $btns_html;
	}


	protected function get_video() {
		$mobile_class = ($this->is_mobile) ? 'class="vcp-mobile"' : '';

		$video = array();
		$video[] = '<div id="vcp-video-size" '.$mobile_class.' style="width:'.$this->meta['vcp_split'][0].'%;">';
			$video[] = $this->get_video_html();
			$video[] = $this->get_info_box_html();
		$video[] = '</div>';

		return $video;
	}

	protected function get_gplus_comments() {
		$gp_comments = array();
		$vcp_comments_classes = (!$this->is_mobile) ? '' : 'class="vcp-mobile"';
		$source = $this->check_existing_source();
		$vcp_comments_function = "jQuery(window).load(vcp_resize_comments);"."window.addEventListener('resize', vcp_resize_comments);";
		if ($this->is_mobile){
			$vcp_comments_function = "vcp_mobile_comments();";
		}
		$gp_comments[] = '<div id="vcp-comments-scroll">';
			$gp_comments[] = '<div id="vcp-social-bar">';
				$gp_comments[] = $this->get_social_bar_html();
			$gp_comments[] = '</div>';
			$gp_comments[] = '<input type="hidden" name="vcp-def-src-link" value="'.$source.'">';
			$gp_comments[] = "<div id=\"vcp-comments\" $vcp_comments_classes>";
				$gp_comments[] = '<script>';
				$gp_comments[] = $vcp_comments_function;
				$gp_comments[] = '</script>';
			$gp_comments[] = '</div>';
		$gp_comments[] = '</div>';

		return $gp_comments;
	}

	protected function check_existing_source(){
		$manual_src = $this->meta['manual_src_link'][0];
		if (is_string($manual_src)) {
			if ($manual_src == '') {
				$manual_src = false;
			}
		}
		else{
			$manual_src = false;
		}
		$source = $this->meta['vcp_comment_source_link'][0];
		if ($manual_src != false) {
			$source = $manual_src;
		}

		if (is_string($source)) {
			if ($source != '') {
				return $source;
			}
		}
		return false;
	}

	protected function get_social_bar_html (){
		$bar = '<div style="width:70px; height:70px; margin-top:-6px; padding-top:0px; overflow:hidden;">';
			$bar .= '<div class="g-plusone" data-size="tall" data-annotation="none" style="float:left;"></div>';
			$bar .= '<div class="g-plus" data-action="share" data-annotation="none" style="float:left;"></div>';
		$bar .= '</div>';
		return $bar;
	}

	public function video_comments(){
		$final = array();
		$final[] = '<div id="vcp" class="cf">';
		$final[] = "<style>@import url('".get_option(VCP_globals::vcp_wpset_gfonts)."');</style>";
		$final[] = $this->get_apis();
		$final[] = implode("", $this->get_video());
		$final[] = implode("", $this->get_gplus_comments());
		$final[] = '</div>';
		if (current_user_can( 'publish_posts' )){
			$final[] = '<script>jQuery(document).ready(vcp_src_link_autosave(7));</script>';
		}
		return implode("", $final);
	}
}

?>