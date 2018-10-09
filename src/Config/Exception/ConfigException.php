<?php
/**
 * 配置相关的异常
 * User: Libo
 * Date: 2016/7/1
 * Time: 14:00
 */
namespace Tourscool\Config\Exception;


use Exception;

class ConfigException extends \Exception
{
    /**
     * 没找到配置
     */
    const CONFIG_EXISTS                     = 1;
    /**
     * 操作消息文件不存在
     */
    const OPERATION_MSG_FILE_NOT_EXISTS     = 2;
    /**
     * 操作消息键不存在
     */
    const OPERATION_MSG_KEY_NOT_EXISTS      = 3;

    public function __construct($code = 0,$message=null, Exception $previous = null)
    {
        $called_class = get_called_class();
        if($message===null){
            $message = $called_class.$code;
        }
        $return  = array(
            'message'   => $message,
            'namespace' => $this->getFirstNameSpace(get_called_class()),
            'code'      => $code,
            'class'     => $called_class,
        );
        parent::__construct(json_encode($return), $code, $previous);
    }
    private function getFirstNameSpace($class_name){
        $class          = new \ReflectionClass($class_name);
        //获取命名空间
        $namespace      = $class->getNamespaceName();
        //分割获取命名空间
        $arr_namespace  = $namespace ? explode('\\',$namespace):array("");
        $first_namespace= array_shift($arr_namespace);
        return $first_namespace;
    }
}