<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
/**
 * 文章内页
 */
namespace Portal\Controller;
use Common\Controller\HomebaseController;

class ArticleController extends HomebaseController {
    //文章内页
    public function index() {
    	$id=intval($_GET['id']);
    	$article=sp_sql_post($id,'');
    	if(empty($article)){
    	    header('HTTP/1.1 404 Not Found');
    	    header('Status:404 Not Found');
    	    if(sp_template_file_exists(MODULE_NAME."/404")){
    	        $this->display(":404");
    	    }
    	    
    	    return ;
    	}
    	$termid=$article['term_id'];
    	$term_obj= M("Terms");
    	$term=$term_obj->where("term_id='$termid'")->find();
    	
    	$article_id=$article['object_id'];
    	
    	$posts_model=M("Posts");
    	$posts_model->save(array("id"=>$article_id,"post_hits"=>array("exp","post_hits+1")));
    	
    	$article_date=$article['post_modified'];
    	
    	$join = "".C('DB_PREFIX').'posts as b on a.object_id =b.id';
    	$join2= "".C('DB_PREFIX').'users as c on b.post_author = c.id';
    	$rs= M("TermRelationships");
    	
    	$next=$rs->alias("a")->join($join)->join($join2)->where(array("post_modified"=>array("egt",$article_date), "tid"=>array('neq',$id), "status"=>1,'term_id'=>$termid))->order("post_modified asc")->find();
    	$prev=$rs->alias("a")->join($join)->join($join2)->where(array("post_modified"=>array("elt",$article_date), "tid"=>array('neq',$id), "status"=>1,'term_id'=>$termid))->order("post_modified desc")->find();
    	
    	 
    	$this->assign("next",$next);
    	$this->assign("prev",$prev);
    	
    	$smeta=json_decode($article['smeta'],true);
    	$content_data=sp_content_page($article['post_content']);
    	$article['post_content']=$content_data['content'];
    	$this->assign("page",$content_data['page']);
    	$this->assign($article);
    	$this->assign("smeta",$smeta);
    	$this->assign("term",$term);
    	$this->assign("article_id",$article_id);
    	
    	$tplname=$term["one_tpl"];
    	$tplname=sp_get_apphome_tpl($tplname, "article");
    	$this->display(":$tplname");
    }
    
    public function do_like(){
    	$this->check_login();
    	$object_id=$_GET['id'];//content表中id TODO:前端部分有点BUG，ajax post里带个object_id字段即可成功点赞

    	$can_like=sp_check_user_action($object_id,1);//这个函数是会有副作用的，不只是简单的check。如果没有结果会写数据库，坑。
    	
    	if($can_like){
    		D('content')->saveByUUID($object_id,array("post_like"=>array("exp","post_like+1")));
    		$this->success("赞好啦！");
			//TODO:加一个点赞表，记录点赞用户的信息
    	}else{
    		$this->error("您已赞过啦！");
    	}
    	
    }
	public function content() {
		$id=$_GET['id'];
		$article=sp_sql_content($id,'');
		if(empty($article)){
			header('HTTP/1.1 404 Not Found');
			header('Status:404 Not Found');
			if(sp_template_file_exists(MODULE_NAME."/404")){
				$this->display(":404");
			}

			return ;
		}
		$termid=$article['term_id'];
		$term_obj= M("Terms");
		$term=$term_obj->where("term_id='$termid'")->find();

		$article_id=$article['object_id'];

		$dbContent=D("content");
		$dbContent->saveByUUID($id,array("post_hits"=>array("exp","post_hits+1")));

		$article_date=$article['post_modified'];

		$join = "".C('DB_PREFIX').'content as b on a.object_id =b.id';
		$join2= "".C('DB_PREFIX').'users as c on b.post_author = c.id';
		$rs= M("TermRelationships");

		$next=$rs->alias("a")->join($join)->join($join2)->where(array("post_modified"=>array("egt",$article_date), "tid"=>array('neq',$id), "status"=>1,'a.term_id'=>$termid))->order("post_modified asc")->find();
		$prev=$rs->alias("a")->join($join)->join($join2)->where(array("post_modified"=>array("elt",$article_date), "tid"=>array('neq',$id), "status"=>1,'a.term_id'=>$termid))->order("post_modified desc")->find();


		$this->assign("next",$next);
		$this->assign("prev",$prev);

		$smeta = json_decode($article['smeta'], true);
		$content_data=sp_content_page($article['post_content']);
		$article['post_content']=$content_data['content'];
		switch ($article['term_id']){
			case 2: $article['content_type'] = '论文';
				break;
			case 3: $article['content_type'] = '项目';
				break;
			case 4: $article['content_type'] = '获奖';
				break;
			case 5: $article['content_type'] = '专利';
				break;
		}


		$this->assign("page",$content_data['page']);
		$this->assign("article", $article);
		$this->assign("smeta",$smeta);
		$this->assign("term",$term);
		$this->assign("article_id",$article_id);

		$tplname=$term["one_tpl"];
		$tplname=sp_get_apphome_tpl($tplname, "article");
		$this->display(":$tplname");
	}
}
