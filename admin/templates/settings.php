<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="wrap">
    <h1><?php esc_html_e('WP Visitor Notify Settings', 'wp-visitor-notify'); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('wpvn_settings_group');
        $opts = get_option('wpvn_settings', []);
        ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Enable Tracking', 'wp-visitor-notify'); ?></th>
                <td><input type="checkbox" name="wpvn_settings[enabled]" value="1" <?php checked($opts['enabled'] ?? 0, 1); ?>></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
