<?php defined('ABSPATH') || exit; ?>

<h3><?php echo esc_html__('Your Newsletter Account', 'newsletter-for-wp'); ?></h3>
<p><?php echo esc_html__('The table below shows your Newsletter audiences and their details. If you just applied changes to your Newsletter account, please use the following button to renew the cache.', 'newsletter-for-wp'); ?></p>

<div id="nl4wp-list-fetcher">
    <form method="post" action="">
        <input type="hidden" name="_nl4wp_action" value="empty_lists_cache" />
        <?php wp_nonce_field('_nl4wp_action'); ?>
        <p>
            <input type="submit" value="<?php echo esc_attr__('Renew Newsletter audiences', 'newsletter-for-wp'); ?>" class="button">
        </p>
    </form>
</div>

<div class="nl4wp-lists-overview">
    <?php
    if (empty($lists)) {
        ?>
        <p><?php echo esc_html__('No audiences were found in your Newsletter account', 'newsletter-for-wp'); ?>.</p>
        <?php
    } else {
        // translators: %d is the number of Newsletter audiences found.
        echo '<p>', sprintf(esc_html__('A total of %d audiences were found in your Newsletter account.', 'newsletter-for-wp'), count($lists)), '</p>';
        echo '<table class="widefat striped" id="nl4wp-newsletter-lists-overview">';

        $headings = [
            esc_html__('Audience name', 'newsletter-for-wp'),
            esc_html__('Audience ID', 'newsletter-for-wp'),
            esc_html__('# of contacts', 'newsletter-for-wp'),
        ];

        echo '<thead>';
        echo '<tr>';
        foreach ($headings as $heading) {
            echo '<th>', esc_html($heading), '</th>';
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($lists as $list) {
            echo '<tr>';
            echo '<td><a href="#" class="nl4wp-newsletter-list" data-list-id="', esc_attr($list->id), '">', esc_html($list->name), '</a><span class="row-actions alignright"></span></td>';
            echo '<td><code>', esc_html($list->id), '</code></td>';
            echo '<td>', esc_html($list->stats->member_count), '</td>';
            echo '</tr>';

            echo '<tr class="list-details list-', esc_attr($list->id), '-details" style="display: none;">';
            echo '<td colspan="3" style="padding: 0 20px 40px;">';

            echo '<div><div>', esc_html__('Loading... Please wait.', 'newsletter-for-wp'), '</div></div>';
            echo '</td>';
            echo '</tr>';
            ?>
            <?php
        } // end foreach $lists
        echo '</tbody>';
        echo '</table>';
    } // end if empty
    ?>
</div>
