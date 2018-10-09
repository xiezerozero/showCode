<?php

namespace Tourscool\Repository;

/**
 * @description 顾客业务
 * @package Tourscool\Repository
 */
class Customer extends Base
{

    // 顾客已经登录(登录接口会检测是否已经登录)
    const CUSTOMER_ALREADY_LOGIN = 201;
    // 用户登录用户名不合法
    const CUSTOMER_LOGIN_USERNAME_INVALID = 202;
    // 密码错误
    const CUSTOMER_PASSWORD_ERROR = 203;
    // 用户动态登录发送验证码失败
    const CUSTOMER_DYNAMIC_LOGIN_SEND_FAIL = 204;
    // 用户动态验证码过期
    const CUSTOMER_CODE_EXPIRE = 205;
    // 用户注册时邮箱已被注册
    const CUSTOMER_REGISTER_EMAIL_EXIST = 206;
    // 用户注册时电话已被注册
    const CUSTOMER_REGISTER_PHONE_EXIST = 207;
    // 用户注册时密码和确认密码不一致
    const CUSTOMER_PASS_NOT_EQUAL = 208;
    // 用户未激活
    const CUSTOMER_NOT_ACTIVE = 209;

    public function generatePwd($plainPassword)
    {
        $salt = substr(md5(uniqid()) , 0 ,2);
        return md5($salt . $plainPassword) . ':' . $salt ;
    }

    /**
     * @description 验证密码是否匹配
     * @param string $encrypt 加密密码
     * @param string $plain 明文密码
     * @return bool
     */
    public static function validatePassword($encrypt, $plain)
    {
        $salt = substr($encrypt , -2);
        return $encrypt == md5($salt . $plain) . ':'. $salt;
    }

    /**
     * @description 检测邮箱或者手机号是否存在
     * @param $type
     * @param $value
     * @return bool
     */
    public function checkEmailOrPhoneExist($type, $value)
    {
        if ($type == 'email') {
            $data = $this->findByAttributes('customer', ['email' => $value]);
        } else {
            $data = $this->findByAttributes('customer', ['phone' => $value]);
        }

        return $data !== false;
    }

    /**
     * @description 注册用户
     * @param array $data
     * @return int >0 注册成功
     */
    public function doRegister($data = [])
    {
        if (isset($data['password'])) {
            $data['password'] = self::generatePwd($data['password']);
        }
        // 激活
        $data['active'] = 1;
        $data['default_address_id'] = 0;

        return $this->add('customer', $data);
    }

    /**
     * @description 用户登录
     * @param $username
     * @param $password
     * @param bool $usePassword
     * @throws \Tourscool\Repository\Exception
     */
    public function doLogin($username, $password, $usePassword = true)
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $customer = $this->findByAttributes('customer', ['email' => $username]);
        } else {
            $customer = $this->findByAttributes('customer', ['phone' => $username]);
        }
        if (!$customer) {
            throw new Exception(self::RECORD_NOT_FOUND, "找不到用户");
        }
        if ($customer['active'] != 1) {
            throw new Exception(self::CUSTOMER_NOT_ACTIVE, '用户未激活');
        }
        if ($usePassword) {
            if (!self::validatePassword($customer['password'], $password)) {
                throw new Exception(self::CUSTOMER_PASSWORD_ERROR, '密码错误');
            }
        }
        // 更新customer_info数据
        $info = $this->findByAttributes('customer_info', ['customer_id' => $customer['customer_id']]);

        if ($info) {
            $this->update('customer_info', ['number_of_logins' => $info['number_of_logins'] + 1], [
                'customer_id' => $customer['customer_id'],
            ]);
        } else {
            $now = date('Y-m-d H:i:s');
            $this->add('customer_info', [
                'created' => $now,
                'last_updated' => $now,
                'last_login' => $now,
                'idle_mail_send_date' => $now,
                'number_of_logins' => 1,
                'customer_id' => $customer['customer_id'],
            ]);
        }
        // 增加登录送积分
        $customerPoint = new CustomerPoint();
        $customerPoint->addLoginPoint($customer['customer_id'], date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'));

        return $customer['customer_id'];
    }

    /**
     * @description 重置密码
     * @param $userType
     * @param $userName
     * @param $password
     * @return int
     */
    public function resetPwdByAccount($userType, $userName, $password)
    {
        $encrypt = self::generatePwd($password);
        if ($userType == 'email') {
            return $this->db->update('customer', [
                'password' => $encrypt,
            ], [
                'email' => $userName,
            ]);
        } else {
            return $this->db->update('customer', [
                'password' => $encrypt,
            ], [
                'phone' => $userName,
            ]);
        }
    }




}