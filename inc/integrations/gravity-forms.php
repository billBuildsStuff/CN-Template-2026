<?php
/**
 * Gravity Forms integration.
 *
 * @package cn-starter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Populate the Gravity Form block's form selector with available forms.
 *
 * @param array $field The ACF field.
 * @return array
 */
function cn_populate_gravity_forms_choices( $field ) {
	$field['choices'] = array();

	if ( class_exists( 'GFAPI' ) ) {
		$forms = GFAPI::get_forms();
		foreach ( $forms as $form ) {
			$field['choices'][ $form['id'] ] = $form['title'];
		}
	}

	return $field;
}
add_filter( 'acf/load_field/key=field_cn_gform_form_id', 'cn_populate_gravity_forms_choices' );

/**
 * Let the theme own Gravity Forms styling (uses base.css form styles).
 */
add_filter( 'gform_disable_form_theme_css', '__return_true' );
