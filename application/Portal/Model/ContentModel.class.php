<?php
/**
 * Created by PhpStorm.
 * User: Richard
 * Date: 2016/3/12
 * Time: 16:18
 */

namespace Common\Model;

use Think;

class ContentModel extends CommonModel {
    Protected $autoCheckFields = false;

    function _initialize()
    {
        parent::_initialize();
    }

    /**
     * @param $uuid
     * string or array
     *
     * @param $data
     * Data for each uuid
     *
     * @return bool
     * True if all uuid successful
     * False otherwise
     */
    function saveByUUID($uuid, $data)
    {
        if (is_string($uuid)) {
            $arrUUID[0] = $uuid;
        } elseif (is_array($uuid)) {
            $arrUUID = $uuid;
        } else {
            return false;
        }


        //处理视图和原数据表字段名差别
        if (isset($data['post_title'])) {
            $data['title'] = $data['post_title'];
        }
        if (isset($data['post_status'])) {
            $data['status'] = $data['post_status'];
        }
        $flag = true;
        foreach($arrUUID as $id) {
            $tblname = $this->where([ 'id' => $id ])->getField('table_name'); //reveal table name for each uuid
            $db = M($tblname);
            $result = $db->where([ 'id' => $id ])->save($data);
            //            \Think\Log::record($result, \Think\Log::DEBUG);
            $flag = $flag && ($result !== false ? true : false);//when result for single row failed, flag will be false
        }
        return $flag;
    }

    /**
     * @param $uuid
     * string or array
     *
     * @return bool
     * True if all uuid successful
     * False otherwise
     */
    function deleteByUUID($uuid)
    {
        if (is_string($uuid)) {
            $arrUUID[0] = $uuid;
        } elseif (is_array($uuid)) {
            $arrUUID = $uuid;
        } else {
            return false;
        }

        $flag = true;

        foreach($arrUUID as $id) {
            $tblname = $this->where([ 'id' => $id ])->getField('table_name'); //reveal table name for each uuid

            $result = M($tblname)->where([ 'id' => $id ])->delete();
            $flag = $flag && ($result !== false ? true : false);//when result for single row failed, flag will be false
        }
        return $flag;
    }

    public function readByUUID($uuid, $field, $where)
    {
        $table_name = $this->where([ 'id' => $uuid ])->getField('table_name');
        $join = "" . C('DB_PREFIX') . "$table_name as b on a.object_id = b.id";
        $join2 = "" . C('DB_PREFIX') . 'users as c on b.post_author = c.id';
        if (isset($where['status'])) {
            $where['b.status'] = $where['status'];
            unset($where['status']);
        }
        $content = M('term_relationships')->alias('a')->join($join)->join($join2)->field($field)->where($where)->find();
        return $content;
    }
}