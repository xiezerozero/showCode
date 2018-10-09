<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2018/9/27
 * Time: 下午7:03
 */

namespace Tourscool\Repository;


class Review extends Base
{
    public $table = 'review';

    /**
     * @description 获取五星好评数据
     * @param $limit
     * @return array
     */
    public function getData($limit=5)
    {
        $limit && ($limit=5);
        $sql = "SELECT r.`customer_name`, r.`created`, rd.`name`,r.`product_id`,c.`face` FROM $this->table r 
LEFT  JOIN review_description rd  ON r.review_id = rd.review_id
LEFT JOIN `customer`  c   ON r.customer_id = r.customer_id
  WHERE r.`travel_rating`=100 AND r.status=1 AND c.active=1 ORDER BY r.`created` DESC LIMIT $limit";
       return $this->db->fetchAll($sql);
    }
}