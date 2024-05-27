<?php defined('ABSPATH') or exit;

$tabs = array(
    'fields' => __('Fields', 'newsletter-for-wp'),
    'messages' => __('Messages', 'newsletter-for-wp'),
    'settings' => __('Settings', 'newsletter-for-wp'),
    'appearance' => __('Appearance', 'newsletter-for-wp')
);

/**
 * Filters the setting tabs on the "edit form" screen.
 *
 * @param array $tabs
 * @ignore
 */
$tabs = apply_filters('nl4wp_admin_edit_form_tabs', $tabs);

?>
<div id="nl4wp-admin" class="wrap nl4wp-settings">

    <p class="breadcrumbs">
        <span class="prefix"><?php echo __('You are here: ', 'newsletter-for-wp'); ?></span>
        <a href="<?php echo admin_url('admin.php?page=newsletter-for-wp'); ?>">Newsletter for WordPress</a> &rsaquo;
        <a href="<?php echo admin_url('admin.php?page=newsletter-for-wp-forms'); ?>"><?php _e('Forms', 'newsletter-for-wp'); ?></a>
        &rsaquo;
        <span class="current-crumb"><strong><?php echo __('Form', 'newsletter-for-wp'); ?> <?php echo $form_id; ?>
                | <?php echo esc_html($form->name); ?></strong></span>
    </p>

    <div class="row">

        <!-- Main Content -->
        <div class="main-content col col-6">

            <h1 class="page-title">
                <?php _e("Edit Form", 'newsletter-for-wp'); ?>

                <!-- Form actions -->
                <?php

                /**
                 * @ignore
                 */
                do_action('nl4wp_admin_edit_form_after_title');
                ?>
            </h1>

            <h2 style="display: none;"></h2><?php // fake h2 for admin notices?>

            <!-- Wrap entire page in <form> -->
            <form method="post">
                <?php // default submit button to prevent opening preview?>
                <input type="submit" style="display: none; "/>
                <input type="hidden" name="_nl4wp_action" value="edit_form"/>
                <input type="hidden" name="nl4wp_form_id" value="<?php echo esc_attr($form->ID); ?>"/>
                <?php wp_nonce_field('edit_form', '_nl4wp_nonce'); ?>

                <div id="titlediv" class="small-margin">
                    <div id="titlewrap">
                        <label class="screen-reader-text"
                               for="title"><?php _e('Enter form title here', 'newsletter-for-wp'); ?></label>
                        <input type="text" name="nl4wp_form[name]" size="30"
                               value="<?php echo esc_attr($form->name); ?>" id="title" spellcheck="true"
                               autocomplete="off"
                               placeholder="<?php echo __("Enter the title of your sign-up form", 'newsletter-for-wp'); ?>"
                               style="line-height: initial;">
                    </div>
                    <div>
                        <?php printf(__('Use the shortcode %s to display this form inside a post, page or text widget.', 'newsletter-for-wp'), '<input type="text" onfocus="this.select();" readonly="readonly" value="'. esc_attr(sprintf('[nl4wp_form id="%d"]', $form->ID)) .'" size="'. (strlen($form->ID) + 18) .'">'); ?>
                    </div>
                </div>


                <div>
                    <h2 class="nav-tab-wrapper" id="nl4wp-tabs-nav">
                        <?php foreach ($tabs as $tab => $name) {
                    $class = ($active_tab === $tab) ? 'nav-tab-active' : '';
                    echo sprintf('<a class="nav-tab nav-tab-%s %s" href="%s">%s</a>', $tab, $class, esc_attr($this->tab_url($tab)), $name);
                } ?>
                    </h2>

                    <div id="nl4wp-tabs">

                        <?php foreach ($tabs as $tab => $name) :

                            $class = ($active_tab === $tab) ? 'tab-active' : '';

                            // start of .tab
                            echo sprintf('<div class="tab %s" id="tab-%s">', $class, $tab);

                            /**
                             * Runs when outputting a tab section on the "edit form" screen
                             *
                             * @param string $tab
                             * @ignore
                             */
                            do_action('nl4wp_admin_edit_form_output_' . $tab . '_tab', $opts, $form);

                            $tab_file = dirname(__FILE__) . '/tabs/form-' . $tab . '.php';
                            if (file_exists($tab_file)) {
                                include $tab_file;
                            }

                            // end of .tab
                            echo '</div>';

                        endforeach; // foreach tabs?>

                    </div><!-- / tabs -->
                </div>

            </form><!-- Entire page form wrap -->


            <?php  ?>

        </div>

        


    </div>

</div>
