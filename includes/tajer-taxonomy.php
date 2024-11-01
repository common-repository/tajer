<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// Register Products Category Custom Taxonomy
add_action( 'init', 'tajer_register_taxes' );
function tajer_register_taxes() {

	$labels = apply_filters( 'tajer_product_category_labels', array(
		"name"                       => "Product Categories",
		"label"                      => "Categories",
		"menu_name"                  => "Categories",
		"all_items"                  => "All Categories",
		"edit_item"                  => "Edit Category",
		"view_item"                  => "View Category",
		"update_item"                => "Update Category Name",
		"add_new_item"               => "Add New Category",
		"new_item_name"              => "New Category Name",
		"parent_item"                => "Parent Category",
		"parent_item_colon"          => "Parent Category",
		"search_items"               => "Search Categories",
		"popular_items"              => "Popular Categories",
		"separate_items_with_commas" => "Separate categories with commas)",
		"add_or_remove_items"        => "Add or remove categories",
		"choose_from_most_used"      => "Choose from the most used categories",
		"not_found"                  => "No categories found",
	) );

	$args = apply_filters( 'tajer_product_category_args', array(
		"labels"            => $labels,
		"hierarchical"      => true,
		"label"             => "Categories",
		"show_ui"           => true,
		"query_var"         => true,
		"rewrite"           => array( 'slug' => 'product-categories', 'with_front' => true, 'hierarchical' => true ),
		"show_admin_column" => true
	), $labels );
	register_taxonomy( "tajer_product_category", array( "tajer_products" ), $args );


	$labels = apply_filters( 'tajer_product_tag_labels', array(
		"name"                       => "Product Tags",
		"label"                      => "Tags",
		"menu_name"                  => "Tags",
		"all_items"                  => "All Tags",
		"edit_item"                  => "Edit Tag",
		"view_item"                  => "View Tag",
		"update_item"                => "Update Tag Name",
		"add_new_item"               => "Add New Tag",
		"new_item_name"              => "New Tag Name",
		"parent_item"                => "Parent Tag",
		"parent_item_colon"          => "Parent Tag",
		"search_items"               => "Search Tags",
		"popular_items"              => "Popular Tags",
		"separate_items_with_commas" => "Separate tags with commas",
		"add_or_remove_items"        => "Add or remove tags",
		"choose_from_most_used"      => "Choose from the most used tags",
		"not_found"                  => "No tags found",
	) );

	$args = apply_filters( 'tajer_product_tag_args', array(
		"labels"            => $labels,
		"hierarchical"      => false,
		"label"             => "Tags",
		"show_ui"           => true,
		"query_var"         => true,
		"rewrite"           => array( 'slug' => 'product-tags', 'with_front' => true, 'hierarchical' => true ),
		"show_admin_column" => true
	), $labels );
	register_taxonomy( "tajer_product_tag", array( "tajer_products" ), $args );

// End tajer_register_taxes
}
