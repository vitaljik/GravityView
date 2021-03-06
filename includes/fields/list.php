<?php

/**
 * Add custom options for list fields
 *
 * @since 1.14
 */
class GravityView_Field_List extends GravityView_Field {

	var $name = 'list';

	function __construct() {

		parent::__construct();

		add_filter( 'gravityview/template/field_label', array( $this, '_filter_field_label' ), 10, 4 );
	}

	/**
	 * Get the value of a Multiple Column List field for a specific column.
	 *
	 * @since 1.14
	 *
	 * @see GF_Field_List::get_value_entry_detail()
	 *
	 * @param GF_Field_List $field Gravity Forms field
	 * @param string|array $field_value Serialized or unserialized array value for the field
	 * @param int|string $column_id The numeric key of the column (0-index) or the label of the column
	 * @param string $format If set to 'raw', return an array of values for the column. Otherwise, allow Gravity Forms to render using `html` or `text`
	 *
	 * @return array|string|null Returns null if the $field_value passed wasn't an array or serialized array
	 */
	public static function column_value( GF_Field_List $field, $field_value, $column_id = 0, $format = 'html' ) {

		$list_rows = maybe_unserialize( $field_value );

		if( ! is_array( $list_rows ) ) {
			do_action( 'gravityview_log_error', __METHOD__ . ' - $field_value did not unserialize', $field_value );
			return null;
		}

		$column_values = array();

		// Each list row
		foreach ( $list_rows as $list_row ) {
			$current_column = 0;
			foreach ( $list_row as $column_key => $column_value ) {

				// If the label of the column matches $column_id, or the numeric key value matches, add the value
				if( (string)$column_key === (string)$column_id || ( is_numeric( $column_id ) && (int)$column_id === $current_column ) ) {
					$column_values[] = $column_value;
				}
				$current_column++;
			}
		}

		// Return the array of values
		if( 'raw' === $format ) {
			return $column_values;
		}
		// Return the Gravity Forms Field output
		else {
			return $field->get_value_entry_detail( serialize( $column_values ), '', false, $format );
		}
	}

	/**
	 * When showing a single column values, display the label of the column instead of the field
	 *
	 * @since 1.14
	 *
	 * @param string $label Existing label string
	 * @param array $field GV field settings array, with `id`, `show_label`, `label`, `custom_label`, etc. keys
	 * @param array $form Gravity Forms form array
	 * @param array $entry Gravity Forms entry array
	 *
	 * @return string Existing label if the field isn't
	 */
	public function _filter_field_label( $label, $field, $form, $entry ) {

		$field_object = RGFormsModel::get_field( $form, $field['id'] );

		// Not a list field
		if( ( ! $field_object || 'list' !== $field_object->get_input_type() ) ) {
			return $label;
		}

		// Custom label is defined, so use it
		if( ! empty( $field['custom_label'] ) ) {
			return $label;
		}

		$field_id_array = explode( '.', $field['id'] );

		// Parent field, not column field
		if( ! isset( $field_id_array[1] ) ) {
			return $label;
		}

		$column_id = intval( $field_id_array[1] );

		return self::get_column_label( $field_object, $column_id, $label );
	}

	/**
	 * Get the column label for the list
	 *
	 * @since 1.14
	 *
	 * @param GF_Field_List $field Gravity Forms List field
	 * @param int $column_id The key of the column (0-index)
	 * @param string $backup_label Backup label to use. Optional.
	 *
	 * @return string
	 */
	public static function get_column_label( GF_Field_List $field, $column_id, $backup_label = '' ) {

		// Doesn't have columns enabled
		if( ! isset( $field->choices ) || ! $field->enableColumns ) {
			return $backup_label;
		}

		// Get the list of columns, with numeric index keys
		$columns = wp_list_pluck( $field->choices, 'text' );

		return isset( $columns[ $column_id ] ) ? $columns[ $column_id ] : $backup_label;
	}

}

new GravityView_Field_List;