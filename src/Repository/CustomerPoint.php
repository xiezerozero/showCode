<?php

namespace Tourscool\Repository;


class CustomerPoint extends Base
{

    public function addLoginPoint($customerId, $startDate, $endDate)
    {
        $sql = "SELECT * FROM `customer_point` WHERE customer_id = ? AND `comment` = 'EVERY_DAY_CHECKIN' AND `created` between ? AND ? ";
        $point = $this->db->fetchAssoc($sql, [$customerId, $startDate, $endDate]);

        if ($point) {
            return ;
        }
        $this->add('customer_point', [
            'order_id' => 0,
            'pending' => 5,
            'comment' => 'EVERY_DAY_CHECKIN',
            'type' => 'CH',
            'product_id' => 1,
            'status' => 2,
            'customer_id' => $customerId,
            // 外键限制, 目前写死的数据
            'user_id' => 2,
            'store_id' => 4,
        ]);
    }

}