<?php defined('ABSPATH') || exit; ?>

<p>
    <?php esc_html_e('To get a custom integration to work, include the following HTML in the form you are trying to integrate with.', 'newsletter-for-wp'); ?>
</p>

<?php ob_start(); ?>
<p>
    <label>
        <input type="checkbox" name="nl4wp-subscribe" value="1" />
        <?php esc_html_e('Subscribe to our newsletter.', 'newsletter-for-wp'); ?>
    </label>
</p>

<?php $html = ob_get_clean(); ?>

<textarea class="widefat code-sample" rows="<?php echo esc_attr((string) substr_count($html, PHP_EOL)); ?>" readonly onfocus="this.select()"><?php echo esc_textarea($html); ?></textarea>
