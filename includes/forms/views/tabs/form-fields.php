<?php
defined('ABSPATH') || exit;

add_thickbox(); ?>

<div class="alignright">
    <a href="#TB_inline?width=0&height=550&inlineId=nl4wp-form-variables" class="thickbox button-secondary">
        <span class="dashicons dashicons-info"></span>
        <?php echo esc_html__('Form variables', 'newsletter-for-wp'); ?>
    </a>
    <a href="#TB_inline?width=600&height=400&inlineId=nl4wp-add-field-help" class="thickbox button-secondary">
        <span class="dashicons dashicons-editor-help"></span>
        <?php echo esc_html__('Add more fields', 'newsletter-for-wp'); ?>
    </a>
</div>
<h2><?php echo esc_html__('Form Fields', 'newsletter-for-wp'); ?></h2>

<!-- Placeholder for the field wizard -->
<div id="nl4wp-field-wizard"></div>

<div class="nl4wp-form-markup-wrap">
    <div class="nl4wp-form-editor-wrap">
        <h4 style="margin: 0"><?php echo esc_html__('Form code', 'newsletter-for-wp'); ?> <span style="visibility: hidden;" class="dashicons dashicons-editor-help"></span></h4>
        <!-- Textarea for the actual form content HTML -->
        <textarea class="widefat" cols="160" rows="20" id="nl4wp-form-content" name="nl4wp_form[content]" placeholder="<?php echo esc_attr__('Enter the HTML code for your form fields..', 'newsletter-for-wp'); ?>" autocomplete="false" autocorrect="false" autocapitalize="false" spellcheck="false"><?php echo esc_textarea($form->content); ?></textarea>
    </div>
    <div class="nl4wp-form-preview-wrap">
        <h4 style="margin: 0;">
            <?php echo esc_html__('Form preview', 'newsletter-for-wp'); ?>
            <span class="dashicons dashicons-editor-help" title="<?php echo esc_attr__('The form may look slightly different than this when shown in a post, page or widget area.', 'newsletter-for-wp'); ?>"></span>
        </h4>
        <iframe id="nl4wp-form-preview" src="<?php echo esc_attr($form_preview_url); ?>"></iframe>
    </div>
</div>


<!-- This field is updated by JavaScript as the form content changes -->
<input type="hidden" id="required-fields" name="nl4wp_form[settings][required_fields]" value="<?php echo esc_attr($form->settings['required_fields']); ?>" />

<?php submit_button(); ?>

<p class="nl4wp-form-usage">
<?php
    echo wp_kses(
        sprintf(
            // translators: %s is the shortcode to display the form (e.g. [nl4wp_form id=123]).
            esc_html__('Use the shortcode %s to display this form inside a post, page or text widget.', 'newsletter-for-wp'),
            '<input type="text" onfocus="this.select();" readonly="readonly" value="' . esc_attr(sprintf('[nl4wp_form id=%d]', $form->ID)) . '" size="' . esc_attr((string) ( strlen((string) $form->ID) + 15 )) . '">'
        ),
        [
            'input' => [
                'type' => [],
                'onfocus' => [],
                'readonly' => [],
                'value' => [],
                'size' => [],
            ],
        ]
    );
    ?>
    </p>


<?php // Content for Thickboxes ?>
<div id="nl4wp-form-variables" style="display: none;">
    <?php require __DIR__ . '/../parts/dynamic-content-tags.php'; ?>
</div>

<div id="nl4wp-add-field-help" style="display: none;">
    <?php require __DIR__ . '/../parts/add-fields-help.php'; ?>
</div>
