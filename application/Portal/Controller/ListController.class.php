<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController;

/**
 * 文章列表
*/
class ListController extends HomebaseController {

	//文章内页
	public function index() {
		$term=sp_get_term($_GET['id']);
		
		if(empty($term)){
		    header('HTTP/1.1 404 Not Found');
		    header('Status:404 Not Found');
		    if(sp_template_file_exists(MODULE_NAME."/404")){
		        $this->display(":404");
		    }
		    	
		    return ;
		}
		
		$tplname=$term["list_tpl"];
    	$tplname=sp_get_apphome_tpl($tplname, "list");
    	$this->assign($term);
    	$this->assign('cat_id', intval($_GET['id']));
    	$this->display(":$tplname");
	}
	
	public function nav_index(){
		$navcatname="文章分类";
		$datas=sp_get_terms("field:term_id,name");
		$navrule=array(
				"action"=>"List/index",
				"param"=>array(
						"id"=>"term_id"
				),
				"label"=>"name");
		exit(sp_get_nav4admin($navcatname,$datas,$navrule));
		
	}

    public function list_content()
    {
        $tplname = $term["list_tpl"];
        $tplname = sp_get_apphome_tpl($tplname, "list_content");
		if (empty($tplname)) {
			header('HTTP/1.1 404 Not Found');
			header('Status:404 Not Found');
			if (sp_template_file_exists(MODULE_NAME . "/404")) {
				$this->display(":404");
			}

			return;
		}
		$db = M('content');
		$db2 = 	M('term_relationships');
		$obj = $db2 ->where('status=1')->select();
		foreach ($obj as $item) {
			$objid[] =$item['object_id'];
		}
		$map['post_status']=1;//判断文章是否审核通过；
		$map['id'] = array('in',$objid);//判断文章是否被删除；
		$list = $db->where($map)->order('post_date')->select();
		$this->assign('lists_post',$list);
        $this->assign($term);
        $this->display(":$tplname");
    }
	
}
