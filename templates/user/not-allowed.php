<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="yw-not-allowed">
    <h3><?php echo YWSettings::get_yw_config('not_allowed_title') !== null && !empty(YWSettings::get_yw_config('not_allowed_title')) ? YWSettings:: get_yw_config('not_allowed_title') : ''; ?></h3>
    <p><?php echo YWSettings::get_yw_config('not_allowed_text') !== null && !empty(YWSettings::get_yw_config('not_allowed_title')) ? YWSettings::get_yw_config('not_allowed_text') : ''; ?></p>
    <a href="<?php echo YWSettings::get_yw_config('not_allowed_button_link') !== null && !empty(YWSettings::get_yw_config('not_allowed_button_link')) ? YWSettings::get_yw_config('not_allowed_button_link') : '#'; ?>" class="button">درخواست حساب خرید عمده</a>
</div>
