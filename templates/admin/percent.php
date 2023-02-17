<?php

if(!defined('ABSPATH')){exit;}

$roles = wp_roles()->roles;

$load_partners = YWSettings::get_yw_config('partners_percent');
$all_users = YWHelpers::get_all_users();
$all_cats = YWHelpers::get_all_product_categories();
$all_brands = YWHelpers::get_all_product_brands();
?>

<div class="wrap">
    <section class="yw-admin-style">
        <div class="yw-title">
            <span class="symbol"></span>
            <h2>درصدهای همکاران</h2>
        </div>
        <div class="settings-container">
            <form action="" method="POST">
                <div class="yw-form-control">
                    <p>اعمال درصد های دلخواه برای هر همکار</p>
                </div>
                <div class="yw-form-control">
                    <button type="button" id="add_new_partner_button">افزودن همکار</button>
                </div>
                <hr>

                <div id="main_container">
                    <?php if($load_partners !==null && count($load_partners) > 0): ?>
                        <?php foreach ($load_partners as $partner_id => $partner_value): ?>
                            <div class="partner-container">
                                <select name="yw_partner[selected][]">
                                    <?php foreach ($all_users as $user): ?>
                                        <option value="<?php echo $user[0]; ?>" <?php echo $partner_id == $user[0] ? 'selected' : ''; ?> ><?php echo $user[1]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="yw_partner[category][]">
                                    <?php foreach ($all_cats as $category): ?>
                                        <option value="<?php echo $category[0]; ?>" <?php echo $partner_value['category'] == $category[0] ? 'selected' : ''; ?> ><?php echo $category[1]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="yw_partner[brand][]">
                                    <?php foreach ($all_brands as $brand): ?>
                                        <option value="<?php echo $brand[0]; ?>" <?php echo $partner_value['brand'] == $brand[0] ? 'selected' : ''; ?> ><?php echo $brand[1]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input name="yw_partner[list_percent][]" placeholder="درصد افزایش قیمت در لیست" value="<?php echo $partner_value['list_percent']; ?>">
                                <input name="yw_partner[discount_percent][]" placeholder="درصد تخفیف در خرید" value="<?php echo $partner_value['discount_percent']; ?>">
                                <button id="remove_partner_button" type="button" onclick="remove_parent(this)">حذف</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <hr>
                <div class="yw-form-control">
                    <button type="submit" name="yw_percent_form">ذخیره</button>
                </div>
            </form>
        </div>
    </section>
</div>