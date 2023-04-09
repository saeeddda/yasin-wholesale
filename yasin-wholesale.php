<?php

/**
 * Plugin Name: Yasin wholesale
 * Plugin URI: https://saeedna.ir/
 * Description: Wholesale to authorised partners
 * Version: 1.5
 * Author: Saeed Noori
 * Author URI: https://saeedna.ir/
 */

if(!defined('ABSPATH')){exit;}

class YasinWholesale{
    public function __construct()
    {
        $this->init_define();

        if(is_admin()){
            add_action('admin_enqueue_scripts', [$this, 'admin_styles']);
        }else{
            add_action('wp_enqueue_scripts', [$this, 'user_styles']);
        }

        require_once(YW_DIR . 'includes/settings.php');
        require_once(YW_DIR . 'includes/helpers.php');
        require_once(YW_DIR . 'includes/buy-ajax.php');
        require_once(YW_DIR . 'includes/save_user_data_ajax.php');
		
        if(is_admin()) {
            require_once(YW_DIR . 'includes/admin_menu.php');
        }else{
            require_once(YW_DIR . 'includes/user_menu.php');
        }
    }

    private function init_define(){
        if(!defined('YW_DIR')) define('YW_DIR',plugin_dir_path(__FILE__));
        if(!defined('YW_URL')) define('YW_URL',plugin_dir_url(__FILE__));
        if(!defined('YW_ASSETS')) define('YW_ASSETS', YW_URL . trailingslashit('assets') );
    }

    public function admin_styles(){
        wp_enqueue_style('yw-admin-style',YW_ASSETS . 'css/admin.css');

//        wp_enqueue_script('yw-admin-script', YW_ASSETS . 'js/admin.js');
    }

    public function user_styles(){
	    wp_enqueue_style('yw-user-style',YW_ASSETS . 'css/sweetalert2.min.css');
	    wp_enqueue_style('yw-sweetalert-style',YW_ASSETS . 'css/user.css');

        wp_enqueue_script('yw-sweetalert-script', YW_ASSETS . 'js/sweetalert2.min.js');
    }
}

new YasinWholesale();