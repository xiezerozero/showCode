<?php
/**
 * @author: Libo
 * @since: 2016/6/20
 * Time: 16:54
 */

namespace Tourscool\Config;




use Tourscool\Config\Exception\ConfigException;

/**
 * 通用读取配置
 *
 * ```
 * \BaodConfig\Config::get($configName,$fileName = '.env');
 * 如：\BaodConfig\Config::get('DB_HOST'); 与 \BaodConfig\Config::get('DB_HOST','.env');
 * 如：\BaodConfig\Config::get('DB_HOST','pc.env');
 *
 * 本包提倡配置文件存放在/mnt/home/webroot/webconfig目录下
 *
 * 读取配置项按以下顺序查找，一旦找到，则返回配置值，不再继续查找。如果流程中查找完后仍未读取到配置项，则抛异常
 * 1、查找本项目中与composer.json同级的指定配置文件（不指定默认为 .env）
 * 2、如果存在变量$_ENV['BAOBASE_CONFIGS_PATH']，则查找$_ENV['BAOBASE_CONFIGS_PATH']路径下指定配置文件（不指定默认为 .env）
 * 3、查找/mnt/home/webroot/webconfig目录下指定文件（不指定默认为 .env）
 * 4、抛异常
 * ```
 * Class Config
 * @package BaodConfig
 */
class Config
{
    const DEFAULTPATH = '/www/webconfig';

    /**
     * 读取配置
     *
     * @param string $configName 配置项
     * @param string $fileName 配置文件名 默认.env
     * @return $config_value
     * @throws ConfigException
     */

    static function get($configName,$fileName = '.env')
    {
        if(isset($_ENV[$configName])){
            return $_ENV[$configName];

        }
        $configPath = self::DEFAULTPATH;
        $custompath = dirname(dirname(dirname(dirname(__DIR__))));
        if(file_exists($custompath."/".$fileName)){
            $dotenvPath = new \Dotenv\Dotenv($custompath,$fileName);
            $dotenvPath->load();
            $configPath = isset($_ENV['BAOBASE_CONFIGS_PATH'])?$_ENV['BAOBASE_CONFIGS_PATH']:$configPath;
        }

        if(!isset($_ENV[$configName])){

            $dotenv = new \Dotenv\Dotenv($configPath,$fileName);
            $dotenv->load();
        }
        if(isset($_ENV[$configName])){
            return $_ENV[$configName];

        }else{
            throw new ConfigException(ConfigException::CONFIG_EXISTS,"$configPath/.env : $configName not find");
        }
    }
}