<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Tuolaji <479923197@qq.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;

use Common\Controller\AdminbaseController;

class AdminPostController extends AdminbaseController {
    protected $posts_model;
    protected $term_relationships_model;
    protected $terms_model;

    protected $dbContent;


    function _initialize()
    {
        parent::_initialize();
        $this->posts_model = D("Portal/Posts");
        $this->terms_model = D("Portal/Terms");
        $this->term_relationships_model = D("Portal/TermRelationships");

        $this->dbContent = D('content');
    }

    function index()
    {
        $this->_lists();
        $this->_getTree();
        $this->display();
    }

    private function _lists($status = 1)
    {
        $term_id = 0;
        if ( !empty($_REQUEST["term"])) {
            $term_id = intval($_REQUEST["term"]);
            $term = $this->terms_model->where("term_id=$term_id")->find();
            $this->assign("term", $term);
            $_GET['term'] = $term_id;
        }

        $where_ands = empty($term_id) ? array( "a.status=$status" ) : array( "a.term_id = $term_id and a.status=$status" );

        $fields = array(
            'start_time' => array( "field" => "post_date", "operator" => ">" ),
            'end_time'   => array( "field" => "post_date", "operator" => "<" ),
            'keyword'    => array( "field" => "post_title", "operator" => "like" ),
        );
        if (IS_POST) {

            foreach($fields as $param => $val) {
                if (isset($_POST[$param]) && !empty($_POST[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = $_POST[$param];
                    $_GET[$param] = $get;
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        } else {
            foreach($fields as $param => $val) {
                if (isset($_GET[$param]) && !empty($_GET[$param])) {
                    $operator = $val['operator'];
                    $field = $val['field'];
                    $get = $_GET[$param];
                    if ($operator == "like") {
                        $get = "%$get%";
                    }
                    array_push($where_ands, "$field $operator '$get'");
                }
            }
        }

        $where = join(" and ", $where_ands);


        $count = $this->term_relationships_model
            ->alias("a")
            ->join(C('DB_PREFIX') . "content b ON a.object_id = b.id")
            ->where($where)
            ->count();

        $page = $this->page($count, 20);


        $posts = $this->term_relationships_model
            ->alias("a")
            ->join(C('DB_PREFIX') . "content b ON a.object_id = b.id")
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order("a.listorder ASC,b.post_modified DESC")->select();
        //        $posts = $this->dbContent->select();


        $users_obj = M("Users");
        $users_data = $users_obj->field("id,user_login")->where("user_status=1")->select();
        $users = array();
        foreach($users_data as $u) {
            $users[$u['id']] = $u;
        }
        $terms = $this->terms_model->order(array( "term_id = $term_id" ))->getField("term_id,name", true);
        $this->assign("users", $users);
        $this->assign("terms", $terms);
        $this->assign("Page", $page->show('Admin'));
        $this->assign("current_page", $page->GetCurrentPage());
        unset($_GET[C('VAR_URL_PARAMS')]);
        $this->assign("formget", $_GET);
        $this->assign("posts", $posts);
    }

    private function _getTree()
    {
        $term_id = empty($_REQUEST['term']) ? 0 : intval($_REQUEST['term']);
        $result = $this->terms_model->order(array( "listorder" => "asc" ))->select();

        $tree = new \Tree();
        $tree->icon = array( '&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ' );
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        foreach($result as $r) {
            $r['str_manage'] = '<a href="' . U("AdminTerm/add", array( "parent" => $r['term_id'] )) . '">添加子类</a> | <a href="' . U("AdminTerm/edit", array( "id" => $r['term_id'] )) . '">修改</a> | <a class="js-ajax-delete" href="' . U("AdminTerm/delete", array( "id" => $r['term_id'] )) . '">删除</a> ';
            $r['visit'] = "<a href='#'>访问</a>";
            $r['taxonomys'] = $this->taxonomys[$r['taxonomy']];
            $r['id'] = $r['term_id'];
            $r['parentid'] = $r['parent'];
            $r['selected'] = $term_id == $r['term_id'] ? "selected" : "";
            $array[] = $r;
        }

        $tree->init($array);
        $str = "<option value='\$id' \$selected>\$spacer\$name</option>";
        $taxonomys = $tree->get_tree(0, $str);
        $this->assign("taxonomys", $taxonomys);
    }

    function add()
    {
        $terms = $this->terms_model->order(array( "listorder" => "asc" ))->select();
        $term_id = intval(I("get.term"));
        $this->_getTermTree();
        $term = $this->terms_model->where("term_id=$term_id")->find();
        $this->assign("author", "1");
        $this->assign("term", $term);
        $this->assign("terms", $terms);
        $this->display();
    }

    private function _getTermTree($term = array())
    {
        $result = $this->terms_model->order(array( "listorder" => "asc" ))->select();

        $tree = new \Tree();
        $tree->icon = array( '&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ' );
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        foreach($result as $r) {
            $r['str_manage'] = '<a href="' . U("AdminTerm/add", array( "parent" => $r['term_id'] )) . '">添加子类</a> | <a href="' . U("AdminTerm/edit", array( "id" => $r['term_id'] )) . '">修改</a> | <a class="js-ajax-delete" href="' . U("AdminTerm/delete", array( "id" => $r['term_id'] )) . '">删除</a> ';
            $r['visit'] = "<a href='#'>访问</a>";
            $r['taxonomys'] = $this->taxonomys[$r['taxonomy']];
            $r['id'] = $r['term_id'];
            $r['parentid'] = $r['parent'];
            $r['selected'] = in_array($r['term_id'], $term) ? "selected" : "";
            $r['checked'] = in_array($r['term_id'], $term) ? "checked" : "";
            $array[] = $r;
        }

        $tree->init($array);
        $str = "<option value='\$id' \$selected>\$spacer\$name</option>";
        $taxonomys = $tree->get_tree(0, $str);
        $this->assign("taxonomys", $taxonomys);
    }

    //排序

    function add_post()
    {
        if (IS_POST) {
            if (empty($_POST['term'])) {
                $this->error("请至少选择一个分类栏目！");
            }
            if ( !empty($_POST['photos_alt']) && !empty($_POST['photos_url'])) {
                foreach($_POST['photos_url'] as $key => $url) {
                    $photourl = sp_asset_relative_url($url);
                    $_POST['smeta']['photo'][] = array( "url" => $photourl, "alt" => $_POST['photos_alt'][$key] );
                }
            }
            $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);

            $_POST['post']['post_date'] = date("Y-m-d H:i:s", time());
            $_POST['post']['post_author'] = get_current_admin_id();
            $article = I("post.post");
            $article['smeta'] = json_encode($_POST['smeta']);
            $article['post_content'] = htmlspecialchars_decode($article['post_content']);
            $result = $this->posts_model->add($article);
            if ($result) {
                //
                foreach($_POST['term'] as $mterm_id) {
                    $this->term_relationships_model->add(array( "term_id" => intval($mterm_id), "object_id" => $result ));
                }

                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }

        }
    }

    public function edit()
    {
        $id = intval(I("get.id"));

        $term_relationship = M('TermRelationships')->where(array( "object_id" => $id, "status" => 1 ))->getField("term_id", true);
        $this->_getTermTree($term_relationship);
        $terms = $this->terms_model->select();
        $post = $this->posts_model->where("id=$id")->find();
        $this->assign("post", $post);
        $this->assign("smeta", json_decode($post['smeta'], true));
        $this->assign("terms", $terms);
        $this->assign("term", $term_relationship);
        $this->display();
    }

    public function edit_post()
    {
        if (IS_POST) {
            if (empty($_POST['term'])) {
                $this->error("请至少选择一个分类栏目！");
            }
            $post_id = intval($_POST['post']['id']);

            $this->term_relationships_model->where(array( "object_id" => $post_id, "term_id" => array( "not in", implode(",", $_POST['term']) ) ))->delete();
            foreach($_POST['term'] as $mterm_id) {
                $find_term_relationship = $this->term_relationships_model->where(array( "object_id" => $post_id, "term_id" => $mterm_id ))->count();
                if (empty($find_term_relationship)) {
                    $this->term_relationships_model->add(array( "term_id" => intval($mterm_id), "object_id" => $post_id ));
                } else {
                    $this->term_relationships_model->where(array( "object_id" => $post_id, "term_id" => $mterm_id ))->save(array( "status" => 1 ));
                }
            }

            if ( !empty($_POST['photos_alt']) && !empty($_POST['photos_url'])) {
                foreach($_POST['photos_url'] as $key => $url) {
                    $photourl = sp_asset_relative_url($url);
                    $_POST['smeta']['photo'][] = array( "url" => $photourl, "alt" => $_POST['photos_alt'][$key] );
                }
            }
            $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            unset($_POST['post']['post_author']);
            $article = I("post.post");
            $article['smeta'] = json_encode($_POST['smeta']);
            $article['post_content'] = htmlspecialchars_decode($article['post_content']);
            $result = $this->posts_model->save($article);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        }
    }

    public function listorders()
    {
        $status = parent::_listorders($this->term_relationships_model);
        if ($status) {
            $this->success("排序更新成功！");
        } else {
            $this->error("排序更新失败！");
        }
    }

    function delete()
    {
        if (isset($_GET['id'])) {
            $id = (I("get.id"));
            $data['status'] = 0;
            if ($this->term_relationships_model->where([ 'object_id' => $id ])->save($data)) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($_POST['ids'])) {
            //            $ids = join(",", $_POST['ids']);
            $ids = array_map(function($id) { return "'" . $id . "'"; }, $_POST['ids']);
            $ids = join(',', $ids);
            $data['status'] = 0;
            if ($this->term_relationships_model->where("object_id in ($ids)")->save($data)) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }

    function check()
    {
        if (isset($_POST['ids']) && $_GET["check"]) {
            $data["post_status"] = 1;
            $arrUUID = $_POST['ids'];

            if ($this->dbContent->saveByUUID($arrUUID, $data) === true) {
                $this->success('审核成功');
            } else {
                $this->error('一项或多项审核失败，请检查后重试！');
            }

        }
        if (isset($_POST['ids']) && $_GET["uncheck"]) {

            $data["post_status"] = 0;
            $arrUUID = $_POST['ids'];

            if ($this->dbContent->saveByUUID($arrUUID, $data) === true) {
                $this->success('取消审核成功');
            } else {
                $this->error('一项或多项取消审核失败，请检查后重试！');
            }
        }
    }

    function recyclebin()
    {
        $this->_lists(0);
        $this->_getTree();
        $this->display();
    }

    function clean()
    {
        if (isset($_POST['ids'])) {
            $tids = array_map(function($id) { return "'" . $id . "'"; }, $_POST['ids']);
            $tids = join(',', $tids);
            $status = $this->term_relationships_model->where("object_id in ($tids)")->delete();
            if ($status !== false) {
                foreach($_POST['ids'] as $post_id) {
                    $count = $this->term_relationships_model->where(array( "object_id" => $post_id ))->count();
                    if (empty($count)) {
                        $status = $this->dbContent->deleteByUUID($post_id);
                    }
                }
            }
            if ($status !== false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        } else {
            if (isset($_GET['id'])) {
                $id = I('get.id');
                $status = $this->term_relationships_model->where([ 'object_id' => $id ])->delete();
                if ($status !== false) {
                    $count = $this->term_relationships_model->where([ "object_id" => $id ])->count();
                    if (empty($count)) {
                        $status = $this->dbContent->deleteByUUID($id);
                    }

                }
                if ($status !== false) {
                    $this->success("删除成功！");
                } else {
                    $this->error("删除失败！");
                }
            }
        }
    }

    function restore()
    {
        if (isset($_GET['id'])) {
            $data = array( "status" => "1" );
            if ($this->term_relationships_model->where([ "object_id" => $_GET['id'] ])->save($data)) {
                $this->success("还原成功！");
            } else {
                $this->error("还原失败！");
            }
        }
    }

}