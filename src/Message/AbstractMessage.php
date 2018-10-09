<?php

namespace Tourscool\Message;


abstract class AbstractMessage implements IMessage
{

    public function makeCode($length)
    {
        $codeStr = '';
        for ($i = 0; $i < $length; $i++) {
            $codeStr .= mt_rand(0, 9);
        }

        return $codeStr;
    }

}