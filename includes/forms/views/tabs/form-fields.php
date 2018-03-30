<?php add_thickbox(); ?>

<div class="alignright">
	<a href="#TB_inline?width=0&height=550&inlineId=nl4wp-form-variables" class="thickbox button-secondary">
		<span class="dashicons dashicons-info"></span>
		<?php _e( 'Form variables', 'newsletter-for-wp' ); ?>
	</a>
	<a href="#TB_inline?width=600&height=400&inlineId=nl4wp-add-field-help" class="thickbox button-secondary">
		<span class="dashicons dashicons-editor-help"></span>
		<?php _e( 'Add more fields', 'newsletter-for-wp' ); ?>
	</a>
</div>
<h2><?php _e( "Form Fields", 'newsletter-for-wp' ); ?></h2>

<!-- Placeholder for the field wizard -->
<div id="nl4wp-field-wizard"></div>

<div class="nl4wp-row">
	<div class="nl4wp-col nl4wp-col-3 nl4wp-form-editor-wrap">
		<h4 style="margin: 0"><label><?php _e( 'Form code', 'newsletter-for-wp' ); ?></label></h4>
		<!-- Textarea for the actual form content HTML -->
		<textarea class="widefat" cols="160" rows="20" id="nl4wp-form-content" name="nl4wp_form[content]" placeholder="<?php _e( 'Enter the HTML code for your form fields..', 'newsletter-for-wp' ); ?>" autocomplete="false" autocorrect="false" autocapitalize="false" spellcheck="false"><?php echo htmlspecialchars( $form->content, ENT_QUOTES, get_option( 'blog_charset' ) ); ?></textarea>
	</div>
	<div class="nl4wp-col nl4wp-col-3 nl4wp-form-preview-wrap">
		<h4 style="margin: 0;"><label><?php _e( 'Form preview', 'newsletter-for-wp' ); ?> <span class="dashicons dashicons-editor-help hf-tooltip" title="<?php esc_attr_e( 'The form may look slightly different than this when shown in a post, page or widget area.', 'newsletter-for-wp' ); ?>"></span></label></h4>
		<iframe id="nl4wp-form-preview" src="<?php echo esc_attr( $form_preview_url ); ?>"></iframe>
	</div>
</div>


<!-- This field is updated by JavaScript as the form content changes -->
<input type="hidden" id="required-fields" name="nl4wp_form[settings][required_fields]" value="<?php echo esc_attr( $form->settings['required_fields'] ); ?>" />

<?php submit_button(); ?>

<p class="nl4wp-form-usage"><?php printf( __( 'Use the shortcode %s to display this form inside a post, page or text widget.' ,'newsletter-for-wp' ), '<input type="text" onfocus="this.select();" readonly="readonly" value="'. esc_attr( sprintf( '[nl4wp_form id="%d"]', $form->ID ) ) .'" size="'. ( strlen( $form->ID ) + 18 ) .'">' ); ?></p>


<?php // Content for Thickboxes ?>
<div id="nl4wp-form-variables" style="display: none;">
	<?php include dirname( __FILE__ ) . '/../parts/dynamic-content-tags.php'; ?>
</div>

<div id="nl4wp-add-field-help" style="display: none;">
	<?php include dirname( __FILE__ ) . '/../parts/add-fields-help.php'; ?>
</div>
