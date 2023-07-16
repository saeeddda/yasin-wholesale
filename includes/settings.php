<?php
if(!defined('ABSPATH')){exit;}

class YWSettings{
    private static $setting_name = 'yw_settings';
    private static $default_options = [
        'active_partner_buy' => '',
        'not_allowed_title' => '',
        'not_allowed_text' => '',
        'not_allowed_button_link' => '',
        'selected_roles' => [
            'administrator',
            'shop_manager',
        ],
        'partners_percent' => []
    ];

    public static function get_yw_config($key){
        $options = get_option(self::$setting_name);

        if(!$options){
            return self::$default_options[$key];
        }

        return $options[$key];
    }

    public static function set_yw_config($key, $value){
        $setting = get_option(self::$setting_name);

        if(!$setting){
            $setting = self::$default_options;
            $setting[$key] = $value;
        }else{
            $setting[$key] = $value;
        }

        update_option(self::$setting_name, $setting);
    }
}