<?php

namespace Tourscool\Repository;

/**
 * @description 优惠券相关
 */
class Coupon extends Base
{
    // 优惠码已被使用
    const CODE_HAS_BEEN_USED = 10001;
    // 优惠券不可用
    const COUPON_IS_INVALID = 10002;
    // 优惠券还没生效
    const COUPON_NOT_STARTED = 10003;
    // 优惠券过期
    const COUPON_HAS_EXPIRE = 10004;



    // 优惠券是否可用
    const COUPON_STATUS_VALID = 1;
    // 优惠券是否不可用
    const COUPON_STATUS_INVALID = 0;
    //优惠码已使用
    const CODE_STATUS_USED = 1;
    //优惠码未使用
    const CODE_STATUS_UNUSED = 0;

    // 优惠券类型-优惠金额
    const COUPON_TYPE_AMOUNT = 'F';
    // 优惠券类型-折扣
    const COUPON_TYPE_DISCOUNT = 'P';
    /**
     * @description 检测平台优惠码, 可用返回平台优惠券信息
     */
    public function checkPlatformCode($platformCode)
    {
        $couponCode = $this->findByAttributes('platform_coupon_code', [
            'code' => $platformCode,
        ]);
        if (!$couponCode) {
            throw new Exception(self::RECORD_NOT_FOUND, '优惠码不存在');
        }
        if ($couponCode['status'] == self::CODE_STATUS_UNUSED) {
            throw new Exception(self::CODE_HAS_BEEN_USED, '优惠码已被使用');
        }
        $platFormCoupon = $this->find('platform_coupon', 'id', $couponCode['platform_coupon_id']);
        if (!$platFormCoupon || $platFormCoupon['status'] != self::COUPON_STATUS_VALID) {
            throw new Exception(self::COUPON_IS_INVALID, '优惠券不可用');
        }
        $now = time();
        if ($platFormCoupon['start_time'] > $now) {
            throw new Exception(self::COUPON_NOT_STARTED, '优惠券还没生效');
        }
        if ($platFormCoupon['expire_time'] < $now) {
            throw new Exception(self::COUPON_HAS_EXPIRE, '优惠券已过期');
        }
        return $platFormCoupon;
    }

    /**
     * @description 使用平台优惠码
     */
    public function usePlatformCode($platformCode, $orderId, $customerId)
    {
        $couponCode = $this->findByAttributes('platform_coupon_code', [
            'code' => $platformCode,
        ]);

        if (!$couponCode) {
            throw new Exception(self::RECORD_NOT_FOUND, '优惠码不存在');
        }
        $this->update('platform_coupon_code', [
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'status' => self::CODE_STATUS_USED,
            'updated' => time(),
        ], [
            'code' => $platformCode,
        ]);
    }

    /**
     * @description 获取某个用户所有有效期的优惠券
     */
    public function findCustomerCoupons($customerId)
    {
        $now = date('Y-m-d');
        $sql = "select * from `customer_coupon` where `customer_id` = ?
and `start_date` <= ? and `expire_date` >= ?";
        return $this->db->fetchAll($sql, [$customerId, $now, $now]);
    }

    /**
     * @description 根据IDS获取coupons 信息
     * @param array $couponIds
     * @return array
     */
    public function coupons(array $couponIds)
    {
        $couponIds = array_filter($couponIds, 'is_numeric');
        if (empty($couponIds)) {
            return [];
        }
        $couponIdString = implode(',', $couponIds);
        $sql = "select * from `coupons` where `id` in ({$couponIdString})";
        return $this->db->fetchAll($sql);
    }

    /**
     * @description 生成平台优惠码数据
     * @param $couponId
     * @param $nums
     */
    public function makePlatformCoupon($couponId, $nums)
    {
        for ($i = 0; $i < $nums; $i ++) {
            $code = $this->makeCode();
            $this->db->insert('platform_coupon_code', [
                'code' => $code,
                'platform_coupon_id' => $couponId,
            ]);
        }
    }

    private function makeCode()
    {
        $code = '';
        for ($i = 0; $i < 10; $i ++) {
            $index = rand(1, 3);
            if ($index == 1) {
                $code .= rand(0, 9);
            } elseif ($index == 2) {    //A-Z
                $code .= chr(rand(65, 90));
            } else {    //a-z
                $code .= chr(rand(97, 122));
            }
        }
        return $code;
    }

}