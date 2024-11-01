<?php
//namespace vcpplugin;

add_action('wp_ajax_vcp_save_src', 'vcp_save_src');
function vcp_save_def_src(){
	$data = $_REQUEST['vcp_src_link'];
	$post_id = $data[0];
	$src = $data[1];

}

function vcp_save_src (){
	if(current_user_can( 'publish_posts' )){


		$data = $_REQUEST['vcp_src_link'];
		$post_id = $data[0];
		$src = $data[1];
		$page_url = $data[2];

		$xml_path = dirname(__FILE__).'/vcp-src-db.xml';
		$xml = simplexml_load_file($xml_path);
		vcp_db_add_src($post_id, $page_url, $src, $xml);
		$xml->asXml($xml_path);
		update_post_meta($post_id, "vcp_comment_source_link", $src);
	}
}

function vcp_db_add_src($post_id, $page_url, $src, $xml){
	if (current_user_can( 'publish_posts' )) {
		if(!isset($xml)){
			$xml_path = plugin_dir_path(__FILE__).'vcp-src-db.xml';
			$xml = simplexml_load_file($xml_path);
		}
		$post_id = ($post_id == '') ? 'vcp_notset' : 'vcp_'.(string)$post_id ;
		$page_url = ($page_url == '') ? 'url_notset' : (string)$page_url;
		$src = ($src == '') ? 'src_notset' : str_replace("&", "~", (string)$src);

		if(isset($xml->$post_id)){
			$found = false;
			foreach ($xml->$post_id->children() as $url) {
				if ($url == $page_url){
					//match
					$xml->$post_id->page_url = $page_url;
					$xml->$post_id->page_url->src = $src;
					$found = true;
					break;
				}
			}
			if ($found != true){
				$elem_post = $xml->$post_id;
				$elem_url = $elem_post->addChild('page_url', $page_url);
				$elem_url->addChild('src', $src);
			}

		}
		else{
			$elem_post = $xml->addChild((string)$post_id);
			$elem_url = $elem_post->addChild('page_url', $page_url);
			$elem_url->addChild('src', $src);
		}
		return true;
	}
	else{
		return false;
	}
}

function vcp_db_get_src($post_id, $page_url){
	if(current_user_can( 'publish_posts' )){
		$xml_path = dir(__FILE__).'vcp-src-db.xml';
		$xml = simplexml_load_file($xml_path);

		$post_id = 'vcp_'.(string)$post_id;

		foreach ($xml->$post_id->children() as $url) {
			if ($url == $page_url){
				return str_replace("~", "&", $url->src);
			}
		}
		return 'does not exist';
	}
	else{
		return 'you do not have permission to get src.';
	}
}

function vcp_db_get_urls($post_id){
	if(current_user_can( 'publish_posts' )){

		$xml_path = plugin_dir_path(__FILE__).'vcp-src-db.xml';
		$xml = simplexml_load_file($xml_path);
		$post_id = 'vcp_'.(string)$post_id;

		$pages = array();

		if(isset($xml->$post_id)){
			foreach ($xml->$post_id->children() as $url) {
				$pages[] = array($url, str_replace("~", "&", $url->src));
			}
		}

		return $pages;
	}
	else{
		return 'you do not have permission to get src.';
	}
}


?>