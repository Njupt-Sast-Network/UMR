<?php

/**
 * 会员中心
 */
namespace User\Controller;

use Common\Controller\MemberbaseController;

class ProfileController extends MemberbaseController {

    protected $users_model;

    public function _initialize()
    {
        parent::_initialize();
        $this->users_model = D("Common/Users");
    }

    //编辑用户资料
    public function edit()
    {
        $userid = sp_get_current_userid();
        $user = $this->users_model->where(array( "id" => $userid ))->find();
        $this->assign($user);
        $this->display();
    }

    public function edit_post()
    {
        if (IS_POST) {
            $userid = sp_get_current_userid();
            $_POST['id'] = $userid;
            if ($this->users_model->field('id,user_nicename,sex,birthday,user_url,signature')->create()) {
                if ($this->users_model->save() !== false) {
                    $user = $this->users_model->find($userid);
                    sp_update_current_user($user);
                    $this->success("保存成功！", U("user/profile/edit"));
                }
                else {
                    $this->error("保存失败！");
                }
            }
            else {
                $this->error($this->users_model->getError());
            }
        }

    }

    public function password()
    {
        $userid = sp_get_current_userid();
        $user = $this->users_model->where(array( "id" => $userid ))->find();
        $this->assign($user);
        $this->display();
    }

    public function password_post()
    {
        if (IS_POST) {
            if (empty($_POST['old_password'])) {
                $this->error("原始密码不能为空！");
            }
            if (empty($_POST['password'])) {
                $this->error("新密码不能为空！");
            }
            $uid = sp_get_current_userid();
            $admin = $this->users_model->where("id=$uid")->find();
            $old_password = $_POST['old_password'];
            $password = $_POST['password'];
            if (sp_compare_password($old_password, $admin['user_pass'])) {
                if ($_POST['password'] == $_POST['repassword']) {
                    if (sp_compare_password($password, $admin['user_pass'])) {
                        $this->error("新密码不能和原始密码相同！");
                    }
                    else {
                        $data['user_pass'] = sp_password($password);
                        $data['id'] = $uid;
                        $r = $this->users_model->save($data);
                        if ($r !== false) {
                            $this->success("修改成功！");
                        }
                        else {
                            $this->error("修改失败！");
                        }
                    }
                }
                else {
                    $this->error("密码输入不一致！");
                }

            }
            else {
                $this->error("原始密码不正确！");
            }
        }

    }

    public function bang()
    {
        $oauth_user_model = M("OauthUser");
        $uid = sp_get_current_userid();
        $oauths = $oauth_user_model->where(array( "uid" => $uid ))->select();
        $new_oauths = array();
        foreach($oauths as $oa) {
            $new_oauths[strtolower($oa['from'])] = $oa;
        }
        $this->assign("oauths", $new_oauths);
        $this->display();
    }

    public function upload_paper()
    {
        //TODO:
        $this->display();
    }

    public function upload_project()
    {
        //TODO:
        $this->display();
    }

    public function upload_award()
    {
        //TODO:
        $this->display();
    }

    public function upload_patent()
    {
        //TODO:
        $this->display();
    }

    public function article_post()
    {
        if (IS_POST) {
            if ( !in_array(I('post.post')['post_type'], [ 'paper', 'project', 'award', 'patent' ])) {
                $this->error('post类型不正确');
            }
            $dbContent = M('content_' . I('post.post')['post_type']);
            $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);

            //$_POST['post']['post_date'] = date("Y-m-d H:i:s", time());
            //提交时间(包括修改时间)交由数据库的特性处理
            $_POST['post']['post_author'] = sp_get_current_userid();
            $article = I("post.post");
            $article['smeta'] = json_encode($_POST['smeta']);
            $article['provement'] = htmlspecialchars_decode($article['provement']);
            $result = $dbContent->add($article);
            if ($result) {
                $this->success("添加成功！");
            }
            else {
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
        //TODO:
        $this->display();
    }

    public function manage_project()
    {
        //TODO:
        $this->display();
    }

    public function manage_award()
    {
        //TODO:
        $this->display();
    }

    public function manage_patent()
    {
        //TODO:
        $this->display();
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
        }
        else {
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
            }
            else {
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
            }
            else {
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
            }
            else {
                $this->error("头像更新失败！");
            }

        }
    }

}
