<?php

if(!defined('ABSPATH')){exit;}

$roles = wp_roles()->roles;
?>

<div class="wrap">
    <section class="yw-admin-style">
        <div class="yw-title">
            <span class="symbol"></span>
            <h2>تنظیمات فروش همکار</h2>
        </div>
        <div class="settings-container">
            <form action="" method="POST">
                <div class="yw-form-control">
                    <input type="checkbox" class="checkbox" id="active_partner_buy" name="yw_settings[active_partner_buy]" <?php echo YWSettings::get_yw_config('active_partner_buy') !== null && YWSettings::get_yw_config('active_partner_buy') == 'on' ? 'checked' : ''; ?> >
                    <label for="active_partner_buy">فعال کردن سیستم فروش همکار ؟</label>
                </div>
                <hr>
                <div class="yw-form-control">
                    <p>چه نقش هایی مجاز به سفارش همکاری هستند؟</p>
                </div>

                <?php foreach ($roles as $key => $value): ?>
                    <div class="yw-form-control">
                        <input type="checkbox" class="checkbox" id="role_<?php echo $key; ?>" name="yw_settings[roles][<?php echo $key; ?>]" <?php echo YWSettings::get_yw_config('selected_roles') !== null && in_array($key, YWSettings::get_yw_config('selected_roles')) ? 'checked' : ''; ?> >
                        <label for="role_<?php echo $key; ?>"><?= $value['name']; ?></label>
                    </div>
                <?php endforeach; ?>

                <hr>
                <div class="yw-form-control">
                    <p>پیام صفحه فعال نشدن خرید عمده</p>
                </div>
                <div class="yw-form-control">
                    <label for="not_allowed_title">عنوان صفحه</label>
                    <input type="text" id="not_allowed_title" name="yw_settings[not_allowed_title]" value="<?php echo YWSettings::get_yw_config('not_allowed_title') !== null && !empty(YWSettings::get_yw_config('not_allowed_title')) ? YWSettings:: get_yw_config('not_allowed_title') : ''; ?>" >
                </div>
                <div class="yw-form-control">
                    <label for="not_allowed_text">متن پیام</label>
                    <textarea id="not_allowed_text" name="yw_settings[not_allowed_text]" rows="5"><?php echo YWSettings::get_yw_config('not_allowed_text') !== null && !empty(YWSettings::get_yw_config('not_allowed_title')) ? YWSettings::get_yw_config('not_allowed_text') : ''; ?></textarea>
                </div>
                <div class="yw-form-control">
                    <label for="not_allowed_button_link">لینک صفحه درخواست</label>
                    <input type="text" id="not_allowed_button_link" name="yw_settings[not_allowed_button_link]" value="<?php echo YWSettings::get_yw_config('not_allowed_button_link') !== null && !empty(YWSettings::get_yw_config('not_allowed_button_link')) ? YWSettings::get_yw_config('not_allowed_button_link') : ''; ?>" >
                </div>

                <hr>
                <div class="yw-form-control">
                    <button type="submit" name="yw_settings_form">ذخیره</button>
                </div>
            </form>
        </div>
    </section>
</div>