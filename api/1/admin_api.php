<?php
/***************************************************************************
 * Copyright (c) 2015 youzu.com, Inc. All Rights Reserved
 **************************************************************************/
 
/**
 * @file bigapp_admin_api.php
 * @author bigapp(@youzu.com)
 * @date 2015/07/15 16:34:40
 *  
 **/
class bigapp_admin_api{
    protected $route = "admin_api";   
    //register routes
    public function register_routes( $routes ) { 
        $routes[ $this->route] = array(
            "update_menu_conf" =>array( array($this,"update_menu_conf"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),  
            "update_banner_conf" =>array( array($this,"update_banner_conf"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),  
            "update_menu_switch" =>array( array($this,"update_menu_switch"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),  
            "update_verify_info" =>array( array($this,"set_ak_sk"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),  
            "add_tag" =>array( array($this,"check_tag_valid"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),  
            "upload_img" =>array( array($this,"upload_img"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			 "mobile_api" =>array( array($this,"mobile_api"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			 "mobile_page" =>array( array($this,"mobile_page"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			 "update_special_conf" =>array( array($this,"update_special_conf"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			 "add_special" =>array( array($this,"add_special"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			 "delete_special" =>array( array($this,"delete_special"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			 "add_special_post" =>array( array($this,"add_special_post"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			 "delete_special_post" =>array( array($this,"delete_special_post"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
			 "update_special_post_meta" =>array( array($this,"update_special_post_meta"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),
            );
       return $routes; 
    }
	
	
	public function add_special($title,$img='',$order=0,$type=1){
		$ret = false;
		if(!$info = term_exists($title,'bigapp_special')){
			$des['type'] = $type;
			$des['img'] = $img;
			$des['order'] = $order;
			$des['date'] = date("Y-m-d H:i:s");
			$description = json_encode($des);
			$slug = $title;
			$parent = 0;
			$args = compact('title', 'slug', 'parent', 'description');
			$id = wp_insert_term($title,"bigapp_special",$args);
			$ret = true;
		}else{
			$term = get_term( $info['term_id'], 'bigapp_special', ARRAY_A );
			$des['type'] = $type;
			if(!empty($img)){
				$des['img'] = $img;
			}
			if(!empty($order)){
				$des['order'] = $order;
			}
			if(!empty($term['description'])){
				$description = json_decode($term['description'],true);
				foreach($des as $k => $d){
					$description[$k] = $d;
				}
			}else{
				$description = $des;
			}
			$description = json_encode($description);
			$slug = $title;
			$parent = 0;
			$args = compact('title', 'slug', 'parent', 'description');
			wp_update_term($info['term_id'],'bigapp_special',$args);
			$ret = true;
		}
	    return $ret;
	}
	/**
     * 删除专题
	 * special_id 专题id
     */
	public function delete_special($special_id){
		$ret = false;
		if(is_array($special_id)){
			foreach($special_id as $id){
				$ret = wp_delete_term($id,'bigapp_special');
			}
		}else{
			$ret = wp_delete_term($special_id,'bigapp_special');
		}
		
		return $ret;
	}
	/**
     * 删除专题文章
	 * special_id 专题id
	 * id 文章id
     */
	public function delete_special_post($id,$special_id){
		$ret = false;
		if(is_array($id)){
			foreach($id as $i){
				$ret = wp_delete_object_term_relationships(inval($i),intval($special_id));
			}
		}else{
			$ret = wp_remove_object_terms( intval($id), intval($special_id), 'bigapp_special' );
		}
		return $ret;
	}
	
	/**
     * 更新专题文章
     */
	public function add_special_post($title,$url=null,$id=null){
		global $wpdb;
		$ret = false;
		if(!is_null($id)){
			if(is_array($id)){
				foreach($id as $i){
					wp_set_object_terms($i,$title,'bigapp_special');
				}
				$ret = true;
			}else{
				$id = wp_set_object_terms($id,$title,'bigapp_special');
				if($id){
					$ret = true;
				}
			}
		}else{
			if(is_array($url)){
				foreach($url as $u){
					$id = $wpdb->get_results( $wpdb->prepare("select ID FROM $wpdb->posts WHERE guid = %s AND post_type in ('post','page')", trim($u)) );
					if(isset($id[0])){
						foreach($id as $i){
							wp_set_object_terms($i->ID,$title,'bigapp_special');
						}
						$ret = true;
					}
				}
			}else{
				$id = $wpdb->get_results( $wpdb->prepare("select ID FROM $wpdb->posts WHERE guid = %s AND post_type in ('post','page')", trim($url)) );
				if(isset($id[0])){
					foreach($id as $i){
						wp_set_object_terms($i->ID,$title,'bigapp_special');
					}
					$ret = true;
				}
			}
		}
		return $ret;
	}
	
	/**
     * 更新专题文章排序
     */
    public function update_special_post_meta($id,$order,$special_id){
        $meta_key = '_bigapp_special_order_'.$special_id;
		$meta = get_post_meta($id,$meta_key,true);
		if($meta){
			update_post_meta($id,$meta_key,$order);
		}else{
			add_post_meta($id,$meta_key,$order,true);
		}
		return true;
    }
	
	/**
     * 更新专题配置
     */
    public function update_special_conf($switch=1,$list_style=1,$detail_style=1){
        $special_conf['switch'] = $switch;
		$special_conf['list_style'] = $list_style;
		$special_conf['detail_style'] = $detail_style;
        update_option("bigapp_special_conf",json_encode($special_conf));
        return true;
    }

    /**
     * 更新菜单配置
     */
    public function update_menu_conf($menu_confs=array()){
        if (!is_string($menu_confs)){
            foreach($menu_confs as &$conf){
                $conf['name'] = urldecode($conf['name']);
            }
        }
        update_option("bigapp_menu_conf",json_encode($menu_confs));
        return true;
    }
    /**
     * 更新banner配置信息
     * banner_conf:array(array(name,img_url,url,type,rank,show))
     * type = 1 站外链接
     * type = 2 文章链接
     * type = 3 菜单链接
     */
    public function update_banner_conf($menu_id,$banner_conf=array()){
        //do image
        if(!is_array($banner_conf)){
            return false;
        }
        foreach($banner_conf as &$conf){
            if(!isset($conf['img_url']) || $conf['img_url'] == ''){
                return false;
            }
            $conf['name'] = urldecode($conf['name']);
            $conf['link'] = urldecode($conf['link']);

        }
        //save to db
        $db_banner_conf = get_option("bigapp_banner_conf");
        if($db_banner_conf){
            $db_banner_conf = json_decode(get_option("bigapp_banner_conf"),true);
        }
        $db_banner_conf[$menu_id] = $banner_conf;
        update_option("bigapp_banner_conf",json_encode($db_banner_conf));
        return true;
    }
    /**
     * 设置AK SK
     * @param ak,sk
     */
    public function set_ak_sk($ak,$sk){
        $ak = trim($ak);
        $sk = trim($sk);
        $st = false;
        if( strlen($ak) == 32 && strlen($sk) == 32 ){
            $ak_sk = array('ak'=>$ak,'sk'=>$sk);
            $st = update_option("bigapp_ak_sk",json_encode($ak_sk));
            $st = true;
        }else{
            json_error(BigAppErr::$server['code'],__lan("app key/app secret format is wrong"),"");
        }
        return $st;
    }
    /**
     * 菜单模块是否启用
     * switch:1:on 0:off
     */
    public function update_menu_switch($menu_switch=0){
        $switch = strtolower($menu_switch);
        if(!in_array($switch,array(0,1))){
            $switch = 0;
        }
        $st = update_option("bigapp_menu_switch",$switch);
        return true;
    }
    /**
     * 检测tag是否可以添加到右侧列表
     */
    public function check_tag_valid($tag){
        if($tag == ''){
            return false;
        }
        $tax = new WP_JSON_Taxonomies();
        $tag_list = $tax->get_post_tags(array("name__like"=>$tag));
        if($tag_list){
            foreach($tag_list as $list){
                if($list['name'] == $tag){
                    $list['type'] = $list['taxonomy'];
                    $list['show'] = false;
                    $list['rank'] = 100;
                    return $list;
                }
            }
        }
        return false;
    }
    /**
     * 上传图片接口
     * 图片格式要求
     */
    public function upload_img(){
        $key = isset($_REQUEST['key'])?$_REQUEST['key']:"upload_img";
        $ret = upload_img($key);
        $path = '';
        if($ret['status'] == 0){
            $path = $ret['data'];
        }else{

        }
        header("Content-Type: text/html;charset=utf-8");        //前端需要设置这样的header
        return $path;
    }
	
	public function mobile_page() {
		$params = BigAppAdminModel::get_extend_conf();
		
		$tm = time();
		$dateline = $params["dateline"] ? $params["dateline"] : 0;
		
		$webroot = explode("/",BIGAPP_ROOT);
		$plugin_name = $webroot[sizeof($webroot) - 1];
		
		$plugin_path = get_bloginfo('siteurl') . '/wp-content/plugins/' . $plugin_name . '/admin';
		$auth_info =  BigAppAdminModel::get_bigapp_ak_info();
        $ak = $auth_info['ak'];
		
		if ($dateline < $tm) {
			$obj = new BkSvr($auth_info['ak'], $auth_info['sk'], 30);
			$appInfo = $obj->getInfo(BigAppConf::$appInfoUrl, array('method' => 'get_basic'));
			
			$app_id = isset($appInfo['app_id']) ? $appInfo['app_id'] : 0;
			
			$ret = $obj->getInfo(BigAppConf::$taskInfoUrl, array('app_id' => $app_id, 'method'=>'get_latest'), false);
			
			if(false !== $ret && isset($ret["data"])){
				$params["icon_img"] = $ret["data"]["task_info"]["icon_image"];
				$params["appname"] = $ret["data"]["task_info"]["app_name"];
			}
			if ($params["icon_img"] == "") {
				$params["icon_img"] = $plugin_path."/img/logo.png";
			}
			if (!isset($params["appname"]) || $params["appname"]=="") { 
				$params["appname"] = "bigapp";
			}
			
			$params["pagetitle"] = $params["appname"].$params["title"];
			$params["dateline"] = time() + 600; //!< 每10分钟拉一次最新打包的配置
			
			//更新进db库
			$pstr = json_encode($params);
			$svalue = str_replace("\\\\u", "#u", $pstr);

			update_option("bigapp_extend_setting", $svalue);
		}
		
		$latest_pkgurl = BigAppConf::$releaseApis['latest_package'];

		$con = strpos($latest_pkgurl, "?")===false ? "?" : "&";
		$latest_pkgurl.= $con."app_key=".$auth_info['ak']."&os=1";
		
		$page_data["androidurl"] = $latest_pkgurl;
		
		if (isset($_GET["method"]) && $_GET["method"]=="down") {
			if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
				$url = $params["iosurl"];
				header("Location: $url");
			}else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
				$latest_pkgurl = $page_data["androidurl"];
				header("Location: $latest_pkgurl");
			}else{
				echo 'please open in mobile device!';
			}
			die(0);
		}
		
		$page_data["icon_img"] = $params["icon_img"];
		$page_data["mobile_app_img"] = $params["mobile_app_image"];
		if ($page_data["mobile_app_img"] == "") {
			$page_data["mobile_app_img"] = $plugin_path . "/img/preview.png";
		}
		$page_data["iosurl"] = $params["iosurl"];
		$page_data['plugin_path'] = $plugin_path;
		
		$js_data = $params;

		header("Content-Type: text/html;charset=utf-8");
		echo_output(loadTemplate('admin_mobile.tpl', $js_data, $page_data));
		exit(0);
		?>
		<?php
	}
	
	/**
     * 保存推广信息
     * 
     */
	public function mobile_api() {
		$params = array (
			"title"   => $_POST["title"],
			"iosurl"  => $_POST["ios_url"],
			"appdesc" => $_POST["appdesc"],
			"mobile_app_image" => $_POST["mobile_app_image"],
		);
		
		$webroot = explode("/", BIGAPP_ROOT);
		$plugin_name = $webroot[sizeof($webroot) - 1];
		
		$plugin_path = get_bloginfo('siteurl') . '/wp-content/plugins/' . $plugin_name . '/admin';
		$auth_info =  BigAppAdminModel::get_bigapp_ak_info();
		
		$obj = new BkSvr($auth_info['ak'], $auth_info['sk'], 30);
		$appInfo = $obj->getInfo(BigAppConf::$appInfoUrl, array('method' => 'get_basic'));
		
		$app_id = isset($appInfo['app_id']) ? $appInfo['app_id'] : 0;
		
		$ret = $obj->getInfo(BigAppConf::$taskInfoUrl, array('app_id' => $app_id, 'method'=>'get_latest'), false);
		
		if(false !== $ret && isset($ret["data"])){
			$params["icon_img"] = $ret["data"]["task_info"]["icon_image"];
			$params["appname"] = $ret["data"]["task_info"]["app_name"];
		}
		if ($params["icon_img"]=="") {
			$params["icon_img"] = $plugin_path."/img/logo.png";
		}
		if ($params["appname"]=="") 
			$params["appname"] = "bigapp";
		
		$params["pagetitle"] = $params["appname"].$params["title"];
		
		//!< 每10分钟拉一次最新打包的配置
		$params["dateline"] = time() + 600; 
		
		// 二维码链接生成短地址
		$bigapp_page_alias = BigAppConf::$page_alias; 
		$downurl = get_bloginfo('siteurl').'/?yz_app=1&api_route=admin_api&action=mobile_page&method=down';
		$params["downurl"] = bigapp_admin_api::createShortUrl($downurl);
		
		$pstr = json_encode($params);
		$svalue = str_replace("\\u", "#u", $pstr);

		update_option("bigapp_extend_setting", $svalue);
	}
	
	
	
	// 发送http请求
	function httpRequest($url ,$method = 'GET',$params = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if('POST' == $method){
			curl_setopt($ch, CURLOPT_POST, true);
			if(!empty($params)){
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			}
		}else{
			curl_setopt($ch, CURLOPT_HEADER, false);
		}
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	// 生成短地址
	function createShortUrl($url, $retry = 3)
	{
		if(empty($url)){
			return $url;
		}
		while($retry > 0) {
			//$dwz = "http://dwz.cn/create.php";
			$dwz = "http://s.youzu.com/gen.php";
			$data=array('url'=>$url);
			$res = bigapp_admin_api::httpRequest($dwz , 'POST' ,$data);
			$result =json_decode($res,true);
			$shortUrl = $url;
			if(isset($result['tinyurl'])){
				$shortUrl = $result['tinyurl'];
				break;
			}
			$retry--;
		}
		return $shortUrl;
	}
}





/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
