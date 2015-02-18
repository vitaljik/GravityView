<?php
/**
 * Display the created_by field type
 *
 * @package GravityView
 */

$gravityview_view = GravityView_View::getInstance();

extract( $gravityview_view->getCurrentField() );

// There was no logged in user.
if( empty( $value ) ) {
	return;
}

// Get the user data for the passed User ID
$User = get_userdata($value);

// Display the user data, based on the settings `id`, `username`, or `display_name`
$name_display = empty( $field_settings['name_display'] ) ? 'display_name' : $field_settings['name_display'];

echo $User->{$name_display};
