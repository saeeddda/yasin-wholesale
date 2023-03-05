<?php
if(!defined('ABSPATH')){exit;}

class YWUserMenu{
    public function __construct()
    {
        if(YWSettings::get_yw_config('active_partner_buy') !== null && YWSettings::get_yw_config('active_partner_buy') == 'on') {
            if(YWSettings::get_yw_config('selected_roles') !== null) {
                $user_roles = wp_get_current_user()->roles;
                $selected_roles = (array)YWSettings::get_yw_config('selected_roles');

                add_action('init', [$this, 'add_wp_endpoint']);
                add_filter('woocommerce_account_menu_items', [$this, 'add_account_menu_item'], 40);

                if (count(array_intersect( $selected_roles, $user_roles )) > 0) {
                    add_action('woocommerce_account_partner-buy_endpoint', [$this, 'account_menu_item_content']);
                    add_action('wp_footer', [$this, 'add_script'], 999);

                    add_filter('woocommerce_product_get_price', [$this, 'global_pricing_per_user'], 90, 2 );
                    add_filter('woocommerce_product_get_regular_price', [$this, 'global_pricing_per_user'], 90, 2 );
                }else{
                    add_action('woocommerce_account_partner-buy_endpoint', [$this, 'account_menu_item_content_not_allowed']);
                }
            }
        }
    }

    public function add_account_menu_item($menu_links){
        $menu_links = array_slice( $menu_links, 0, 1, true )
            + array( 'partner-buy' => 'ثبت سفارش همکار' )
            + array_slice( $menu_links, 1, NULL, true );
        return $menu_links;
    }

    public function  add_wp_endpoint(){
        add_rewrite_endpoint( 'partner-buy', EP_PAGES );
    }

    public function account_menu_item_content(){
        include_once YW_DIR . 'templates/user/order-page.php';
    }

    public function account_menu_item_content_not_allowed(){
        include_once YW_DIR . 'templates/user/not-allowed.php';
    }

    public function add_script(){
        ?>
        <script type="text/javascript">
            jQuery(document.body).on('submit', '#order_table_form', function(e){
                e.preventDefault();

                jQuery('#table_container').addClass('disabled');
				jQuery.ajax({
                    type: "POST",
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    data: {
                        action : "yw_partner_buy_ajax",
                        yw_data_collection : jQuery("#order_table_form").serialize()
                    },
                    success: function (response){
                        jQuery('#table_container').removeClass('disabled');
						alert(response.data);
						setTimeout(function(){
							window.location.reload();
						},1000);
                    },
                    dataType: 'json'
                });
            });
        </script>
        <?php
    }

    public function global_pricing_per_user( $price, $product ) {
            $user = wp_get_current_user();
            $load_partners = YWSettings::get_yw_config('partners_percent');
            $loaded_partner = '';
            $new_price = $price;

            if($load_partners !== null && count($load_partners) > 0) {
                foreach ($load_partners as $partner_id => $partner_value) {
                    if ($user->id == $partner_id) {
                        $loaded_partner = [
                            'partner_id' => $partner_id,
                            'category' => $partner_value['category'],
                            'brand' => $partner_value['brand'],
                            'list_percent' => $partner_value['list_percent'],
                            'discount_percent' => $partner_value['discount_percent'],
                        ];

                        break;
                    }
                }
            }

            if($loaded_partner !== null && !empty($loaded_partner)){
                if ( !empty($loaded_partner['category']) && $loaded_partner['category'] != 'none') {

                    $cat_ids = $product->category_ids;

                    foreach ($cat_ids as $cat_id) {
                        if ($loaded_partner['category'] == $cat_id) {
                            $new_price = intval(YWHelpers::increase_percent(floatval($price), intval($loaded_partner['list_percent'])));
                            break;
                        }
                    }
                }

                if ( !empty($loaded_partner['brand']) && $loaded_partner['brand'] != 'none') {

                    $product_brand = get_the_terms($product->id, 'product_brand')[0];

                    if ($loaded_partner['brand'] == $product_brand->term_id) {
                        $new_price = intval(YWHelpers::increase_percent(floatval($price), intval($loaded_partner['list_percent'])));
                    }
                }

                return $new_price;
            }else {
                if (get_user_meta($user->id, 'list_percent', true) !== null && intval(get_user_meta($user->id, 'list_percent', true)) > 0) {
                    return intval(YWHelpers::increase_percent(floatval($price), intval(get_user_meta($user->id, 'list_percent', true))));
                } else {
                    $cat_ids = $product->category_ids;

                    foreach ($cat_ids as $cat_id) {
                        if (get_term_meta($cat_id, 'list_percent', true) !== null && intval(get_term_meta($cat_id, 'list_percent', true) > 0)) {
                            return intval(YWHelpers::increase_percent(floatval($price), intval(get_term_meta($cat_id, 'list_percent', true))));
                        }
                    }
                }
            }
        return $price;
    }
} new YWUserMenu();