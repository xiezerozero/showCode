<?php

namespace Tourscool\Repository;

/**
 * @description 数据操作接口
 */
interface IDataOperation
{

    /**
     * @description 根据表主键获取单条数据
     */
    public function find($table, $primaryKey, $value);

    /**
     * @description 根据条件查找所有数据
     */
    public function findAll($table, array $criteria, array $options = []);

    /**
     * @description 根据条件获取单条数据
     */
    public function findByAttributes($table, array $criteria);

    /**
     * @description 新增
     */
    public function add($table, $data);

    /**
     * @description 根据条件更新数据
     */
    public function update($table, $data, array $criteria);

    /**
     * @description 根据条件删除数据
     */
    public function delete($table, array $criteria);

    /**
     * @description 根据条件计算数量
     */
    public function count($table, array $criteria);

}