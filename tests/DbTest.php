<?php

use Tourscool\Repository\Customer;

require '../vendor/autoload.php';

class DbTest extends PHPUnit_Framework_TestCase
{
    /** @var \Tourscool\Repository\Customer */
    private $customer;

    public function setUp()
    {
        $this->customer = new Customer();
    }

    public function testFind()
    {

        $res = $this->customer->find('customer', 'customer_id',929);

        $this->assertNotFalse($res);
        $this->assertEquals("814004508@qq.com", $res['email'], '验证邮箱是否一致,获取的数据是否是指定的那条');

        $notFound = $this->customer->find('customer', 'customer_id', 99999999);

        $this->assertFalse($notFound);
    }

    public function testCount()
    {
        // 有这条记录
        $c = $this->customer->count('customer', [
            'email' => '814004508@qq.com',
        ]);

        $this->assertEquals(1, $c);

        // 没得这条记录
        $c2 = $this->customer->count('customer', [
            'email' => '8140045aaaa08@qq.com',
        ]);

        $this->assertEquals(0, $c2);
    }

    /**
     * @depends testCount
     */
    public function testFindAll()
    {
        $count = $this->customer->count('customer', ['email' => 'asdsadasd']);

        $this->assertEquals(0, $count);

        $res = $this->customer->findAll('customer', ['email' => 'asdsadasd']);

        // 找不到数据也是返回的数据
        $this->assertTrue(is_array($res));

        $this->assertEquals($count, count($res));
    }
}
