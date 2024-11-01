<?php
//namespace vcpplugin;

function kjp_get_svg_img_src ($icon_name){
	$svg = vcp_get_icon($icon_name.'.svg');
	$png = vcp_get_icon($icon_name.'.png');
	return "src=\"$svg\" alt=\"$png\"";
}

class kjp_custom_post {

	protected $singular = 'kjp_custom_post';	
	protected $plural = 'kjp_custom_posts';	
	protected $labels = array();
	protected $args = array();
	protected $rewrite = array( 
	        	'slug' => 'set me from name',
	        	'with_front' => true,
	        	'pages' => false,
	        	'feeds' => true,
	    );
	protected $supports = array( 
	        	'title', 
	        	'editor', 
	        	'author', 
	        	'custom-fields' 
	    );
	protected $meta_boxes = array();

	function setup (){
		//========= OVERRIDE ME!!! =============
	}

	public function __construct (){
		$this->setup();
		$this->rewrite['slug'] = ($this->rewrite['slug'] == 'set me from name') ? $this->get_slug() : $this->rewrite['slug'] ;
		$this->set_labels($this->labels);
		$this->set_args($this->args);
        add_action('init', array($this, 'kjp_register_post_type'));
	}

	public function kjp_register_post_type(){
        register_post_type( $this->singular, $this->args);
	}

	public function set_names ($name){
		if (is_array($name)){
			$this->singular = $name[0];
			$this->plural = $name[1];
		}
		else{
			$this->singular = $name;
			$this->plural = $name.'s';
		}
	}

	public function get_names ($both = false){
		return ($both == true) ? array($this->singular, $this->plural) : $this->singular ;
	}

	public function set_labels ($labels){
		foreach ($labels as $key => $value) {
			$this->labels[$key] = $value;
		}
		$def_labels = $this->get_default_labels();
		foreach ($def_labels as $key => $value) {
			if(!isset($this->labels[$key])){
				$this->labels[$key] = $value;
			}
		}
	}

	public function set_args ($args){
		foreach ($args as $key => $value) {
			$this->args[$key] = $value;
		}
		$def_args = $this->get_default_args();
		foreach ($def_args as $key => $value) {
			if(!isset($this->args[$key])){
				$this->args[$key] = $value;
			}
		}
	}



	public function get_default_labels (){
		return array(
			'name'               => __($this->plural),
			'singular_name'      => __($this->singular),
			'menu_name'          => __($this->plural),
			'name_admin_bar'     => __($this->singular),
			'add_new'            => __('Add New'),
			'add_new_item'       => __('Add New '.$this->singular),
			'new_item'           => __('New '.$this->singular),
			'edit_item'          => __('Edit '.$this->singular),
			'view_item'          => __('View '.$this->singular),
			'all_items'          => __('All '.$this->plural),
			'search_items'       => __('Search '.$this->plural),
			'parent'			 => __('Parent '.$this->singular),
			'not_found'          => __('No '.$this->plural.' found'),
			'not_found_in_trash' => __('No '.$this->plural.' in trash')
		);
	}
	public function get_default_args(){
		return array(
			'labels' => $this->labels,
			'public'              => true,
	        'publicly_queryable'  => true,
	        'exclude_from_search' => false,
	        'show_in_nav_menus'   => true,
	        'show_ui'             => true,
	        'show_in_menu'        => true,
	        'show_in_admin_bar'   => true,
	        'menu_position'       => 2,
	        'menu_icon'           => 'dashicons-menu',//https://developer.wordpress.org/resource/dashicons/
	        'can_export'          => true,
	        'delete_with_user'    => false,
	        'hierarchical'        => false,
	        'has_archive'         => true,
	        'query_var'           => true,
	        'capability_type'     => 'post',
	        'map_meta_cap'        => true,
	        // 'capabilities' => array(),
	        'rewrite'             => $this->rewrite,
	        'supports'            => $this->supports
		);
	}

	public function get_slug(){
		return str_replace(' ', '_', $this->singular);
	}
}

class kjp_custom_metabox{

	protected $post_type;
	protected $name;
	protected $label;
	protected $stored_meta;
	protected $input_args;
	protected $js_on_resize = '';


	function setup_render_HTML ($post){
		$this->set_nonce();
		$this->stored_meta = get_post_meta($post->ID);
		$this->render_HTML($post);
	}
	public function render_HTML ($post){
		// ==================== OVERRIDE ME ====================
	}

	public function setup(){
		// =========== OPTIONAL OVERRIDE FOR MORE CONTROL OF VARIABLES ===============
		// use add_input_field() here to add all field args
	}

	public function __construct ($args = array()){
		//echo '<script>function on_resize(c,t){onresize=function(){clearTimeout(t);t=setTimeout(c,100)};return c};</script>';
		$args = $this->clean_args($args);
		$this->setup();
        add_action('add_meta_boxes', array($this, 'kjp_add_custom_metabox'));
        add_action('save_post', array($this, 'kjp_save_custom_metabox'));
	}
	function clean_args($args = array()){
		$this->post_type = (isset($args['post_type'])) ? $args['post_type'] : array();
		$this->name = (isset($args['name'])) ? $args['name'] : 'kjp_metabox';
		$this->name = str_replace(' ', '-', $this->name);
		$this->label = (isset($args['label'])) ? $args['label'] : 'kjp_metabox_label';
		return $args;
	}

	public function add_input_field ($args){
		$name = str_replace(' ', '-', $args['name']);
		$name = str_replace('_', '-', $name);
		$args['meta'] = str_replace('-','_', $name);
		$args['sanitize'] = (isset($args['sanitize'])) ? $args['sanitize'] : true ;
		$this->input_args[$name] = $args;
	}

	public function kjp_add_custom_metabox(){
		if (count($this->post_type) == 0){
			echo ('<script>alert("warning: no post type set for kjp_metabox");</script>');
		}

		add_meta_box(
			$this->name, // metabox name
			__($this->label), // headline
			array($this, 'setup_render_HTML'),// callback function for HTML code
			$this->post_type,
			'advanced',// defualt value
			'high'// priority
		);
	}

	public function kjp_save_custom_metabox( $post_id ) {
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );

		$nonce = $this->get_nonce_name();
		$is_valid_nonce = (isset( $_POST[$nonce]) && wp_verify_nonce($_POST[$nonce], basename(__FILE__))) ? 'true':'false';

		if($is_autosave || $is_revision || !$is_valid_nonce){
			return;
		}

		if(!isset($this->input_args) || count($this->input_args) < 1){
			return;
		}

		foreach ($this->input_args as $key => $args) {
			$meta_key = $args['meta'];
			$name = str_replace('_','-',$meta_key);
			if (isset($_POST[$name])){
				$value = ($args['sanitize'] == true) ? sanitize_text_field($_POST[$name]) : $_POST[$name] ;
				update_post_meta( $post_id, $meta_key, $value);
			}
			elseif (isset($_POST[str_replace('-','_',$name)])) {
				$name = str_replace('-','_',$meta_key);
				$value = ($args['sanitize'] == true) ? sanitize_text_field($_POST[$name]) : $_POST[$name] ;
				update_post_meta( $post_id, $meta_key, $value);
			}
		}
		//update_post_meta( $post_id, 'vcp_test_meta', sanitize_text_field( count($this->input_args) ));
	}

	protected function set_nonce (){
		wp_nonce_field(basename(__FILE__), $this->get_nonce_name());
	}
	function get_nonce_name (){
		return $this->name.'_nonce';
	}

	public function nice_text_field($name, $info_class = ''){
		$name = str_replace(' ', '-', $name);
		$name = str_replace('_', '-', $name);
		$args = $this->input_args[$name];
		$label = (isset($args['label'])) ? $args['label'] : str_replace('-', ' ', $name);
		$value = (isset($args['value'])) ? $args['value'] : '' ;
		$placeholder = (isset($args['placeholder'])) ? 'placeholder="'.$args['placeholder'].'"' : '' ;
		$icon = (isset($args['icon'])) ? $args['icon'] : '' ;
		$id = (isset($args['id'])) ? $args['id'] : $name;
		$styles = (isset($args['styles'])) ? $args['styles'] : '' ;
		$on_change = (isset($args['on_change'])) ? ' onchange="'.$args['on_change'].'"' : '' ;
		$meta = $this->stored_meta[str_replace('-', '_', $name)]; //replace spaces with underscores
		$value = (! empty ($meta)) ? 'value="'.esc_attr($meta[0]).'"' : 'value="'.$value.'"' ;
		?>
		<div id="<?php echo $id;?>-div" class="kjp-nice-field" style="<?php echo $styles;?>" >
			<img <?php echo kjp_get_svg_img_src($icon); ?> >
				<input style="width:80%;" <?php foreach (array('name="'.$name.'"', 'id="'.$id.'"', $value, $on_change, $placeholder) as $key => $attr) {echo ' '.$attr.'';}?> type="text">
				<button type="button" class="kjp-invis-buton <?php echo $info_class; ?>" id="<?php echo $name; ?>-info" style="float:right; line-height: 12px; font-size: 32px; font-weight: 600; color:#00a0d2; width:32px; margin: 0px !important;">?</button>
			</input>
			<script type="text/javascript">
				jQuery(window).load(function(){
					var input_field = jQuery("#<?php echo $id; ?>");
					var div = jQuery("#<?php echo $id; ?>-div");
					input_field.width(div.width() - 78);
				});
			</script>
		</div>
		<?php
		$this->add_js_sizing_field($id);
	}

	public function nice_number_field($name, $info_class = ''){
		$name = str_replace(' ', '-', $name);
		$name = str_replace('_', '-', $name);
		$args = $this->input_args[$name];
		$label = (isset($args['label'])) ? $args['label'] : str_replace('-', ' ', $name);
		$value = (isset($args['value'])) ? $args['value'] : '' ;
		$placeholder = (isset($args['placeholder'])) ? 'placeholder="'.$args['placeholder'].'"' : '' ;
		$icon = (isset($args['icon'])) ? $args['icon'] : '' ;
		$id = (isset($args['id'])) ? $args['id'] : $name;
		$styles = (isset($args['styles'])) ? $args['styles'] : '' ;
		$on_change = (isset($args['on_change'])) ? ' onchange="'.$args['on_change'].'"' : '' ;
		$meta = $this->stored_meta[str_replace('-', '_', $name)]; //replace spaces with underscores
		$value = (! empty ($meta)) ? 'value="'.esc_attr($meta[0]).'"' : 'value="'.$value.'"' ;
		?>
		<div id="<?php echo $id;?>-div" class="kjp-nice-field" style="<?php echo $styles;?>" >
			<img <?php echo kjp_get_svg_img_src($icon); ?> >
				<input style="width:80%;" <?php foreach (array('name="'.$name.'"', 'id="'.$id.'"', $value, $on_change, $placeholder) as $key => $attr) {echo ' '.$attr.'';}?> type="number">
				<button type="button" class="kjp-invis-buton <?php echo $info_class; ?>" id="<?php echo $name; ?>-info" style="float:right; line-height: 12px; font-size: 32px; font-weight: 600; color:#00a0d2; width:32px; margin: 0px !important;">?</button>
			</input>
			<script type="text/javascript">
				jQuery(window).load(function(){
					var input_field = jQuery("#<?php echo $id; ?>");
					var div = jQuery("#<?php echo $id; ?>-div");
					input_field.width(div.width() - 78);
				});
			</script>
		</div>
		<?php
		$this->add_js_sizing_field($id);
	}

	public function nice_dropdown($name, $info_class = '', $values = array('default'), $labels = array('default')){
		$name = str_replace(' ', '-', $name);
		$name = str_replace('_', '-', $name);
		$args = $this->input_args[$name];
		$title = $args['title'];
		$label = (isset($args['label'])) ? $args['label'] : str_replace('-', ' ', $name);
		$value = (isset($args['value'])) ? $args['value'] : '' ;
		$icon = (isset($args['icon'])) ? $args['icon'] : '' ;
		$info = (isset($args['info'])) ? $args['info'] : '' ;
		$values = (isset($args['values'])) ? $args['values'] : $values ;
		$labels = (isset($args['labels'])) ? $args['labels'] : $labels ;
		$id = (isset($args['id'])) ? $args['id'] : $name;
		$styles = (isset($args['styles'])) ? $args['styles'] : '' ;
		$on_change = (isset($args['on_change'])) ? 'onchange="'.$args['on_change'].'"' : '' ;
		$meta = $this->stored_meta[str_replace('-', '_', $name)]; //replace spaces with underscores
		$value = (! empty ($meta)) ? 'value="'.esc_attr($meta[0]).'"' : 'value="'.$value.'"' ;
		?>
		<div id="<?php echo $id;?>-dropdown" class="kjp-nice-field" style="<?php echo $styles;?>" >
			<div style="width:50%; float:left; overflow:hidden; height:100%;">
				<img <?php echo kjp_get_svg_img_src($icon); ?> >
				<h2 style="padding:0px !important; float:left; color:#646464; line-height:34px; width:30%; white-space:nowrap; overflow:hidden;" ><?php echo $title; ?></h2>
				<button type="button" class="kjp-invis-buton <?php echo $info.' '.$info_class; ?>" id="<?php echo $name; ?>-info" style="float:right; line-height: 12px; font-size: 32px; font-weight: 600; color:#00a0d2; width:32px; margin: 0px !important; height:100%;">?</button>
			</div>
			<?php 
			$stored_value = (! empty ($meta)) ? esc_attr($meta[0]) : $values[0] ;
			$dropdown_styles = ' style="width:50% !important; float:right; height:100%; margin:0px; padding:0px; border-radius:4px;"';
			$basic_dropdown .= '<select name="'.$name.'" id="'.$id.'" '.$on_change.'>';
			for ($i=0; $i < count($values); $i++) { 
				$sel = ($stored_value == $values[$i]) ? 'selected' : '' ;
				$basic_dropdown .= '<option '.$sel.' value="'.$values[$i].'">'.$labels[$i].'</option>';
			}
			$basic_dropdown .= '</select>';
			echo $basic_dropdown;
			?>

			</input>
			<script type="text/javascript">
				jQuery(window).load(function(){
					var h2 = jQuery("#<?php echo $id; ?>-dropdown > div > h2");
					var div = jQuery("#<?php echo $id; ?>-dropdown > div");
					h2.width(div.width() - 70);
				});
			</script>
		</div>
		<?php
		$this->add_js_sizing_dropdown($id);
	}

	public function add_js_sizing_dropdown ($id){
		$this->js_on_resize .= '
			var h2 = jQuery("#'.$id.'-dropdown > div > h2");
			var div = jQuery("#'.$id.'-dropdown > div");
			h2.width(div.width() - 70);
		';
	}

	public function add_js_sizing_field ($id){
		$this->js_on_resize .= '
			var input_field = jQuery("#'.$id.'");
			var div = jQuery("#'.$id.'-div");
			input_field.width(div.width() - 78);
		';
	}

	public function run_js_on_resize (){
		?>
		<script>
			function vcp_on_resize(c,t){onresize=function(){clearTimeout(t);t=setTimeout(c,100)};return c};
			vcp_on_resize(function(){
				<?php echo $this->js_on_resize; ?>
			});
		</script>
		<?php
	}

	public function run_js_on_ending(){
		?>
		<script>
			/*jQuery(window).load(function(){
				jQuery(".kjp-nice-field").each(function () {
					alert(jQuery(this).attr('id'));
					jQuery(this).on
				})
			})*/
		</script>
		<?php
	}

	public function basic_field($name){
		$name = str_replace(' ', '-', $name);
		$name = str_replace('_', '-', $name);
		$args = $this->input_args[$name];
		$label = (isset($args['label'])) ? $args['label'] : str_replace('-', ' ', $name);
		$value = (isset($args['value'])) ? $args['value'] : '' ;
		$id = (isset($args['id'])) ? $args['id'] : $name.'-id';
		$type = $args['type'];
		$input_extras = (isset($args['input_extras'])) ? $args['input_extras'] : '' ;
		$label_extras = (isset($args['label_extras'])) ? $args['label_extras'] : '' ;
		$styles = (isset($args['styles'])) ? ' style="'.$args['styles'].'"' : '' ;
		$on_change = (isset($args['on_change'])) ? 'onchange="'.$args['on_change'].'"' : '' ;

		$table_html = array(
			'<div class="meta-th">',
			'</div>',
			'<div class="meta-td">',
			'</div>',
			);
		if (isset($args['table'])){
			$args['table'] = $table_html;
		}
		else{
			$args['table'] = array();
			for ($i=0; $i < count($table_html); $i++) { 
				$args['table'][] = '';
			}
		}

		$meta = $this->stored_meta[str_replace('-', '_', $name)];
		$value = (! empty ($meta)) ? esc_attr($meta[0]) : $value ;
		$basic_field = '<div class="meta-row" '.$styles.'>';
			$basic_field .= $args['table'][0];
			$basic_field .= '<label for="'.$name.'" '.$label_extras.'>'.$label.' : </label>';
			$basic_field .= $this->basic_hintbox($args);
			$basic_field .= $args['table'][1];
			$basic_field .= $args['table'][2];
			$basic_field .= '<input type="'.$type.'" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$on_change.' '.$input_extras.' ></input>';
			$basic_field .= $args['table'][3];
		$basic_field .= '</div>';
		echo $basic_field;
	}

	protected function embed_link_fix (){
		return str_replace('watch?v=', 'embed/', $this->meta['video_link'][0]);
	}

	public function basic_video0($args = array()){
		if (!isset($args['link']) || !isset($args['width'])) {
			return;
		}
		if (strpos($args['link'], 'https://youtu.be/') !== false) {
			$args['link'] = str_replace('https://youtu.be/', 'https://www.youtube.com/embed/');
		}
		$args['style'] = (isset($args['style'])) ? ' style="width:'.$args['width'].';'.$args['style'].'"' : ' style="width:'.$args['width'].';"' ;
		$args['class'] = (isset($args['class'])) ? ' class="'.$args['class'].'"' : '' ;
		$args['id'] = (isset($args['id'])) ? ' id="'.$args['id'].'"' : '' ;
		$iframe_styles = 'style="position: absolute;top:0;left:0;width: 100%!important;height: 100%!important;"';
		$iframe = '<iframe '.$args['id'].' src="'.$args['link'].'" frameborder="0" allowfullscreen=""'.$iframe_styles.'></iframe>';
		$con = array(
			'<div '.$args['class'].$args['style'].'>',
			'<div style="position: relative;padding-bottom: 56.25%;padding-top: 25px;">',
			'</div>'
		);
		echo $con[0].$con[1].$iframe.$con[2].$con[2];
	}

	public function basic_video($args){
		if (!isset($args['link']) || !isset($args['width'])) {
			return;
		}
		if ($args['link'] ) {
			# code...
		}
		$args['class'] = (isset($args['class'])) ? ' class="'.$args['class'].'"' : '' ;
		$args['id'] = (isset($args['id'])) ? ' id="'.$args['id'].'" ' : '' ;
		$iframe_styles = 'style="position: absolute;top:0;left:0;width: 100%!important;height: 100%!important;"';
		$iframe = '<iframe '.$args['id'].$iframe_styles.' width="'.$args['width'].'" src="'.$args['link'].'" frameborder="0" allowfullscreen></iframe>';

		$con = array(
			'<div '.$args['class'].$args['style'].'>',
			'<div style="position: relative;padding-bottom: 56.25%;padding-top: 25px;">',
			'</div>'
		);
		echo $con[0].$con[1].$iframe.$con[2].$con[2];
	}

	public function basic_dropdown ($name, $values = array('default'), $labels = array('default')){
		$name = str_replace(' ', '-', $name);
		$name = str_replace('_', '-', $name);
		$args = $this->input_args[$name];
		$label = (isset($args['label'])) ? $args['label'] : str_replace('_', ' ', $args['name']) ;
		$id = (isset($args['id'])) ? $args['id'] : $args['name'].'-id' ;
		$styles = (isset($args['styles'])) ? ' style="'.$args['styles'].'"' : '' ;
		$classes = (isset($args['classes'])) ? $args['classes'] : '' ;
		$values = (isset($args['values'])) ? $args['values'] : $values ;
		$labels = (isset($args['labels'])) ? $args['labels'] : $labels ;
		$label = (isset($args['label'])) ? $args['label'].' : ' : '' ;
		$def = (isset($args['default'])) ? $args['default'] : '' ;
		$on_change = (isset($args['on_change'])) ? 'onchange="'.$args['on_change'].'"' : '' ;

		$meta = $this->stored_meta[str_replace('-', '_', $name)]; //replace spaces with underscores
		$stored_value = (! empty ($meta)) ? esc_attr($meta[0]) : $values[0] ;

		$basic_dropdown = '<div class="meta-row '.$classes.' " '.$styles.'>';
			$basic_dropdown .= '<label for="'.$name.'">'.$label.'</label>';
			$basic_dropdown .= $this->basic_hintbox($args);
			$basic_dropdown .= '<select name="'.$name.'" id="'.$id.'" '.$on_change.'>';
			for ($i=0; $i < count($values); $i++) { 
				$sel = ($stored_value == $values[$i]) ? 'selected' : '' ;
				$basic_dropdown .= '<option '.$sel.' value="'.$values[$i].'">'.$labels[$i].'</option>';
			}
			$basic_dropdown .= '</select>';
		$basic_dropdown .= '</div>';

		echo $basic_dropdown;
	}

	public function basic_hintbox ($args = array()){
		if (isset($args['hintbox'])){
			$info_btn = '<button class="hintbox" type="button" onclick="alert('."'".$args['hintbox']."'".')">?</button>';
			return $info_btn;
		}
		else{return '';}
	}

	public function wp_editor ($post, $name = 'content'){
		$name = str_replace(' ', '-', $name);
		$name = str_replace('_', '-', $name);
		$meta = str_replace('-','_',$name); //replace spaces with underscores
		$content = get_post_meta($post->ID, $meta, true);

		$settings = array(
			'textarea_rows' => 8,
			'media_buttons' => true
		);
		?>
		<div id="kjp_editor_<?php echo($meta); ?>" class="meta-editor">
		<?php wp_editor($content, $meta, $settings); ?>
		</div>
		<?php
	}
	public function add_wp_editor_field ($name){
		$this->add_input_field(array('name' => $name, 'sanitize' => false));
	}

	public function contain ($args = array()){
		$start = (isset($args['start'])) ? $args['start'] : false ;
		$style = (isset($args['style'])) ? 'style="'.$args['style'].'"' : '' ;
		$class = (isset($args['class'])) ? 'class="'.$args['class'].'"' : '' ;
		$attr = (isset($args['attr'])) ? $args['attr'] : '' ;
		if ($start == false){
			echo ('</div>');
		}
		else{
			echo ('<div '.$class.' '.$style.' '.$attr.'>');
		}
	}

}

class kjp_popup {

	protected $title;
	protected $id;
	protected $image;
	protected $message;
	protected $styles;
	protected $textfield;
	protected $buttons;
	protected $id_prefix = 'kjp-popup-';
	protected $popup_width;
	protected $font_size;

	public function __construct($args){
		$this->title = (isset($args['title'])) ? $args['title'] : 'Nodification' ;
		$this->id = (isset($args['id'])) ? $args['id'] : $this->title;
		$this->id = str_replace("_", "-", $this->id);
		$this->id = str_replace(" ", "-", $this->id);
		$this->styles = (isset($args['styles'])) ? ' style="'.$args['styles'].'" ' : '' ;
		$this->popup_width = (isset($args['popup_width'])) ? $args['popup_width'] : '350px;' ;
		$this->message = (isset($args['message'])) ? $args['message'] : false ;
		$this->buttons = (isset($args['buttons'])) ? $args['buttons'] : false; // if a button's text is "close" it will automatically get the close on click.
		$this->image = (isset($args['image'])) ? '<img class="kjp-popup-img" src="'.$args['image'].'"/>' : '';
		$this->textfield = (isset($args['textfield'])) ? $args['textfield'] : false ;
		$this->font_size = (isset($args['font_size'])) ? ' font-size:'.$args['font_size'].';' : ' font-size:28px !important;' ;

		$this->HTML();
	}

	public function get_id(){
		return $this->id_prefix.$this->id;	
	}

	public function get_open_class(){
		return 'popup-open-'.$this->id;
	}

	public function HTML (){
		?>
		<div class="kjp-popup kjp-hidden" id="<?php echo $this->get_id(); ?>" <?php echo $this->styles; ?> >
			<div class="kjp-popup-frame" style="width:<?php echo $this->popup_width; ?>;">
				<div class="kjp-popup-header">
					<h2 style="float:left; <?php echo $this->font_size; ?> "><?php echo $this->title; ?></h2>
					<button style="margin-top:4px; float:right; margin-right:6px;" type="button" class="kjp-close">X</button>
				</div>
				<div class="kjp-popup-content">
					<p><?php echo $this->message; ?></p>
				</div>
				<div class="kjp-popup-footer">
					<?php if($this->textfield != false){$this->text_field_HTML();};?>
					<?php $this->buttons_HTML(); ?>
				</div>
			</div>
			<div class="kjp-popup-bkg"></div>
			<?php $this->js_close_popup(); ?>
			<?php $this->js_open_popup(); ?>
		</div>
		<?php
	}

	public function text_field_HTML(){?>
		<span style="margin-left:9%; width:80%;" name="vcp-shortcode-field">
			<input style="width:80%; text-align:center;" readonly="readonly" type="text" value="<?php echo $this->textfield; ?>"/>
		</span>
		<?php
	}

	public function buttons_HTML (){
		if ($this->buttons == false) {
			return;
		}

		foreach ($this->buttons as $key => $btn): ?>
			<button name="popup-btn-<?php echo $btn[0]; ?>" type="button" class="button button-primary button-large "> <?php echo $btn[0]; ?> </button>
		<?php endforeach;?>
		<script type="text/javascript">
			jQuery(window).load(function(){
				<?php foreach ($this->buttons as $key => $btn) :?>
					var cur_btn = jQuery('#<?php echo $this->get_id(); ?> > .kjp-popup-frame > .kjp-popup-footer > button[name="popup-btn-<?php echo $btn[0] ?>"]');
					cur_btn.click(function(){
						<?php echo $btn[1]; ?>();
						jQuery("#<?php echo $this->get_id(); ?>").addClass("kjp-hidden");
					});
				<?php endforeach;?>

			});
		</script><?php
	}

	public function js_close_popup (){?>
		<script type="text/javascript">
		jQuery(window).load(function (){
			var popup = jQuery("#<?php echo $this->get_id(); ?>");
			var popup_bkg = jQuery("#<?php echo $this->get_id(); ?> > .kjp-popup-bkg");
			var xbtn = jQuery("#<?php echo $this->get_id(); ?> > .kjp-popup-frame > .kjp-popup-header > .kjp-close");
			xbtn.click(function (){
				popup.addClass("kjp-hidden");
			})
			popup_bkg.click(function (){
				popup.addClass("kjp-hidden");
			})
		})
		</script>
		<?php
	}

	public function js_open_popup (){?>
		<script type="text/javascript">
			jQuery(window).load(function() {
				jQuery(".<?php echo $this->get_open_class(); ?>").click(function(){
					jQuery("#<?php echo $this->get_id(); ?>").removeClass("kjp-hidden");

					var total_width = jQuery('#<?php echo $this->get_id(); ?> > .kjp-popup-frame').width();
					var count = 1;
					jQuery('#<?php echo $this->get_id(); ?> > .kjp-popup-frame > .kjp-popup-footer > button').each(function(){
						count = count + 1;
					})
					var spacing = total_width/count;
					var last_width = 0;
					jQuery('#<?php echo $this->get_id(); ?> > .kjp-popup-frame > .kjp-popup-footer > button').each(function(){
						var left_margin = spacing - (jQuery(this).outerWidth()/2) - (last_width);
						jQuery(this).css('margin-left', Math.floor(left_margin).toString()+'px');
						last_width = jQuery(this).outerWidth()/2;
					})
				});
			})
		</script>
		<?php
	}

	public function move_to_top(){
		// call from outside class after it is set in PHP to move.
	}


}