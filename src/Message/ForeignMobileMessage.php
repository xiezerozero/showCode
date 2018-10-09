<?php

namespace Tourscool\Message;


class ForeignMobileMessage extends AbstractMessage
{
    const API_URL = "https://xxxxxxxxxx?action=send&userid=&account=&password=&mobile=&code=0&content=&sendTime=&extno=";

    const API_USERNAME = 'username';

    const API_PASSWORD = 'password';


    public function sendMessage($target, $content, $extra = [])
    {
        $sendUrl = sprintf(self::API_URL, self::API_USERNAME, self::API_PASSWORD, $target, $content) ;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$sendUrl);
        // 执行后不直接打印出来
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 不从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        $xmlResult = simplexml_load_string($output);//XML 字符串载入对象中
        $arr = json_encode($xmlResult);//取出的对象转成json 再转成数组
        $arr = json_decode($arr,true);
        if ($arr['returnstatus'] == 'Success') {
            return true;
        } else {
            return false;
        }
    }
}