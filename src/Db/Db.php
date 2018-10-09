<?php
/**
 * @author: Administrator
 * @since: 2016/6/2
 * Time: 18:28
 */

namespace Tourscool\Db;


use Tourscool\Config\Config;

class Db
{
    const PARAM_INT_ARRAY = \Doctrine\DBAL\Connection::PARAM_INT_ARRAY;
    const PARAM_STR_ARRAY = \Doctrine\DBAL\Connection::PARAM_STR_ARRAY;
    
    private $conn = NULL;//连接对象 (private防止实例化)
    //构造函数连接数据库(private防止实例化)
    private function __construct()
    {
        $config = new \Doctrine\DBAL\Configuration();
        $dbConfig = array(
            'dbname'    => Config::get('DB_DATABASE'),
            'user'      => Config::get('DB_USERNAME'),
            'password'  => Config::get('DB_PASSWORD'),
            'host'      => Config::get('DB_HOST'),
            'port'      => Config::get('DB_PORT'),
            'driver'    => 'pdo_mysql',
        );
        $this->conn = \Doctrine\DBAL\DriverManager::getConnection($dbConfig, $config);
        $this->conn->query("set names utf8");
    }
    //防止克隆
    private function __clone()
    {}
    //只能通过静态方法获得该类的对象(单例模式)

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public static function getInstance()
    {
        static $obj = NULL;
        if($obj == NULL)
        {
            $obj_tmp = new Db();
            $obj = $obj_tmp->conn;
        }
        return $obj;
    }
}