<?php

namespace Tourscool\Request;


trait Request
{
    /** @var  DocParser */
    protected static $p;

    /**
     * @return DocParser
     */
    protected static function docParser()
    {
        if (self::$p == null) {
            self::$p = new DocParser();
        }
        return self::$p;
    }

    protected function getParams($separator = ' ')
    {
        $methodInfo = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
        $reflectionMethod = new \ReflectionMethod($methodInfo['class'], $methodInfo['function']);
        $parser = self::docParser();
        $s = $parser->parse($reflectionMethod->getDocComment());
        $params = [];
        if (isset($s['required'])) {
            foreach ($s['required'] as $required) {
                $requiredInfo = array_slice(explode($separator, $required), 0, 4);
                $requiredInfo = array_map('trim', $requiredInfo);
                $params[$requiredInfo[0]] = call_user_func_array([$this, 'getRequired'], $requiredInfo);
            }
        }
        if (isset($s['optional'])) {
            foreach ($s['optional'] as $optional) {
                $optionalInfo = array_slice(explode($separator, $optional), 0, 4);
                $optionalInfo = array_map('trim', $optionalInfo);
                $params[$optionalInfo[0]] = call_user_func_array([$this, 'getOptional'], $optionalInfo);
            }
        }
        if (config('app.debug')) {
            $logsParams = $params + ['uri' => $_SERVER['REQUEST_URI']];
            error_log(
                '['.date('Y-m-d H:i:s') . ']: ' . var_export($logsParams, true) . PHP_EOL,
                3,
                app()->storagePath() . '/logs/params_'.date('Ynd') . '.log'
            );
        }
        return $params;
    }

    /**
     * get required param checked by filter and throw exception if not valid
     *
     * @param $paramName
     * @param string $filter
     * @param string $errorMsg
     *
     * @param array $options
     *
     * @return array|bool|mixed|string
     */
    protected function getRequired($paramName, $filter = 'string', $errorMsg = '', $options = [])
    {
        $result = $this->getRequestField($paramName, $filter, $options);

        if ($result === null || $result === false) {
            empty($errorMsg) && $errorMsg = "{$paramName} 参数不正确";
            throw new \InvalidArgumentException($errorMsg);
        }
        return $result;
    }

    /**
     * get optional param checked by filter and return default if not valid
     *
     * @param $paramName
     * @param string $filter
     * @param null $default
     * @param array $options
     *
     * @return array|bool|mixed|null|string
     */
    protected function getOptional($paramName, $filter = 'string', $default = '', $options = [])
    {
        $result = $this->getRequestField($paramName, $filter, $options);

        return $result === null || $result === false ? $default : $result;
    }

    /**
     * get param and checked by filter
     *
     * @param $paramName
     * @param $filter
     * @param array $options
     *
     * @return array|bool|mixed|string
     */
    protected function getRequestField($paramName, $filter, $options = [])
    {
        $result = $_REQUEST[$paramName];
        if (null !== $result && false !== $result) {
            if (is_callable($filter)) {
                return call_user_func($filter, $result);
            }
            is_string($filter) && $filter = [$filter];
            if (!isset($options['no_sanitize']) && !in_array('string', $filter)) {  // 是否需要过滤标签(默认都加上过滤标签)
                array_unshift($filter, 'string');
            }
            $filter = array_filter($filter);
            foreach ($filter as $f) {
                if (null !== $result && false !== $result) {
                    switch ($f) {
                        case 'notempty':
                            is_string($result) && $result = trim($result);
                            $result = empty($result) ? false : $result;
                            break;
                        // 非空字符串
                        case 'notemptystring':
                            $result = $result === "" ? false : $result;
                            break;
                        case 'int':
                            $result = filter_var($result, FILTER_VALIDATE_INT);
                            break;
                        case 'float':
                            $result = filter_var($result, FILTER_VALIDATE_FLOAT);
                            break;
                        case 'email':
                            $result = filter_var($result, FILTER_VALIDATE_EMAIL);
                            break;
                        case 'ip':
                            $result = filter_var($result, FILTER_VALIDATE_IP);
                            break;
                        case 'url':
                            $result = filter_var($result, FILTER_VALIDATE_URL);
                            break;
                        case 'uint':
                            $result = filter_var($result, FILTER_VALIDATE_INT);
                            $result = false !== $result ? ($result >= 0 ? $result : false): false;
                            break;
                        case 'trim':
                            $result = trim($result);
                            break;
                        case 'mobile':
                            $match = preg_match('/^1\d{10}$/', $result);
                            $result = $match ? $result : false;
                            break;
                        case 'qq':
                            $match = preg_match('/^\d{5,12}$/', $result);
                            $result = $match ? $result : false;
                            break;
                        case 'in':
                            if (isset($options['in'])) {
                                $result = in_array($result, $options['in']) ? $result : null;
                            }
                            break;
                        case 'datetime':
                            if (false === strtotime($result)) {
                                $result = false;
                            }
                            break;
                            // confirm 与指定的value对比是否一致
                        case 'confirm':
                            if (!isset($options['value']) || $result != $options['value']) {
                                $result = false;
                            }
                            break;
                        case 'string':
                            if (is_scalar($result)) {
                                $result = strip_tags($result);
                            }
                            break;
                        // add more
                    }

                }
            }

            return $result;
        }
    }


}