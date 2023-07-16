<?php

if(!defined('ABSPATH')){exit;}

class YWHelpers{
    public static function increase_percent(float $price, int $percent): float
    {
        return round(((($price / 100) * $percent) + $price), -2);
    }

    public static function decrease_percent(float $price, int $percent): float
    {
        return round(((($price / 100) * $percent) - $price), -2);
    }

    public static function get_all_users(): array {
        $raw_users = get_users();
        $users = [];

        foreach ($raw_users as $user){
            $users[] = [
                $user->id,
                $user->display_name
            ];
        }

        return $users;
    }

    public static function get_all_product_categories(): array {
        $cats = [];
        $cats[] = ['none', 'انتخاب دسته بندی'];

        $args = array(
            'taxonomy'     => 'product_cat',
            'orderby'      => 'name',
	        'hierarchical' => 1,
            'hide_empty'   => 1,
	        'exclude_tree' => array(79),
            'parent' => '0',
        );
        $product_categories = get_categories($args);

        foreach ($product_categories as $category) {
            $cats[] = [
				$category->term_id,
	            $category->name,
	            $category->slug,
            ];
        }

        return $cats;
    }

    public static function get_all_product_brands(): array {
        $brands = [];
        $brands[] = ['none', 'انتخاب برند'];

        $args = array(
            'taxonomy'     => 'product_brand',
            'orderby'      => 'name',
            'hide_empty'   => false,
            'parent' => 0
        );

        $product_brands = get_terms($args);

        if(!is_wp_error($product_brands)){
            foreach ($product_brands as $brand) {
                $brands[] = [
                    $brand->term_id,
                    $brand->name
                ];
            }
        }

        return $brands;
    }
}
