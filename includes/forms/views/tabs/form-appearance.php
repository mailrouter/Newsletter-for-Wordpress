<?php

$theme = wp_get_theme();
$css_options = array(
	'0' => sprintf( __( 'Inherit from %s theme', 'newsletter-for-wp' ), $theme->Name ),
	'basic' => __( 'Basic', 'newsletter-for-wp' ),
	__( 'Form Themes', 'newsletter-for-wp' ) => array(
		'theme-light' => __( 'Light Theme', 'newsletter-for-wp' ),
		'theme-dark' => __( 'Dark Theme', 'newsletter-for-wp' ),
		'theme-red' => __( 'Red Theme', 'newsletter-for-wp' ),
		'theme-green' => __( 'Green Theme', 'newsletter-for-wp' ),
		'theme-blue' => __( 'Blue Theme', 'newsletter-for-wp' ),
	)
);

/**
 * Filters the <option>'s in the "CSS Stylesheet" <select> box.
 *
 * @ignore
 */
$css_options = apply_filters( 'nl4wp_admin_form_css_options', $css_options );

?>

<h2><?php _e( 'Form Appearance', 'newsletter-for-wp' ); ?></h2>

<table class="form-table">
	<tr valign="top">
		<th scope="row"><label for="nl4wp_load_stylesheet_select"><?php _e( 'Form Style' ,'newsletter-for-wp' ); ?></label></th>
		<td class="nowrap valigntop">
			<select name="nl4wp_form[settings][css]" id="nl4wp_load_stylesheet_select">

				<?php foreach( $css_options as $key => $option ) {
					if( is_array( $option ) ) {
						$label = $key;
						$options = $option;
						printf( '<optgroup label="%s">', $label );
						foreach( $options as $key => $option ) {
							printf( '<option value="%s" %s>%s</option>', $key, selected( $opts['css'], $key, false ), $option );
						}
						print( '</optgroup>' );
					} else {
						printf( '<option value="%s" %s>%s</option>', $key, selected( $opts['css'], $key, false ), $option );
					}
				} ?>
			</select>
			<p class="help">
				<?php _e( 'If you want to load some default CSS styles, select "basic formatting styles" or choose one of the color themes' , 'newsletter-for-wp' ); ?>
			</p>
		</td>
	</tr>

	<?php
	/** @ignore */
	do_action( 'nl4wp_admin_form_after_appearance_settings_rows', $opts, $form );
	?>

</table>

<?php submit_button(); ?>
