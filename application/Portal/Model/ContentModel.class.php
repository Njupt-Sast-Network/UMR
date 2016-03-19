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
            \Think\Log::record($result, \Think\Log::DEBUG);
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
}