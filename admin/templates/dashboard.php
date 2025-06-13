<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="wrap">
    <h1><?php esc_html_e('Visitor Analytics', 'wp-visitor-notify'); ?></h1>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php esc_html_e('Date', 'wp-visitor-notify'); ?></th>
                <th><?php esc_html_e('Visits', 'wp-visitor-notify'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $row) : ?>
            <tr>
                <td><?php echo esc_html($row['date'] ?? $row['week'] ?? $row['month']); ?></td>
                <td><?php echo esc_html($row['visits']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
