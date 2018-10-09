<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2018/9/26
 * Time: 下午4:34
 */

namespace Tourscool\Repository;


class Configuration extends Base
{
    /**
     * @description 根据key获取value值
     * @param  $key array|string
     * @return array
     */
    public function getValueByKey($key)
    {
        $sql = "SELECT * FROM `configuration` c WHERE ";
        if(is_string($key)){
            $sql .= " c.`key`=?";
            return $this->db->fetchAssoc($sql, [$key]);
        }elseif(is_array($key)){
            $bindString = implode(',', array_pad([], count($key), '?'));
            $sql .= " c.`key` IN ({$bindString})";
            return $this->db->fetchAll($sql,$key);
        }
    }
}