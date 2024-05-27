<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2c420f229f0b44a84053c7de0a411c61_nl
{
    public static $files = array (
        'fca581ae5268210490253d58378748c5' => __DIR__ . '/../..' . '/includes/functions.php',
        '59b5ebb4886cd7eeaf4afcc7f7bdfcdd' => __DIR__ . '/../..' . '/includes/deprecated-functions.php',
        'd26f3384f491dc697dcc189393343bc3' => __DIR__ . '/../..' . '/includes/forms/functions.php',
        '5a75ca1db309a7972c23644d951fa1be' => __DIR__ . '/../..' . '/includes/forms/admin-functions.php',
        '90c3516593b5de6b5d4528b70e2399dd' => __DIR__ . '/../..' . '/includes/integrations/functions.php',
        '1961896a7f482d8affe08226795bf674' => __DIR__ . '/../..' . '/includes/default-actions.php',
        'cb296d991e3145f10320ab99c1b9ed05' => __DIR__ . '/../..' . '/includes/default-filters.php',
    );

    public static $prefixesPsr0 = array (
        'x' => 
        array (
            'xrstf\\Composer52' => 
            array (
                0 => __DIR__ . '/..' . '/xrstf/composer-php52/lib',
            ),
        ),
    );

    public static $classMap = array (
        'NL4WP_API_Connection_Exception' => __DIR__ . '/../..' . '/includes/api/class-connection-exception.php',
        'NL4WP_API_Exception' => __DIR__ . '/../..' . '/includes/api/class-exception.php',
        'NL4WP_API_Resource_Not_Found_Exception' => __DIR__ . '/../..' . '/includes/api/class-resource-not-found-exception.php',
        'NL4WP_API_v3' => __DIR__ . '/../..' . '/includes/api/class-api-v3.php',
        'NL4WP_API_v3_Client' => __DIR__ . '/../..' . '/includes/api/class-api-v3-client.php',
        'NL4WP_Admin' => __DIR__ . '/../..' . '/includes/admin/class-admin.php',
        'NL4WP_Admin_Ajax' => __DIR__ . '/../..' . '/includes/admin/class-admin-ajax.php',
        'NL4WP_Admin_Messages' => __DIR__ . '/../..' . '/includes/admin/class-admin-messages.php',
        'NL4WP_Admin_Texts' => __DIR__ . '/../..' . '/includes/admin/class-admin-texts.php',
        'NL4WP_Admin_Tools' => __DIR__ . '/../..' . '/includes/admin/class-admin-tools.php',
        'NL4WP_AffiliateWP_Integration' => __DIR__ . '/../..' . '/integrations/affiliatewp/class-affiliatewp.php',
        'NL4WP_Array_Bag' => __DIR__ . '/../..' . '/includes/class-array-bag.php',
        'NL4WP_BuddyPress_Integration' => __DIR__ . '/../..' . '/integrations/buddypress/class-buddypress.php',
        'NL4WP_Comment_Form_Integration' => __DIR__ . '/../..' . '/integrations/wp-comment-form/class-comment-form.php',
        'NL4WP_Contact_Form_7_Integration' => __DIR__ . '/../..' . '/integrations/contact-form-7/class-contact-form-7.php',
        'NL4WP_Container' => __DIR__ . '/../..' . '/includes/class-container.php',
        'NL4WP_Custom_Integration' => __DIR__ . '/../..' . '/integrations/custom/class-custom.php',
        'NL4WP_Debug_Log' => __DIR__ . '/../..' . '/includes/class-debug-log.php',
        'NL4WP_Debug_Log_Reader' => __DIR__ . '/../..' . '/includes/class-debug-log-reader.php',
        'NL4WP_Dynamic_Content_Tags' => __DIR__ . '/../..' . '/includes/class-dynamic-content-tags.php',
        'NL4WP_Easy_Digital_Downloads_Integration' => __DIR__ . '/../..' . '/integrations/easy-digital-downloads/class-easy-digital-downloads.php',
        'NL4WP_Events_Manager_Integration' => __DIR__ . '/../..' . '/integrations/events-manager/class-events-manager.php',
        'NL4WP_Field_Formatter' => __DIR__ . '/../..' . '/includes/class-field-formatter.php',
        'NL4WP_Field_Guesser' => __DIR__ . '/../..' . '/includes/class-field-guesser.php',
        'NL4WP_Form' => __DIR__ . '/../..' . '/includes/forms/class-form.php',
        'NL4WP_Form_Asset_Manager' => __DIR__ . '/../..' . '/includes/forms/class-asset-manager.php',
        'NL4WP_Form_Element' => __DIR__ . '/../..' . '/includes/forms/class-form-element.php',
        'NL4WP_Form_Listener' => __DIR__ . '/../..' . '/includes/forms/class-form-listener.php',
        'NL4WP_Form_Manager' => __DIR__ . '/../..' . '/includes/forms/class-form-manager.php',
        'NL4WP_Form_Notice' => __DIR__ . '/../..' . '/includes/forms/class-form-message.php',
        'NL4WP_Form_Output_Manager' => __DIR__ . '/../..' . '/includes/forms/class-output-manager.php',
        'NL4WP_Form_Previewer' => __DIR__ . '/../..' . '/includes/forms/class-form-previewer.php',
        'NL4WP_Form_Tags' => __DIR__ . '/../..' . '/includes/forms/class-form-tags.php',
        'NL4WP_Form_Widget' => __DIR__ . '/../..' . '/includes/forms/class-widget.php',
        'NL4WP_Forms_Admin' => __DIR__ . '/../..' . '/includes/forms/class-admin.php',
        'NL4WP_Give_Integration' => __DIR__ . '/../..' . '/integrations/give/class-give.php',
        'NL4WP_Google_Recaptcha' => __DIR__ . '/../..' . '/includes/forms/class-google-recaptcha.php',
        'NL4WP_Gravity_Forms_Field' => __DIR__ . '/../..' . '/integrations/gravity-forms/class-field.php',
        'NL4WP_Gravity_Forms_Integration' => __DIR__ . '/../..' . '/integrations/gravity-forms/class-gravity-forms.php',
        'NL4WP_Integration' => __DIR__ . '/../..' . '/includes/integrations/class-integration.php',
        'NL4WP_Integration_Admin' => __DIR__ . '/../..' . '/includes/integrations/class-admin.php',
        'NL4WP_Integration_Fixture' => __DIR__ . '/../..' . '/includes/integrations/class-integration-fixture.php',
        'NL4WP_Integration_Manager' => __DIR__ . '/../..' . '/includes/integrations/class-integration-manager.php',
        'NL4WP_Integration_Tags' => __DIR__ . '/../..' . '/includes/integrations/class-integration-tags.php',
        'NL4WP_List_Data_Mapper' => __DIR__ . '/../..' . '/includes/class-list-data-mapper.php',
        'NL4WP_Forms_List_Table' => __DIR__ . '/../..' . '/includes/forms/class-list-table.php',
        'NL4WP_Newsletter' => __DIR__ . '/../..' . '/includes/class-newsletter.php',
        'NL4WP_Newsletter_Interest_Category' => __DIR__ . '/../..' . '/includes/newsletter/class-interest-category.php',
        'NL4WP_Newsletter_List' => __DIR__ . '/../..' . '/includes/newsletter/class-list.php',
        'NL4WP_Newsletter_Merge_Field' => __DIR__ . '/../..' . '/includes/newsletter/class-merge-field.php',
        'NL4WP_Newsletter_Subscriber' => __DIR__ . '/../..' . '/includes/newsletter/class-subscriber.php',
        'NL4WP_MemberPress_Integration' => __DIR__ . '/../..' . '/integrations/memberpress/class-memberpress.php',
        'NL4WP_Ninja_Forms_Action' => __DIR__ . '/../..' . '/integrations/ninja-forms/class-action.php',
        'NL4WP_Ninja_Forms_Field' => __DIR__ . '/../..' . '/integrations/ninja-forms/class-field.php',
        'NL4WP_Ninja_Forms_Integration' => __DIR__ . '/../..' . '/integrations/ninja-forms/class-ninja-forms.php',
        'NL4WP_Ninja_Forms_v2_Integration' => __DIR__ . '/../..' . '/integrations/ninja-forms-2/class-ninja-forms.php',
        'NL4WP_Plugin' => __DIR__ . '/../..' . '/includes/class-plugin.php',
        'NL4WP_Queue' => __DIR__ . '/../..' . '/includes/class-queue.php',
        'NL4WP_Queue_Job' => __DIR__ . '/../..' . '/includes/class-queue-job.php',
        'NL4WP_Registration_Form_Integration' => __DIR__ . '/../..' . '/integrations/wp-registration-form/class-registration-form.php',
        'NL4WP_Request' => __DIR__ . '/../..' . '/includes/class-request.php',
        'NL4WP_Tools' => __DIR__ . '/../..' . '/includes/class-tools.php',
        'NL4WP_Update_Optin' => __DIR__ . '/../..' . '/includes/admin/class-update-optin.php',
        'NL4WP_Upgrade_Routines' => __DIR__ . '/../..' . '/includes/admin/class-upgrade-routines.php',
        'NL4WP_User_Integration' => __DIR__ . '/../..' . '/includes/integrations/class-user-integration.php',
        'NL4WP_WPForms_Field' => __DIR__ . '/../..' . '/integrations/wpforms/class-field.php',
        'NL4WP_WPForms_Integration' => __DIR__ . '/../..' . '/integrations/wpforms/class-wpforms.php',
        'NL4WP_WooCommerce_Integration' => __DIR__ . '/../..' . '/integrations/woocommerce/class-woocommerce.php',
        'xrstf\\Composer52\\AutoloadGenerator' => __DIR__ . '/..' . '/xrstf/composer-php52/lib/xrstf/Composer52/AutoloadGenerator.php',
        'xrstf\\Composer52\\Generator' => __DIR__ . '/..' . '/xrstf/composer-php52/lib/xrstf/Composer52/Generator.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit2c420f229f0b44a84053c7de0a411c61_nl::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit2c420f229f0b44a84053c7de0a411c61_nl::$classMap;

        }, null, ClassLoader::class);
    }
}
