<?php
/**
 * Created by PhpStorm.
 * User: Richard
 * Date: 2016/3/29
 * Time: 19:37
 */

namespace Portal\Model;

use Common\Model\CommonModel;

class LikeModel extends CommonModel {
    public function getLikeByUUID($uuid, $toString = false)
    {
        $arrResult = $this->alias('l')->join(C('DB_PREFIX') . 'users as u on l.user_id=u.id')->where([ 'object_id' => $uuid ])->getField('user_name', true);
        if ($toString) {
            $txtResult = join('、', $arrResult);
            return $txtResult;
        } else {
            return $arrResult;
        }


    }

}