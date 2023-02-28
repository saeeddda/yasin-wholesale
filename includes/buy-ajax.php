<?php

if(!defined('ABSPATH')){exit;}

class YWBuyAjax{
    public function __construct()
    {
        add_action('wp_ajax_yw_partner_buy_ajax', [$this, 'partner_buy_func']);
    }

	public function partner_buy_func(){
		session_start();
        $data = [];
        parse_str($_POST['data_collection'], $data);

        if(wp_verify_nonce( $data['_wp_nonce'], 'yw_partner_buy' ) >= 1 ){
			
            $user = wp_get_current_user();
            $new_order = wc_create_order();
			$payment_methods = WC()->payment_gateways()->get_available_payment_gateways();
            $load_partners = YWSettings::get_yw_config('partners_percent');
            $loaded_partner = [];

            if($load_partners !== null && count($load_partners) > 0) {
                foreach ($load_partners as $partner_id => $partner_value) {
                    if ($user->id == $partner_id) {
                        $loaded_partner = [
                            'partner_id' => $partner_id,
                            'list_percent' => $partner_value['list_percent'],
                            'discount_percent' => $partner_value['discount_percent'],
                        ];

                        break;
                    }
                }
            }

            foreach ($data['buy_quantity'] as $key => $value) {
                if($value != 0 || !empty($value)) {
                    $product = wc_get_product($key);
                    $cat_ids = $product->get_category_ids();
                    $new_price = $product->get_price();

                    if($loaded_partner != null && !empty($loaded_partner)){

                        if ( !empty($loaded_partner['category']) && $loaded_partner['category'] != 'none') {

                            $cat_ids = $product->get_category_ids();

                            foreach ($cat_ids as $cat_id) {
                                if ($loaded_partner['category'] == $cat_id) {

                                    if ( !empty($loaded_partner['list_percent']) && intval($loaded_partner['list_percent']) > 0) {
                                        $new_price = intval(YWHelpers::increase_percent(floatval($product->get_price()), intval($loaded_partner['list_percent'])));

                                        if (!empty($loaded_partner['discount_percent']) && intval($loaded_partner['discount_percent']) > 0) {
                                            $new_price = intval(YWHelpers::decrease_percent(floatval($new_price), intval($loaded_partner['discount_percent'])));
                                        }
                                    }
                                    break;
                                }
                            }
                        }

                        if ( !empty($loaded_partner['brand']) && $loaded_partner['brand'] != 'none') {

                            $product_brand = get_the_terms($product->get_id(), 'product_brand')[0];

                            if ($loaded_partner['brand'] == $product_brand->term_id) {
                                if ( !empty($loaded_partner['list_percent']) && intval($loaded_partner['list_percent']) > 0) {
                                    $new_price = intval(YWHelpers::increase_percent(floatval($product->get_price()), intval($loaded_partner['list_percent'])));

                                    if (!empty($loaded_partner['discount_percent']) && intval($loaded_partner['discount_percent']) > 0) {
                                        $new_price = intval(YWHelpers::decrease_percent(floatval($new_price), intval($loaded_partner['discount_percent'])));
                                    }
                                }
                            }
                        }
                    }else {
                        if (get_user_meta($user->id, 'list_percent', true) !== null && intval(get_user_meta($user->id, 'list_percent', true)) > 0) {
                            $new_price = intval(YWHelpers::increase_percent(floatval($product->price), intval(get_user_meta($user->id, 'list_percent', true))));

                            if (get_user_meta($user->id, 'discount_percent', true) !== null && intval(get_user_meta($user->id, 'discount_percent', true) > 0)) {
                                $new_price = intval(YWHelpers::decrease_percent(floatval($new_price), intval(get_user_meta($user->id, 'discount_percent', true))));
                            }
                        } else {
                            foreach ($cat_ids as $cat_id) {
                                if (get_term_meta($cat_id, 'list_percent', true) !== null && intval(get_term_meta($cat_id, 'list_percent', true) > 0)) {
                                    $new_price = intval(YWHelpers::increase_percent(floatval($product->get_price()), intval(get_term_meta($cat_id, 'discount_percent', true))));

                                    if (get_term_meta($cat_id, 'discount_percent', true) !== null && intval(get_term_meta($cat_id, 'discount_percent', true) > 0)) {
                                        $new_price = intval(YWHelpers::decrease_percent(floatval($new_price), intval(get_term_meta($cat_id, 'discount_percent', true))));
                                    }

                                    break;
                                } else {
                                    if (get_term_meta($cat_id, 'discount_percent', true) !== null && intval(get_term_meta($cat_id, 'discount_percent', true) > 0)) {
                                        $new_price = intval(YWHelpers::decrease_percent(floatval($product->get_price()), intval(get_term_meta($cat_id, 'discount_percent', true))));
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    if ($new_price != $product->get_price()) {
                        $product->set_price($new_price);
                    }

                    $new_order->add_product($product, $value);
                }
            }

            if($new_order->get_item_count() > 0) {
	            try {
		            $new_order->set_customer_id($user->id);
		            $new_order->set_address(array(
			            'first_name' => get_user_meta($user->id, 'billing_first_name', true),
			            'last_name' => get_user_meta($user->id, 'billing_last_name', true),
			            'company' => get_user_meta($user->id, 'billing_company', true),
			            'email' => get_user_meta($user->id, 'billing_email', true),
			            'phone' => get_user_meta($user->id, 'billing_phone', true),
			            'address_1' => get_user_meta($user->id, 'billing_address_1', true),
			            'address_2' => get_user_meta($user->id, 'billing_address_2', true),
			            'city' => get_user_meta($user->id, 'billing_city', true),
			            'state' => get_user_meta($user->id, 'billing_state', true),
			            'postcode' => get_user_meta($user->id, 'billing_postcode', true),
			            'country' => get_user_meta($user->id, 'billing_country', true)
		            ));
		            try {
			            $new_order->set_payment_method($payment_methods['cheque']);
		            }catch (WC_Data_Exception $ex){}
		            $total = $new_order->calculate_totals();
		            $new_order->add_order_note('سفارش افزوده شده از پنل همکار');
					$new_order->set_status('processing');
		            try {
			            $new_order->payment_complete();
		            }catch (WC_Data_Exception $ex){}
					$new_order->update_status('processing', ' ', true);
					
		            $_SESSION['yw_message'] = "سفارش شمار ثبت شد. شماره سفارش : {$new_order->get_id()} مبلغ کل سفارش : {$total} ";
		            wp_send_json_success("سفارش شمار ثبت شد. شماره سفارش : {$new_order->get_id()} مبلغ کل سفارش : {$total} ");
	            }catch (WC_Data_Exception $exception){
		            $_SESSION['yw_message'] = $exception->getMessage();
		            wp_send_json_error($exception->getMessage() );
	            }
            }else{
				wp_delete_post($new_order->get_id(), true);
	            $_SESSION['yw_message'] = 'ورود حداقل یک مقدار الزامی است';
                wp_send_json_error('ورود حداقل یک مقدار الزامی است');
            }
        }
		$_SESSION['yw_message'] = 'مشکلی در ثبت سفارش شما پیش آمده. بعدا مجدد تلاش کنید.';
        wp_send_json_error('مشکلی در ثبت سفارش شما پیش آمده. بعدا مجدد تلاش کنید.');
    }
} new YWBuyAjax();