<?php
if(!defined('ABSPATH')){exit;}

class YWSettings{
    private static $setting_name = 'yw_settings';

    public static function get_yw_config($key){
        return get_option(self::$setting_name)[$key];
    }

    public static function set_yw_config($key, $value){
        $setting = get_option(self::$setting_name);
        $setting[$key] = $value;
        update_option(self::$setting_name,$setting);
    }
}