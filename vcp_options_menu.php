<?php
//namespace vcpplugin;

class VCP_globals{
    const vcp_num_of_background_images = 10;
    //const vcp_wpset_page = 'comment_display';
    const vcp_wpset_bkimage = 'comment_display_bk_image';
    const vcp_wpset_gfonts = 'comment_display_gfonts';
    const vcp_wpset_bk_img_titles = 'comment_display_bk_image_title';
}

/*define('VCP_globals::vcp_num_of_background_images', 10);
//define('vcp_wpset_page', 'comment_display');
define('VCP_globals::vcp_wpset_bkimage', 'comment_display_bk_image');
define('VCP_globals::vcp_wpset_gfonts', 'comment_display_gfonts');
define('VCP_globals::vcp_wpset_bk_img_titles', 'comment_display_bk_image_title');*/

class vcp_sub_page
{
    protected $title = 'Video Comments Plus';
    protected $capabilities = 'publish_posts';
    protected $wpset_page = 'vcp_general_settings';
    protected $wpset_group = 'vcp_comment_display';
    //protected $wpset_bkg_images_group = 'vcp_comment_display_background';
    //protected $wpset_googlefonts_group = 'vcp_googlefonts_ref_group';
    protected $wpset_styling_group = 'vcp_comment_display_styling';
    protected $wpset_section = 'vcp-comment-display-section';
    function slug(){
        return str_replace(' ', '_', $this->title);
    }


    function __construct (){
        add_action( 'admin_menu', array( $this, 'admin_construct' ) );
    }

    function admin_construct (){
        add_submenu_page(
            'edit.php?post_type=vcp',
            //'options-general.php',
            $this->wpset_page,
            $this->title.' Options',
            $this->capabilities,
            $this->slug(),
            array( $this, 'vcp_comment_display_settings_HTML')
        );
        add_action('admin_init', array($this, 'wp_comment_display_settings'));
    }

    public function wp_comment_display_settings (){
        
        for ($i=0; $i < VCP_globals::vcp_num_of_background_images; $i++) { 
            //register_setting($this->wpset_bkg_images_group, VCP_globals::vcp_wpset_bkimage.$i);
            register_setting($this->wpset_group, VCP_globals::vcp_wpset_bkimage.$i);
        }
        for ($i=0; $i < VCP_globals::vcp_num_of_background_images; $i++) { 
            //register_setting($this->wpset_bkg_images_group, VCP_globals::vcp_wpset_bk_img_titles.$i);
            register_setting($this->wpset_group, VCP_globals::vcp_wpset_bk_img_titles.$i);
        }
        add_settings_section($this->wpset_section, 'Comment display options', array( $this, 'wp_comment_display_section_callback'), $this->wpset_page);
        //register_setting($this->wpset_googlefonts_group, VCP_globals::vcp_wpset_gfonts, array($this, 'gfonts_sanatize'));
        register_setting($this->wpset_group, VCP_globals::vcp_wpset_gfonts, array($this, 'gfonts_sanatize'));
        //register_setting($this->wpset_googlefonts_group, VCP_globals::vcp_wpset_gfonts."_css", array($this, 'gfonts_css_sanatize'));
        register_setting($this->wpset_group, VCP_globals::vcp_wpset_gfonts."_css", array($this, 'gfonts_css_sanatize'));

        add_settings_field('new-image-url', 'Background Options:', array($this, 'new_image_url'), $this->wpset_page, $this->wpset_section);
        add_settings_field('googlefonts-ref', 'Google Fonts:', array($this, 'googlefonts_ref'), $this->wpset_page, $this->wpset_section);
    }

    public function new_image_url(){
        for ($i=0; $i < VCP_globals::vcp_num_of_background_images; $i++) { 
            $value = get_option(VCP_globals::vcp_wpset_bkimage.$i);
            $title = get_option(VCP_globals::vcp_wpset_bk_img_titles.$i);
            echo '<input type="text" name="'.VCP_globals::vcp_wpset_bkimage.$i.'" value="'.$value.'" placeholder="URL or Hex"/>';
            echo '<input type="text" name="'.VCP_globals::vcp_wpset_bk_img_titles.$i.'" value="'.$title.'" placeholder="Name the Image"/>';
            echo '</br>';
        }
    }

    public function googlefonts_ref(){
        $value = get_option(VCP_globals::vcp_wpset_gfonts);
        $w = 'width:374px;';
        echo '<input type="text" style="'.$w.'" name="'.VCP_globals::vcp_wpset_gfonts.'" value="'.$value.'" placeholder="google fonts \'Embed Font\' link"></>';
        echo "<br>";
        $value = get_option(VCP_globals::vcp_wpset_gfonts."_css");
        $text = str_replace("&apos", "'", $value);
        echo '<textarea style="'.$w.' min-height:130px;"" name="'.VCP_globals::vcp_wpset_gfonts.'_css" value="'.$value.'" placeholder="google fonts \'Specify in CSS\' text">'.$text.'</textarea>';
        echo "<br>";
    }

    public function gfonts_sanatize($input){
        if (!is_string($input)) {return "Invalid";}
        elseif ($input == '') {return '';}
        if ($input == get_option(VCP_globals::vcp_wpset_gfonts)) {
            return $input;
        }
        $inputquote = explode('"', $input);
        $inputapo = explode("'", $input);
        if (count($inputquote) < 3 && count($inputapo) < 3) {
            return "Invalid";
        }
        if (count($inputquote) >= 3) {
            $input = $inputquote;
        }
        else{
            $input = $inputapo;
        }
        return $input[1];
    }

    public function gfonts_css_sanatize($input){
        //return str_replace("'", "=", $input);
        return str_replace("'", "&apos", $input);
        //return 'ran';
    }

    public function vcp_comment_display_settings_HTML(){
        $download_xml = plugin_dir_url(__FILE__).'vcp-download-xml.php';
        ?>
        <div id="vcp-comment-display-wrap">
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
            <?php
            settings_fields($this->wpset_group);
            //settings_fields($this->wpset_bkg_images_group);
            //settings_fields($this->wpset_googlefonts_group);
            do_settings_sections($this->wpset_page);
            echo "<a style=\"display: inline-block;\" target=\"_blank\" href=\"https://fonts.google.com/\">Click here to get google fonts</a>";
            submit_button();
            ?>
            </form>
        </div>
        <br>
        <div>
            <br>
            <p>Database containing backups of the comments sections for each Video comment stream area. This is a list of URLs, and is for incase the URL of the page a comments stream was changed. Example: you want to move the comments stream from www.mysite.com/page1 to www.mysite.com/page2, or an entirely new website URL.</p>
            <button type="button"  class="button button-primary" onclick="location.href='<?php echo $download_xml ?>'">get xml database</button>
        </div>
        <?php
    }

    public function wp_comment_display_section_callback(){

    }
}

if( is_admin() ){
    $vcp_settings_page = new vcp_sub_page();
}




?>