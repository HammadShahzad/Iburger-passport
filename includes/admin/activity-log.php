<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get logs
$log_type = isset($_GET['log_type']) ? sanitize_text_field($_GET['log_type']) : 'all';
$logs = get_option('iburger_activity_log', array());

// Sort by date (newest first)
usort($logs, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Filter by type
if ($log_type !== 'all') {
    $logs = array_filter($logs, function($log) use ($log_type) {
        return $log['type'] === $log_type;
    });
}

// Limit to last 500 entries
$logs = array_slice($logs, 0, 500);
?>

<div class="wrap iburger-admin-wrap">
    <h1>
        <span class="dashicons dashicons-list-view"></span>
        <?php _e('Activity Log', 'iburger-passport'); ?>
    </h1>
    
    <!-- Filter Tabs -->
    <div class="log-filters" style="margin-bottom: 20px;">
        <a href="?page=iburger-passport-logs&log_type=all" class="button <?php echo $log_type === 'all' ? 'button-primary' : ''; ?>">
            <?php _e('All', 'iburger-passport'); ?>
        </a>
        <a href="?page=iburger-passport-logs&log_type=stamp_added" class="button <?php echo $log_type === 'stamp_added' ? 'button-primary' : ''; ?>">
            🎫 <?php _e('Stamps Added', 'iburger-passport'); ?>
        </a>
        <a href="?page=iburger-passport-logs&log_type=stamp_removed" class="button <?php echo $log_type === 'stamp_removed' ? 'button-primary' : ''; ?>">
            ❌ <?php _e('Stamps Removed', 'iburger-passport'); ?>
        </a>
        <a href="?page=iburger-passport-logs&log_type=email_sent" class="button <?php echo $log_type === 'email_sent' ? 'button-primary' : ''; ?>">
            📧 <?php _e('Emails Sent', 'iburger-passport'); ?>
        </a>
        <a href="?page=iburger-passport-logs&log_type=reward_claimed" class="button <?php echo $log_type === 'reward_claimed' ? 'button-primary' : ''; ?>">
            🎁 <?php _e('Rewards Claimed', 'iburger-passport'); ?>
        </a>
    </div>
    
    <?php if (empty($logs)): ?>
        <div class="no-logs" style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
            <span class="dashicons dashicons-list-view" style="font-size: 48px; width: 48px; height: 48px; color: #ccc;"></span>
            <h3><?php _e('No Activity Yet', 'iburger-passport'); ?></h3>
            <p><?php _e('Activity will appear here when customers collect stamps or receive emails.', 'iburger-passport'); ?></p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped" style="background: white;">
            <thead>
                <tr>
                    <th width="150"><?php _e('Date', 'iburger-passport'); ?></th>
                    <th width="80"><?php _e('Type', 'iburger-passport'); ?></th>
                    <th><?php _e('Customer', 'iburger-passport'); ?></th>
                    <th><?php _e('Details', 'iburger-passport'); ?></th>
                    <th width="100"><?php _e('Source', 'iburger-passport'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <?php 
                    $user = get_user_by('id', $log['user_id']);
                    $type_icons = array(
                        'stamp_added' => '🎫',
                        'stamp_removed' => '❌',
                        'email_sent' => '📧',
                        'reward_claimed' => '🎁',
                        'manual_stamp' => '✍️',
                    );
                    $icon = isset($type_icons[$log['type']]) ? $type_icons[$log['type']] : '📋';
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo date('M j, Y', strtotime($log['date'])); ?></strong><br>
                            <span style="color: #888; font-size: 12px;"><?php echo date('g:i A', strtotime($log['date'])); ?></span>
                        </td>
                        <td style="font-size: 20px;"><?php echo $icon; ?></td>
                        <td>
                            <?php if ($user): ?>
                                <strong><?php echo esc_html($user->display_name); ?></strong><br>
                                <span style="color: #888; font-size: 12px;"><?php echo esc_html($user->user_email); ?></span>
                            <?php else: ?>
                                <span style="color: #999;"><?php _e('User Deleted', 'iburger-passport'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($log['details']); ?></td>
                        <td>
                            <span style="background: <?php echo $log['source'] === 'admin' ? '#fee2e2' : '#dcfce7'; ?>; color: <?php echo $log['source'] === 'admin' ? '#dc2626' : '#16a34a'; ?>; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <?php echo $log['source'] === 'admin' ? __('Admin', 'iburger-passport') : __('Customer', 'iburger-passport'); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p style="margin-top: 20px; color: #888; font-size: 12px;">
            <?php printf(__('Showing last %d entries. Older logs are automatically removed.', 'iburger-passport'), count($logs)); ?>
        </p>
    <?php endif; ?>
</div>

