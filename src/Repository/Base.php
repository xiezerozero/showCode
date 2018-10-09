<?php

namespace Tourscool\Repository;


use Tourscool\Db\Db;

/**
 * Class Base
 * @package Tourscool\Repository
 * @property \Doctrine\DBAL\Connection $db
 */
abstract class Base implements IDataOperation
{

    // 成功
    const SUCCESS = 0;
    //参数错误
    const PARAM_ERROR = 101;
    //未登录
    const USER_NOT_LOGIN = 102;
    //未授权(没权限)
    const UNAUTHENICATED = 103;
    // 找不到记录
    const RECORD_NOT_FOUND = 104;
    // insert update delete 失败
    const ERROR_ON_OPERATE = 105;
    // 验证码不正确
    const MESSAGE_CODE_INCORRECT = 106;
    // 系统错误
    const INTERNAL_ERROR = 500;

    public function __get($name)
    {
        switch ($name) {
            case 'db':
                return Db::getInstance();
            default :
                throw new \Exception("找不到字段");
        }
    }


    /**
     * @description 根据表主键获取单条数据
     * @return array|bool
     */
    public function find($table, $primaryKey, $value)
    {
        return $this->findByAttributes($table, [$primaryKey => $value]);
    }

    /**
     * @description 根据条件查找所有数据
     */
    public function findAll($table, array $criteria, array $options = [])
    {
        $sql = "SELECT * FROM `{$table}`";
        $whereString = '';
        $values = [];
        if (!empty($criteria)) {
            $i = 0;
            foreach ($criteria as $k => $v) {
                if ($i == 0) {
                    $whereString .= " `{$k}` = ? ";
                } else {
                    $whereString .= " AND `{$k}` = ?";
                }
                array_push($values, $v);
                $i ++;
            }
            $sql .= " WHERE {$whereString}";
        }
        if (isset($options['page']) && isset($options['pageSize'])) {
            $offset = $options['pageSize'] * ($options['page'] - 1);
            $sql .= " LIMIT {$offset}, {$options['pageSize']} ";
        }

        if (isset($options['order_by']) && $options['order_direction']) {
            $sql .= " order by {$options['order_by']} {$options['order_direction']} ";
        }
        return $this->db->fetchAll($sql, $values);
    }

    /**
     * @description 根据条件获取单条数据
     */
    public function findByAttributes($table, array $criteria)
    {
        $sql = "SELECT * FROM `{$table}`";
        $whereString = '';
        $values = [];
        if (!empty($criteria)) {
            $i = 0;
            foreach ($criteria as $k => $v) {
                if ($i == 0) {
                    $whereString .= " `{$k}` = ? ";
                } else {
                    $whereString .= " AND `{$k}` = ?";
                }
                array_push($values, $v);
                $i++;
            }
            $sql .= " WHERE {$whereString} LIMIT 1";
        }
        return $this->db->fetchAssoc($sql, $values);
    }

    /**
     * @description 新增
     */
    public function add($table, $data)
    {
        return $this->db->insert($table, $data);
    }

    /**
     * @description 根据条件更新数据
     */
    public function update($table, $data, array $criteria)
    {
        return $this->db->update($table, $data, $criteria);
    }

    /**
     * @description 根据条件删除数据
     */
    public function delete($table, array $criteria)
    {
        return $this->db->delete($table, $criteria);
    }

    /**
     * @description 根据条件计算数量
     */
    public function count($table, array $criteria)
    {
        $sql = "select count(1) as c FROM `{$table}`";
        $whereString = '';
        $values = [];
        if (!empty($criteria)) {
            $i = 0;
            foreach ($criteria as $k => $v) {
                if ($i == 0) {
                    $whereString .= "`{$k}` = ?";
                } else {
                    $whereString .= " AND `{$k}` = ?";
                }
                array_push($values, $v);
                $i ++;
            }
            $sql .= " WHERE {$whereString} LIMIT 1";
        }
        return $this->db->fetchColumn($sql, $values);
    }

}