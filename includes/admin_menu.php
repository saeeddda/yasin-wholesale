<?php
if(!defined('ABSPATH')){exit;}

class YWAdminMenu{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);

        add_action( 'woocommerce_product_options_general_product_data', [$this, 'add_yw_min_max_fields']);
        add_action( 'woocommerce_process_product_meta', [$this, 'save_yw_min_max_fields']);

        add_action( 'show_user_profile', [$this, 'add_profile_fields'] );
        add_action( 'edit_user_profile', [$this, 'add_profile_fields'] );
        add_action( 'personal_options_update', [$this, 'save_profile_fields'] );
        add_action( 'edit_user_profile_update', [$this, 'save_profile_fields'] );

        add_action( 'product_cat_add_form_fields', [$this, 'percent_taxonomy_add_new_meta_field'], 10, 2 );
        add_action( 'product_cat_edit_form_fields', [$this, 'percent_taxonomy_edit_meta_field'], 10, 2 );
        add_action( 'edited_product_cat', [$this, 'save_taxonomy_percent_meta'] );
        add_action( 'create_product_cat', [$this, 'save_taxonomy_percent_meta'] );

        add_action('admin_footer', [$this, 'add_partner_scripts'], 999);

        add_action('wp_ajax_yw_get_data_ajax', [$this, 'yw_get_data_func']);
    }

    public function  register_menu(){
        add_submenu_page(
            'woocommerce',
            'فروش همکار',
            'فروش همکار',
            'manage_options',
            'yasin_wholesale_options',
            [$this,'general_menu_page'],
        );

        add_submenu_page(
            'woocommerce',
            'درصدهای همکاران',
            'درصدهای همکاران',
            'manage_options',
            'yasin_wholesale_percent_options',
            [$this,'percent_menu_page'],
        );
    }

    public function  general_menu_page(){

        if(isset($_POST['yw_settings_form'])){

            $active_partner_buy = isset($_POST['yw_settings']['active_partner_buy']) && $_POST['yw_settings']['active_partner_buy'] == 'on' ? 'on' : 'off';
            $not_allowed_title = isset($_POST['yw_settings']['not_allowed_title']) && !empty($_POST['yw_settings']['not_allowed_title']) ? $_POST['yw_settings']['not_allowed_title'] : '';
            $not_allowed_text = isset($_POST['yw_settings']['not_allowed_text']) && !empty($_POST['yw_settings']['not_allowed_text']) ? $_POST['yw_settings']['not_allowed_text'] : '';
            $not_allowed_button_link = isset($_POST['yw_settings']['not_allowed_button_link']) && !empty($_POST['yw_settings']['not_allowed_button_link']) ? $_POST['yw_settings']['not_allowed_button_link'] : '';

            $selected_roles = [];

            if(isset($_POST['yw_settings']['roles'])){
                $raw_roles = $_POST['yw_settings']['roles'];

                if($raw_roles !== null) {
                    foreach ($raw_roles as $role_id => $role_value) {
                        if ($role_value == 'on') {
                            $selected_roles[] = $role_id;
                        }
                    }
                }
            }

            YWSettings::set_yw_config('active_partner_buy', $active_partner_buy);
            YWSettings::set_yw_config('not_allowed_title', $not_allowed_title);
            YWSettings::set_yw_config('not_allowed_text', $not_allowed_text);
            YWSettings::set_yw_config('not_allowed_button_link', $not_allowed_button_link);
            YWSettings::set_yw_config('selected_roles', $selected_roles);

            add_action('admin_notices', [$this, 'save_notice']);
        }

        include_once YW_DIR . 'templates/admin/settings.php';
    }

    public function  percent_menu_page(){

        if(isset($_POST['yw_percent_form'])){
            $selected_partners = [];
            
            if(isset($_POST['yw_partner'])){
                $partners = $_POST['yw_partner'];

                for($i = 0; $i < count($partners['selected']); $i++) {
                    $selected_partners[$partners['selected'][$i]] = [
                        'category' => $partners['category'][$i],
                        'brand' => $partners['brand'][$i],
                        'list_percent' => $partners['list_percent'][$i],
                        'discount_percent' => $partners['discount_percent'][$i]
                    ];
                }
            }

            YWSettings::set_yw_config('partners_percent', $selected_partners);

            add_action('admin_notices', [$this, 'save_notice']);
        }

        include_once YW_DIR . 'templates/admin/percent.php';
    }

    public function save_notice(){
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'تنظیمات ذخیره شد.'); ?></p>
        </div>
        <?php
    }

    public function add_yw_min_max_fields() {
        echo '<hr>';
        echo '<h4>مدیریت تعداد خرید در فروش همکار</h4>';
        woocommerce_wp_text_input(
            array(
                'id' => 'minimum_buy',
                'label' => __( 'حداقل تعداد قابل خرید'),
                'desc_tip' => true,
                'description' => __( 'درصورت ورود مقدار حتما در سفارش خواهد بود' ),
            )
        );
        woocommerce_wp_text_input(
            array(
                'id' => 'maximum_buy',
                'label' => __( 'حداکثر تعداد قابل خرید'),
                'desc_tip' => true,
                'description' => __( 'به صورت پیش فرض تعداد موجودی کالا حداکثر تعداد قابل خرید میباشد' ),
            )
        );
        woocommerce_wp_text_input(
            array(
                'id' => 'quantity_count_step',
                'label' => __( 'واحد افزایش تعداد محصول'),
                'desc_tip' => true,
                'description' => __( 'برای افزایش تساعدی تعداد محصول مقدار را وارد کنید. در غیر اینصورت یک واحد یک واحد افزایش خواهد یافت.' ),
            )
        );
        echo '<hr>';
    }

    public function save_yw_min_max_fields( $post_id ) {
        update_post_meta( $post_id, 'minimum_buy', wp_kses_post( $_POST['minimum_buy'] ) );
        update_post_meta( $post_id, 'maximum_buy', wp_kses_post( $_POST['maximum_buy'] ) );
        update_post_meta( $post_id, 'quantity_count_step', wp_kses_post( $_POST['quantity_count_step'] ) );
    }

    public function add_profile_fields( $user ) {
        $list_percent = get_user_meta( $user->ID, 'list_percent', true );
        $discount_percent = get_user_meta( $user->ID, 'discount_percent', true );

        ?>
        <h3>درصدهای خرید همکار</h3>
        <table class="form-table" style="background-color:  rgb(252, 160, 79); border-radius: 10px">
            <tr>
                <th style="padding-right: 20px"><label for="list_percent">درصد افزایش کلی لیست</label></th>
                <td>
                    <input type="number" name="list_percent" id="list_percent" value="<?php echo esc_attr( $list_percent ) ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th style="padding-right: 20px"><label for="discount_percent">درصد تخفیف کلی خرید</label></th>
                <td>
                    <input type="number" name="discount_percent" id="discount_percent" value="<?php echo esc_attr( $discount_percent ) ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_profile_fields( $user_id ) {

        if( ! isset( $_POST[ '_wpnonce' ] ) || ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'update-user_' . $user_id ) ) {
            return;
        }

        if( ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }

        update_user_meta( $user_id, 'list_percent', sanitize_text_field( $_POST[ 'list_percent' ] ) );
        update_user_meta( $user_id, 'discount_percent', sanitize_text_field( $_POST[ 'discount_percent' ] ) );

    }

    public function percent_taxonomy_add_new_meta_field() {
        ?>
        <div class="form-field" style="background-color:  rgb(252, 160, 79); padding: 10px">
            <label for="list_percent"><?php _e( 'درصد افزایش قیمت در لیست' ); ?></label>
            <input type="number" name="list_percent" id="list_percent">
            <p>خالی بگذارید تا اعمال نشود</p>
        </div>
        <div class="form-field" style="background-color:  rgb(252, 160, 79); padding: 10px">
            <label for="discount_percent"><?php _e( 'درصد تخفیف در خرید' ); ?></label>
            <input type="number" name="discount_percent" id="discount_percent">
            <p>خالی بگذارید تا اعمال نشود</p>
        </div>
        <?php
    }

    public function percent_taxonomy_edit_meta_field($term) {
        $list_percent = get_term_meta($term->term_id,'list_percent',true);
        $discount_percent = get_term_meta($term->term_id,'discount_percent',true);
        ?>

        <tr class="form-field" style="background-color:  rgb(252, 160, 79); padding: 10px">
            <th scope="row" valign="top"><label for="list_percent"><?php _e( 'درصد افزایش قیمت در لیست' ); ?></label></th>
            <td>
                <input type="number" name="list_percent" id="list_percent" value="<?php echo esc_attr($list_percent); ?>">
                <p>خالی بگذارید تا اعمال نشود</p>
            </td>
        </tr>
        <tr class="form-field" style="background-color:  rgb(252, 160, 79); padding: 10px">
            <th scope="row" valign="top"><label for="discount_percent"><?php _e( 'درصد تخفیف در خرید' ); ?></label></th>
            <td>
                <input type="number" name="discount_percent" id="discount_percent" value="<?php echo esc_attr($discount_percent); ?>">
                <p>خالی بگذارید تا اعمال نشود</p>
            </td>
        </tr>

        <?php
    }

    public function save_taxonomy_percent_meta( $term_id ) {
        update_term_meta($term_id, 'list_percent', sanitize_text_field( $_POST[ 'list_percent' ] ));
        update_term_meta($term_id, 'discount_percent', sanitize_text_field( $_POST[ 'discount_percent' ] ));
    }

    public function add_partner_scripts(){
        ?>
        <script type="text/javascript">
            let all_users = [];
            let all_cats = [];
            let all_brands = [];

            document.getElementById("add_new_partner_button").disabled = true;

            jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>",{
                action : 'yw_get_data_ajax',
            }).done(function(response){
                all_users = response.data[0];
                all_cats = response.data[1];
                all_brands = response.data[2];
                document.getElementById("add_new_partner_button").disabled = false;
            });

            jQuery(document.body).on('click', '#add_new_partner_button', function(e){
                let main_container = document.getElementById('main_container');
                let main_div = document.createElement('div');
                main_div.classList.add('partner-container');

                let partner_select = document.createElement('select');
                partner_select.setAttribute('name','yw_partner[selected][]');

                for(let i = 0; i < all_users.length; i++){
                    let partner_option = document.createElement('option');
                    partner_option.value = all_users[i][0];
                    partner_option.text = all_users[i][1];
                    partner_select.appendChild(partner_option);
                }

                let category_select = document.createElement('select');
                category_select.setAttribute('name','yw_partner[category][]');

                for(let i = 0; i < all_cats.length; i++){
                    let category_option = document.createElement('option');
                    category_option.value = all_cats[i][0];
                    category_option.text = all_cats[i][1];
                    category_select.appendChild(category_option);
                }

                let brand_select = document.createElement('select');
                brand_select.setAttribute('name','yw_partner[brand][]');

                for(let i = 0; i < all_brands.length; i++){
                    let brand_option = document.createElement('option');
                    brand_option.value = all_brands[i][0];
                    brand_option.text = all_brands[i][1];
                    brand_select.appendChild(brand_option);
                }

                let partner_list_percent = document.createElement('input');
                partner_list_percent.setAttribute('name','yw_partner[list_percent][]');
                partner_list_percent.setAttribute('placeholder','درصد افزایش قیمت در لیست');
                partner_list_percent.value = 0;
                partner_list_percent.required = true;

                let partner_discount_percent = document.createElement('input');
                partner_discount_percent.setAttribute('name','yw_partner[discount_percent][]');
                partner_discount_percent.setAttribute('placeholder','درصد تخفیف در خرید');
                partner_discount_percent.value = 0;
                partner_discount_percent.required = true;

                let partner_remove_button = document.createElement('button');
                partner_remove_button.setAttribute('id','remove_partner_button');
                partner_remove_button.setAttribute('type','button');
                partner_remove_button.setAttribute('onclick','remove_parent(this)');
                partner_remove_button.innerHTML = 'حذف';

                main_div.appendChild(partner_select);
                main_div.appendChild(category_select);
                main_div.appendChild(brand_select);
                main_div.appendChild(partner_list_percent);
                main_div.appendChild(partner_discount_percent);
                main_div.appendChild(partner_remove_button);

                main_container.appendChild(main_div);
            });

            function remove_parent(value){
                jQuery(value).parent().remove();
            }
        </script>
        <?php
    }

    public function yw_get_data_func(){
        wp_send_json_success([
            YWHelpers::get_all_users(),
            YWHelpers::get_all_product_categories(),
            YWHelpers::get_all_product_brands(),
        ]);
    }
}new YWAdminMenu();