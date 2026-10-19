<?php

defined('ABSPATH') || exit;

require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/deprecated-functions.php';
require __DIR__ . '/includes/forms/functions.php';
require __DIR__ . '/includes/forms/admin-functions.php';
require __DIR__ . '/includes/integrations/functions.php';
require __DIR__ . '/includes/class-container.php';
require __DIR__ . '/includes/class-dynamic-content-tags.php';
require __DIR__ . '/includes/forms/class-output-manager.php';
require __DIR__ . '/includes/forms/class-form-tags.php';
require __DIR__ . '/includes/forms/class-form-listener.php';
require __DIR__ . '/includes/forms/class-form-previewer.php';
require __DIR__ . '/includes/forms/class-asset-manager.php';
require __DIR__ . '/includes/forms/class-form-amp.php';
require __DIR__ . '/includes/forms/class-form-manager.php';
require __DIR__ . '/includes/integrations/class-integration-fixture.php';
require __DIR__ . '/includes/integrations/class-integration-tags.php';
require __DIR__ . '/includes/integrations/class-integration-manager.php';

spl_autoload_register(function ($class) {
    static $classmap = [
        'NL4WP_API_Connection_Exception' => '/includes/api/class-connection-exception.php',
        'NL4WP_API_Exception' => '/includes/api/class-exception.php',
        'NL4WP_API_Resource_Not_Found_Exception' => '/includes/api/class-resource-not-found-exception.php',
        'NL4WP_API_V3' => '/includes/api/class-api-v3.php',
        'NL4WP_API_V3_Client' => '/includes/api/class-api-v3-client.php',
        'NL4WP_Admin' => '/includes/admin/class-admin.php',
        'NL4WP_Admin_Ajax' => '/includes/admin/class-admin-ajax.php',
        'NL4WP_Admin_Cron_Notice' => '/includes/admin/class-cron-notice.php',
        'NL4WP_Admin_Messages' => '/includes/admin/class-admin-messages.php',
        'NL4WP_Admin_Texts' => '/includes/admin/class-admin-texts.php',
        'NL4WP_Admin_Tools' => '/includes/admin/class-admin-tools.php',
        'NL4WP_AffiliateWP_Integration' => '/integrations/affiliatewp/class-affiliatewp.php',
        'NL4WP_BuddyPress_Integration' => '/integrations/buddypress/class-buddypress.php',
        'NL4WP_Comment_Form_Integration' => '/integrations/wp-comment-form/class-comment-form.php',
        'NL4WP_Contact_Form_7_Integration' => '/integrations/contact-form-7/class-contact-form-7.php',
        'NL4WP_Custom_Integration' => '/integrations/custom/class-custom.php',
        'NL4WP_Debug_Log' => '/includes/class-debug-log.php',
        'NL4WP_Debug_Log_Reader' => '/includes/class-debug-log-reader.php',
        'NL4WP_Easy_Digital_Downloads_Integration' => '/integrations/easy-digital-downloads/class-easy-digital-downloads.php',
        'NL4WP_Events_Manager_Integration' => '/integrations/events-manager/class-events-manager.php',
        'NL4WP_Personal_Data_Exporter' => '/includes/class-personal-data-exporter.php',
        'NL4WP_Field_Formatter' => '/includes/class-field-formatter.php',
        'NL4WP_Field_Guesser' => '/includes/class-field-guesser.php',
        'NL4WP_Form' => '/includes/forms/class-form.php',
        'NL4WP_Form_Element' => '/includes/forms/class-form-element.php',
        'NL4WP_Form_Notice' => '/includes/forms/class-form-message.php',
        'NL4WP_Form_Widget' => '/includes/forms/class-widget.php',
        'NL4WP_Forms_Admin' => '/includes/forms/class-admin.php',
        'NL4WP_Give_Integration' => '/integrations/give/class-give.php',
        'NL4WP_Gravity_Forms_Field' => '/integrations/gravity-forms/class-field.php',
        'NL4WP_Gravity_Forms_Integration' => '/integrations/gravity-forms/class-gravity-forms.php',
        'NL4WP_Integration' => '/includes/integrations/class-integration.php',
        'NL4WP_Integration_Admin' => '/includes/integrations/class-admin.php',
        'NL4WP_List_Data_Mapper' => '/includes/class-list-data-mapper.php',
        'NL4WP_Forms_List_Table' => '/includes/forms/class-list-table.php',
        'NL4WP_Newsletter' => '/includes/class-newsletter.php',
        'NL4WP_Newsletter_Subscriber' => '/includes/class-newsletter-subscriber.php',
        'NL4WP_MemberPress_Integration' => '/integrations/memberpress/class-memberpress.php',
        'NL4WP_Ninja_Forms_Action' => '/integrations/ninja-forms/class-action.php',
        'NL4WP_Ninja_Forms_Field' => '/integrations/ninja-forms/class-field.php',
        'NL4WP_Ninja_Forms_Integration' => '/integrations/ninja-forms/class-ninja-forms.php',
        'NL4WP_PeepSo_Integration' => '/integrations/peepso/class-peepso.php',
        'NL4WP_Plugin' => '/includes/class-plugin.php',
        'NL4WP_Procaptcha_Integration' => '/integrations/prosopo-procaptcha/class-procaptcha-integration.php',
        'NL4WP_Procaptcha' => '/integrations/prosopo-procaptcha/class-procaptcha.php',
        'NL4WP_Queue' => '/includes/class-queue.php',
        'NL4WP_Queue_Job' => '/includes/class-queue-job.php',
        'NL4WP_Registration_Form_Integration' => '/integrations/wp-registration-form/class-registration-form.php',
        'NL4WP_Simple_Basic_Contact_Form_Integration' => '/integrations/simple-basic-contact-form/class-simple-basic-contact-form.php',
        'NL4WP_Tools' => '/includes/class-tools.php',
        'NL4WP_Upgrade_Routines' => '/includes/admin/class-upgrade-routines.php',
        'NL4WP_User_Integration' => '/includes/integrations/class-user-integration.php',
        'NL4WP_WPForms_Field' => '/integrations/wpforms/class-field.php',
        'NL4WP_WPForms_Integration' => '/integrations/wpforms/class-wpforms.php',
        'NL4WP_WooCommerce_Integration' => '/integrations/woocommerce/class-woocommerce.php',
    ];

    if (isset($classmap[$class])) {
        require __DIR__ . $classmap[$class];
    }
});
