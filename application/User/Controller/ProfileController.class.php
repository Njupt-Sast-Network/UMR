<?php

/**
 * 会员中心
 */
namespace User\Controller;

use Common\Controller\MemberbaseController;

class ProfileController extends MemberbaseController {

    protected $users_model;
    protected $term_relationships_model;


    public function _initialize()
    {
        parent::_initialize();
        $this->users_model = D("Common/Users");
        $this->term_relationships_model = D("Portal/TermRelationships");
    }

    //编辑用户资料
    public function edit()
    {
        $userid = sp_get_current_userid();
        $user = $this->users_model->where(array( "id" => $userid ))->find();
        $this->assign($user);
        $this->display();
    }

    /**
     *修改用户信息
     */
    public function edit_post()
    {
        if (IS_POST) {
            $userid = sp_get_current_userid();
            $_POST['id'] = $userid;
            if ($this->users_model->field('id,user_name,sex,birthday,user_url,signature')->create()) {
                if ($this->users_model->save() !== false) {
                    $user = $this->users_model->find($userid);
                    sp_update_current_user($user);
                    $this->success("保存成功！", U("user/profile/edit"));
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->users_model->getError());
            }
        }

    }

    public function password()
    {
        $this->display();
    }

    public function upload_paper()
    {
        $this->display();
    }

    public function upload_project()
    {
        $this->display();
    }

    public function upload_award()
    {
        $this->display();
    }

    public function upload_patent()
    {
        $this->display();
    }

    public function content_post()
    {
        if (IS_POST) {
            if ( !in_array(I('post.post')['post_type'], [ 'paper', 'project', 'award', 'patent' ])) {
                $this->error('post类型不正确');
            }
            switch (I('post.post')['post_type']) {
                case 'paper':
                    $term_id = 2;
                    break;
                case 'project':
                    $term_id = 3;
                    break;
                case 'award':
                    $term_id = 4;
                    break;
                case 'patent':
                    $term_id = 5;
                    break;

            }
            $dbContent = M('content_' . I('post.post')['post_type']);
            $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);

            //$_POST['post']['post_date'] = date("Y-m-d H:i:s", time());
            //提交时间(包括修改时间)交由数据库的特性处理
            $_POST['post']['post_author'] = sp_get_current_userid();
            $article = I("post.post");
            $article['smeta'] = json_encode($_POST['smeta']);
            $article['provement'] = htmlspecialchars_decode($article['provement']);
            $article['id'] = uuid();
            $result = $dbContent->add($article);
            if ($result) {
                $this->term_relationships_model->add(array( "term_id" => $term_id, "object_id" => $article['id'] ));
                $this->success("添加成功！");
            } else {
                $this->error("添加失败！");
            }

        }
    }

    public function edit_award()
    {
        $this->edit_content('award');
    }

    private function edit_content($type)
    {
        if ( !isset($_GET['id'])) {
            $this->error("缺少参数");
        }
        if ( !in_array($type, [ 'paper', 'project', 'award', 'patent' ])) {
            $this->error('post类型不正确');
        }
        $dbContent = M('content_' . $type);
        $where['post_author'] = sp_get_current_userid();
        $where['id'] = I($_GET['id']);
        $post = $dbContent->where($where)->find();
        $this->assign("post", $post);
        $this->display();
    }

    public function edit_paper()
    {
        $this->edit_content('paper');
    }

    public function edit_patent()
    {
        $this->edit_content('patent');
    }

    public function edit_project()
    {
        $this->edit_content('project');
    }

    public function content_update()
    {
        if (IS_POST) {
            if ( !in_array(I('post.post')['post_type'], [ 'paper', 'project', 'award', 'patent' ])) {
                $this->error('post类型不正确');
            }
            if ( !isset($_POST['id'])) {
                $this->error("缺少参数");
            } else {
                $where['id'] = intval($_POST['id']);
            }
            $dbContent = M('content_' . I('post.post')['post_type']);
            $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);

            //$_POST['post']['post_date'] = date("Y-m-d H:i:s", time());
            //提交时间(包括修改时间)交由数据库的特性处理

            $where['post_author'] = sp_get_current_userid();
            $article = I("post.post");
            $article['smeta'] = json_encode($_POST['smeta']);
            $article['provement'] = htmlspecialchars_decode($article['provement']);
            $article['status'] = 0;
            $result = $dbContent->where($where)->save($article);
            if ($result !== false) {
                $this->success("更新成功！");
            } else {
                $this->ajaxReturn($dbContent->getLastSql()); //DEV
                //$this->error("添加失败！");
            }

        }
    }

    public function manage()
    {
        //TODO:
        $this->display();
    }

    public function manage_paper()
    {
        $db = M('content_paper');
        $userid = sp_get_current_userid();
        $paper = $db->where('post_author= ' . $userid)->order('post_modified desc')->select();
        $this->assign('paper', $paper);
        $this->display();
    }

    public function manage_project()
    {
        $db = M('content_project');
        $userid = sp_get_current_userid();
        $project = $db->where('post_author= ' . $userid)->order('post_modified desc')->select();

        foreach($project as &$i) {

            switch ($i['project_level']) {
                case 1:
                    $i['project_level'] = '国家级';
                    break;
                case 2:
                    $i['project_level'] = '省部级';
                    break;
                case 3:
                    $i['project_level'] = '市厅级';
                    break;
                case 4:
                    $i['project_level'] = '校级';
                    break;
                case 5:
                    $i['project_level'] = '其他';
                    break;
            }

        }
        $this->assign('project', $project);
        $this->display();
    }

    public function manage_award()
    {
        $db = M('content_award');
        $userid = sp_get_current_userid();
        $award = $db->where('post_author= ' . $userid)->order('post_modified desc')->select();

        foreach($award as &$i) {

            switch ($i['award_level']) {
                case 1:
                    $i['award_level'] = '国家级';
                    break;
                case 2:
                    $i['award_level'] = '省部级';
                    break;
                case 3:
                    $i['award_level'] = '市厅级';
                    break;
                case 4:
                    $i['award_level'] = '校级';
                    break;
                case 5:
                    $i['award_level'] = '其他';
                    break;
            }

        }
        $this->assign('award', $award);
        $this->display();
    }

    public function manage_patent()
    {
        $db = M('content_patent');
        $userid = sp_get_current_userid();
        $patent = $db->where('post_author= ' . $userid)->order('post_modified desc')->select();

        foreach($patent as &$i) {

            switch ($i['type']) {
                case 1:
                    $i['type'] = '发明';
                    break;
                case 2:
                    $i['type'] = '实用新型';
                    break;
                case 3:
                    $i['type'] = '外观设计';
                    break;
                case 4:
                    $i['type'] = '其他';
                    break;
            }

        }
        $this->assign('patent', $patent);
        $this->display();
    }

    public function delete_paper()
    {
        $this->delete_content('paper');
    }

    private function delete_content($type)
    {
        $dbContent = M("content_" . $type);
        $userid = sp_get_current_userid();
        $map['post_author'] = $userid;
        $map['id'] = $_GET['id'];
        $result = $dbContent->where($map)->delete();
        if ($result) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }

    }

    public function delete_project()
    {
        $this->delete_content('project');
    }

    public function delete_award()
    {
        $this->delete_content('award');
    }

    public function delete_patent()
    {
        $this->delete_content('patent');
    }

    public function avatar()
    {
        $userid = sp_get_current_userid();
        $user = $this->users_model->where(array( "id" => $userid ))->find();
        $this->assign($user);
        $this->display();
    }

    public function avatar_upload()
    {
        $config = array(
            'rootPath' => './' . C("UPLOADPATH"),
            'savePath' => './avatar/',
            'maxSize'  => 512000, //500K
            'saveName' => array( 'uniqid', '' ),
            'exts'     => array( 'jpg', 'png', 'jpeg' ),
            'autoSub'  => false,
        );
        $driver_type = sp_is_sae() ? "Sae" : 'Local'; //TODO 其它存储类型暂不考虑
        $upload = new \Think\Upload($config, $driver_type); //
        $info = $upload->upload();
        //开始上传
        if ($info) {
            //上传成功
            //写入附件数据库信息
            $first = array_shift($info);
            $file = $first['savename'];
            $_SESSION['avatar'] = $file;
            $this->ajaxReturn(sp_ajax_return(array( "file" => $file ), "上传成功！", 1), "AJAX_UPLOAD");
        } else {
            //上传失败，返回错误
            $this->ajaxReturn(sp_ajax_return(array(), $upload->getError(), 0), "AJAX_UPLOAD");
        }
    }

    public function avatar_update()
    {
        if ( !empty($_SESSION['avatar'])) {
            $targ_w = intval($_POST['w']);
            $targ_h = intval($_POST['h']);
            $x = $_POST['x'];
            $y = $_POST['y'];
            $jpeg_quality = 90;

            $avatar = $_SESSION['avatar'];
            $avatar_dir = C("UPLOADPATH") . "avatar/";
            if (sp_is_sae()) {
                //TODO 其它存储类型暂不考虑
                $src = C("TMPL_PARSE_STRING.__UPLOAD__") . "avatar/$avatar";
            } else {
                $src = $avatar_dir . $avatar;
            }

            $avatar_path = $avatar_dir . $avatar;

            if (sp_is_sae()) {
                //TODO 其它存储类型暂不考虑
                $img_data = sp_file_read($avatar_path);
                $img = new \SaeImage();
                $size = $img->getImageAttr();
                $lx = $x / $size[0];
                $rx = $x / $size[0] + $targ_w / $size[0];
                $ty = $y / $size[1];
                $by = $y / $size[1] + $targ_h / $size[1];

                $img->crop($lx, $rx, $ty, $by);
                $img_content = $img->exec('png');
                sp_file_write($avatar_dir . $avatar, $img_content);
            } else {
                $image = new \Think\Image();
                $image->open($src);
                $image->crop($targ_w, $targ_h, $x, $y);
                $image->save($src);
            }

            $userid = sp_get_current_userid();
            $result = $this->users_model->where(array( "id" => $userid ))->save(array( "avatar" => $avatar ));
            $_SESSION['user']['avatar'] = $avatar;
            if ($result) {
                $this->success("头像更新成功！");
            } else {
                $this->error("头像更新失败！");
            }

        }
    }

}
