<?php

use Tourscool\Repository\Customer;

require '../vendor/autoload.php';

class CustomerTest extends PHPUnit_Framework_TestCase
{
    /** @var \Tourscool\Repository\Customer */
    protected $customer;

    protected function setUp()
    {
        $this->customer = new Customer();
    }

    public function testValidatePassword()
    {
        $userName = '814004508@qq.com';
        $password = '11111111';
        $customer = $this->customer->findByAttributes('customer', ['email' => $userName]);
        $this->assertTrue(is_array($customer));

        $this->assertTrue(Customer::validatePassword($customer['password'], $password));
    }

    /**
     * @depends testValidatePassword
     */
    public function testResetPassWord()
    {
        $userType = 'email';
        $userName = '814004508@qq.com';
        $password = '123456';

        $r = $this->customer->resetPwdByAccount($userType, $userName, $password);
        $this->assertEquals(1, $r, '修改数据成功');

        $customer = $this->customer->findByAttributes('customer', ['email' => $userName]);

        $this->assertTrue(is_array($customer));

        $this->assertTrue(Customer::validatePassword($customer['password'], $password));

        // 测试之前密码是否是验证通不过
        $oldpassowrd = '11111111';
        $this->assertFalse(Customer::validatePassword($customer['password'], $oldpassowrd));

        // 修改为原始密码
        $this->customer->resetPwdByAccount($userType, $userName, $oldpassowrd);

        $customer = $this->customer->findByAttributes('customer', ['email' => $userName]);
        $this->assertTrue(Customer::validatePassword($customer['password'], $oldpassowrd));
    }


}
