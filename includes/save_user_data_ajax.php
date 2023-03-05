<?php

if (!defined('ABSPATH')) { exit; }

class YWSaveUserDataAjax
{
	public function __construct()
	{
		add_action('wp_ajax_yw_save_user_data_ajax', [$this, 'save_user_data_func']);
	}
	
	public function save_user_data_func()
	{
		$name_data = $_POST['yw_name'];
		$family_data = $_POST['yw_family'];
		$tel_data = $_POST['yw_tel'];
		$email_data = $_POST['yw_email'];
		$wp_nonce_data = $_POST['_wp_nonce'];
		
		if (wp_verify_nonce($wp_nonce_data, 'yw_save_user_data') >= 1) {
			
			if (empty($name_data) && empty($family_data) && empty($tel_data) && empty($email_data)) {
				wp_send_json([
					'success' => false,
					'message' => 'ورود تمامی اطلاعات الزامی است. مجدد تلاش کنید.',
				]);
			}
			
			$user = wp_get_current_user();
			
			update_user_meta($user->id , 'billing_first_name', $name_data);
			update_user_meta($user->id , 'billing_last_name', $family_data);
			update_user_meta($user->id , 'billing_phone', $tel_data);
			update_user_meta($user->id , 'billing_email', $email_data);
			
			wp_send_json([
				'success' => true,
				'message' => 'اطلاعات ذخیره شد.',
			]);
		}
		
		wp_send_json([
			'success' => false,
			'message' => 'مشکلی در ثبت اطلاعات شما پیش آمده. بعدا مجدد تلاش کنید.',
		]);
	}
}

new YWSaveUserDataAjax();