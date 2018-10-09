<?php

namespace Tourscool\Message;


interface IMessage
{
    /**
     * @description 生成验证码
     *
     * @param $length
     * @return mixed
     */
    public function makeCode($length);


    /**
     * @发送验证码
     * @param $target string 发送对象(手机号,邮箱地址...)
     * @param $content string 内容
     * @param array $extra 额外参数
     * @return mixed
     */
    public function sendMessage($target, $content, $extra = []);


}