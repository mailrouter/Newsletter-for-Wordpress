<?php defined('ABSPATH') || exit;


/**
 * @var NL4WP_Form $form
 * @var string $active_tab
 * @var array $opts
 * @var int $form_id
 */

$tabs = [
    'fields'     => esc_html__('Fields', 'newsletter-for-wp'),
    'messages'   => esc_html__('Messages', 'newsletter-for-wp'),
    'settings'   => esc_html__('Settings', 'newsletter-for-wp'),
    'appearance' => esc_html__('Appearance', 'newsletter-for-wp'),
];

/**
 * Filters the setting tabs on the "edit form" screen.
 *
 * @param array $tabs
 */
$tabs = apply_filters('nl4wp_admin_edit_form_tabs', $tabs);

?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">

    <p class="nl4wp-breadcrumbs">
        <span class="prefix"><?php echo esc_html__('You are here: ', 'newsletter-for-wp'); ?></span>
        <a href="<?php echo esc_url(admin_url('admin.php?page=newsletter-for-wp')); ?>">Newsletter for WordPress</a> &rsaquo;
        <a href="<?php echo esc_url(admin_url('admin.php?page=newsletter-for-wp-forms')); ?>"><?php echo esc_html__('Forms', 'newsletter-for-wp'); ?></a>
        &rsaquo;
        <span class="current-crumb"><strong><?php echo esc_html__('Form', 'newsletter-for-wp'); ?> <?php echo esc_html((string) $form_id); ?> <?php echo esc_html($form->name); ?></strong></span>
    </p>

    <div>
        <h1 class="nl4wp-page-title">
            <?php echo esc_html__('Edit Form', 'newsletter-for-wp'); ?>

            <?php do_action('nl4wp_admin_edit_form_after_title'); ?>
        </h1>

        <?php // fake h2 for admin notices ?>
        <h2 style="display: none;"></h2>

        <?php // wrap entire page in <form> element ?>
        <form method="post">
            <?php // default submit button to prevent opening preview ?>
            <input type="submit" style="display: none;" />
            <input type="hidden" name="_nl4wp_action" value="edit_form"/>
            <?php wp_nonce_field('_nl4wp_action', '_wpnonce'); ?>
            <input type="hidden" name="nl4wp_form_id" value="<?php echo esc_attr((string) $form->ID); ?>"/>

            <div id="titlediv" class="nl4wp-margin-s">
                <div id="titlewrap">
                    <label class="screen-reader-text"
                            for="title"><?php echo esc_html__('Enter form title here', 'newsletter-for-wp'); ?></label>
                    <input type="text" name="nl4wp_form[name]" size="30"
                            value="<?php echo esc_attr($form->name); ?>" id="title" spellcheck="true"
                            autocomplete="off"
                            placeholder="<?php echo esc_html__('Enter the title of your sign-up form', 'newsletter-for-wp'); ?>"
                            style="line-height: initial;">
                </div>
                <div>
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
                </div>
            </div>


            <div>
                <h2 class="nav-tab-wrapper" id="nl4wp-tabs-nav">
                    <?php
                    foreach ($tabs as $tab => $name) {
                        $class = ( $active_tab === $tab ) ? 'nav-tab-active' : '';
                        $href  = $this->tab_url($tab);
                        printf('<a class="nav-tab nav-tab-%1$s %2$s" data-tab="%1$s" href="%3$s">%4$s</a>', esc_attr($tab), esc_attr($class), esc_url($href), esc_html($name));
                    }
                    ?>
                </h2>

                <div id="nl4wp-tabs">

                    <?php
                    foreach ($tabs as $tab => $name) {
                        $class = ( $active_tab === $tab ) ? 'nl4wp-tab-active' : '';
                        printf('<div class="nl4wp-tab %1$s" id="nl4wp-tab-%2$s">', esc_attr($class), esc_attr($tab));

                        /**
                         * Runs when outputting a tab section on the "edit form" screen
                         *
                         * @param array $opts
                         * @param NL4WP_Form $form
                         */
                        do_action('nl4wp_admin_edit_form_output_' . $tab . '_tab', $opts, $form);

                        $tab_file = __DIR__ . '/tabs/form-' . $tab . '.php';
                        if (file_exists($tab_file)) {
                            include $tab_file;
                        }

                        // end of .tab
                        echo '</div>';
                    } // foreach tabs
                    ?>

                </div>
            </div>
        </form>

        <?php  ?>
    </div>
</div>
