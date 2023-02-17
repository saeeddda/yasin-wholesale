<?php
if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$all_categories = YWHelpers::get_all_product_categories();
$load_partners = YWSettings::get_yw_config('partners_percent');
$loaded_partner = '';

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

?>


<section class="yw-user-order">
    <form method="POST" action="" id="order_table_form" name="order_table_form">
        <div class="yw-header">
            <p>برای ثبت سفارش مقدار محصول درخواستی را وارد و روی دکمه ثبت کلیک کنید.</p>
            <button type="submit" class="button">ثبت سفارش</button>
        </div>
        <div class="yw-body">
            <div class="yw-categories" id="list">
                <?php foreach ($all_categories as $category):
                    if($category[0] == 'none') continue;
	
	                $Args = array(
		                'post_type' => 'product',
		                'product_cat' => $category[2],
		                'posts_per_page' => -1,
		                'meta_query' => array(
			                array(
				                'key' => '_stock_status',
				                'value' => 'instock'
			                ),
			                array(
				                'key' => '_price',
				                'value' => 0,
				                'compare' => '>'
			                ),
		                ),
	                );
	
	                $product_loop = new WP_Query($Args);
                    if($product_loop->have_posts()): ?>
                        <a href="#category_<?php echo $category[0]; ?>" class="cat-btn" rel="bookmark"><?php echo $category[1]; ?></a>
                <?php endif; endforeach; ?>
            </div>
            <?php foreach ($all_categories as $category):
                if($category[0] == 'none') continue;
                
	            $args = array(
		            'post_type' => 'product',
		            'product_cat' => $category[2],
		            'posts_per_page' => -1,
		            'orderby' => 'menu_order',
		            'order' => 'ASC',
		            'meta_query' => array(
			            'relation' => 'AND',
			            array(
				            'key' => '_stock_status',
				            'value' => 'instock',
				            'compare' => 'IN'
			            ),
			            array(
				            'key' => '_price',
				            'value' => 0,
				            'compare' => '>'
			            ),
		            ),
	            );
	
	            $product_loop2 = new WP_Query($args);
                
                if($product_loop2->have_posts()): ?>
                <div class="table-container" id="table_container">
                    <div class="button-container">
                        <button type="submit" class="button">ثبت سفارش</button>
                        <a href="#list" class="button outline">بازگشت به فهرست</a>
                    </div>
                    <table class="order-table">
                        <thead>
                            <tr style="background-color: #00c7c7; ">
                                <th colspan="7" id="category_<?php echo $category[0]; ?>"><?php echo $category[1]; ?></th>
                            </tr>
                            <tr>
                                <th>کد</th>
                                <th>نام محصول</th>
                                <th>توضیحات</th>
                                <th>تعداد در کارتن</th>
                                <th>قیمت</th>
                                <th>تصویر</th>
                                <th>تعداد درخواستی</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($product_loop2->have_posts() ): $product_loop2->the_post(); $product = wc_get_product(get_the_ID()); ?>
                                <tr>
                                    <td><?php echo $product->get_sku(); ?></td>
                                    <td><?php echo $product->get_name(); ?></td>
                                    <td><?php echo get_post_meta($product->get_id(), 'list_guarantee_field', true); ?></td>
                                    <td style="width: 110px;"><?php echo get_post_meta($product->get_id(), 'box_quantity_field', true); ?></td>
                                    <td style="width: 140px;">
                                        <?php
                                        $new_price = $product->get_price();
                            
                                        if($loaded_partner !== null && !empty($loaded_partner)){
                                            if ( !empty($loaded_partner['category']) && $loaded_partner['category'] != 'none') {
                                    
                                                $cat_ids = $product->get_category_ids();
                                    
                                                foreach ($cat_ids as $cat_id) {
                                                    if ($loaded_partner['category'] == $cat_id) {
                                                        $new_price = intval(YWHelpers::increase_percent(floatval($product->get_price()), intval($loaded_partner['list_percent'])));
                                                        break;
                                                    }
                                                }
                                            }
                                
                                            if ( !empty($loaded_partner['brand']) && $loaded_partner['brand'] != 'none') {
                                    
                                                $product_brand = get_the_terms($product->get_id(), 'product_brand')[0];
                                    
                                                if ($loaded_partner['brand'] == $product_brand->term_id) {
                                                    $new_price = intval(YWHelpers::increase_percent(floatval($product->get_price()), intval($loaded_partner['list_percent'])));
                                                }
                                            }
                                        }else {
                                            if (get_user_meta($user->id, 'list_percent', true) !== null && intval(get_user_meta($user->id, 'list_percent', true)) > 0) {
                                                $new_price = intval(YWHelpers::increase_percent(floatval($product->get_price()), intval(get_user_meta($user->id, 'list_percent', true))));
                                            } else {
                                                $cat_ids = $product->get_category_ids();
                                    
                                                foreach ($cat_ids as $cat_id) {
                                                    if (get_term_meta($cat_id, 'list_percent', true) !== null && intval(get_term_meta($cat_id, 'list_percent', true) > 0)) {
                                                        $new_price = intval(YWHelpers::increase_percent(floatval($product->get_price()), intval(get_term_meta($cat_id, 'list_percent', true))));
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                            
                                        if ($new_price != $product->get_price()) {
                                            echo $new_price . ' ' . get_woocommerce_currency_symbol();
                                        } else {
                                            echo $product->get_price() . ' ' . get_woocommerce_currency_symbol();
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <img src="<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'single-post-thumbnail')[0]; ?>" alt="">
                                    </td>
                                    <td>
                                        <input type="number" name="buy_quantity[<?php echo $product->get_id(); ?>]"
                                               id="buy_quantity_<?php echo $product->get_id(); ?>"
                                               min="<?php echo get_post_meta($product->get_id(), 'minimum_buy', true) !== null && !empty(get_post_meta($product->get_id(), 'minimum_buy', true)) ? get_post_meta($product->get_id(), 'minimum_buy', true) : '0'; ?>"
                                               step="<?php echo get_post_meta($product->get_id(), 'quantity_count_step', true) !== null && !empty(get_post_meta($product->get_id(), 'quantity_count_step', true)) ? get_post_meta($product->get_id(), 'quantity_count_step', true) : '1'; ?>"
                                               max="<?php echo get_post_meta($product->get_id(), 'maximum_buy', true) !== null && !empty(get_post_meta($product->get_id(), 'maximum_buy', true)) && get_post_meta($product->get_id(), 'maximum_buy', true) != '0' ? get_post_meta($product->get_id(), 'maximum_buy', true) : $product->get_stock_quantity(); ?>"
                                               value="<?php echo get_post_meta($product->get_id(), 'minimum_buy', true) !== null && !empty(get_post_meta($product->get_id(), 'minimum_buy', true)) ? get_post_meta($product->get_id(), 'minimum_buy', true) : '0'; ?>"
                                        >
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif;  wp_reset_postdata(); endforeach; ?>
        </div>
        <div class="yw-footer">
            <?php echo wp_nonce_field('yw_partner_buy','_wp_nonce')?>
            <button type="submit" class="button">ثبت سفارش</button>
        </div>
    </form>
</section>