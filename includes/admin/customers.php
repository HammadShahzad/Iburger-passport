<?php
if (!defined('ABSPATH')) {
    exit;
}

// Total countries in system - customer must collect ALL
$all_country_ids = get_posts(array(
    'post_type' => 'burger_country',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'fields' => 'ids'
));
$stamps_required = count($all_country_ids);

// Get all users with stamps
$users = get_users(array(
    'meta_key' => '_iburger_stamps',
    'meta_compare' => 'EXISTS'
));

$customers_data = array();

foreach ($users as $user) {
    $stamps = get_user_meta($user->ID, '_iburger_stamps', true);
    if (!is_array($stamps) || empty($stamps)) {
        continue;
    }
    
    $unique_countries = array_unique(array_column($stamps, 'country_id'));
    $rewards_claimed = get_user_meta($user->ID, '_iburger_rewards_claimed', true);
    $rewards_count = is_array($rewards_claimed) ? count($rewards_claimed) : 0;
    $has_pending = get_user_meta($user->ID, '_iburger_reward_pending', true);
    
    $customers_data[] = array(
        'user' => $user,
        'total_stamps' => count($stamps),
        'unique_countries' => count($unique_countries),
        'rewards_claimed' => $rewards_count,
        'has_pending' => $has_pending,
        'progress' => $stamps_required > 0 ? min(100, round((count($unique_countries) / $stamps_required) * 100)) : 0,
    );
}

// Sort by unique countries desc
usort($customers_data, function($a, $b) {
    return $b['unique_countries'] - $a['unique_countries'];
});
?>

<div class="wrap iburger-admin-wrap">
    <h1>
        <span class="dashicons dashicons-groups"></span>
        <?php _e('Customer Passports', 'iburger-passport'); ?>
    </h1>
    
    <?php if (empty($customers_data)): ?>
        <div class="no-customers">
            <span class="dashicons dashicons-book-alt" style="font-size: 48px; width: 48px; height: 48px; color: #ccc;"></span>
            <h3><?php _e('No Passport Holders Yet', 'iburger-passport'); ?></h3>
            <p><?php _e('Customers will appear here once they start collecting burger stamps!', 'iburger-passport'); ?></p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Customer', 'iburger-passport'); ?></th>
                    <th><?php _e('Email', 'iburger-passport'); ?></th>
                    <th><?php _e('Total Stamps', 'iburger-passport'); ?></th>
                    <th><?php _e('Unique Countries', 'iburger-passport'); ?></th>
                    <th><?php _e('Progress', 'iburger-passport'); ?></th>
                    <th><?php _e('Rewards Claimed', 'iburger-passport'); ?></th>
                    <th><?php _e('Status', 'iburger-passport'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers_data as $data): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($data['user']->display_name); ?></strong>
                            <div class="row-actions">
                                <span class="view">
                                    <a href="<?php echo admin_url('user-edit.php?user_id=' . $data['user']->ID); ?>"><?php _e('View Profile', 'iburger-passport'); ?></a>
                                </span>
                            </div>
                        </td>
                        <td><?php echo esc_html($data['user']->user_email); ?></td>
                        <td><strong><?php echo $data['total_stamps']; ?></strong></td>
                        <td>
                            <strong><?php echo $data['unique_countries']; ?></strong> / <?php echo $stamps_required; ?>
                        </td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?php echo $data['progress']; ?>%;"></div>
                            </div>
                            <span class="progress-text"><?php echo $data['progress']; ?>%</span>
                        </td>
                        <td>
                            <?php if ($data['rewards_claimed'] > 0): ?>
                                <span class="reward-badge"><?php echo $data['rewards_claimed']; ?> 🎁</span>
                            <?php else: ?>
                                <span class="no-rewards">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($data['has_pending']): ?>
                                <span class="status-badge status-pending">🎉 <?php _e('Reward Ready!', 'iburger-passport'); ?></span>
                            <?php elseif ($data['progress'] >= 100): ?>
                                <span class="status-badge status-complete">✅ <?php _e('Completed', 'iburger-passport'); ?></span>
                            <?php elseif ($data['progress'] >= 50): ?>
                                <span class="status-badge status-progress">🍔 <?php _e('Collecting', 'iburger-passport'); ?></span>
                            <?php else: ?>
                                <span class="status-badge status-started">🌱 <?php _e('Started', 'iburger-passport'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php printf(_n('%s customer', '%s customers', count($customers_data), 'iburger-passport'), count($customers_data)); ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.progress-bar-container {
    background: #e0e0e0;
    border-radius: 10px;
    height: 10px;
    width: 100px;
    display: inline-block;
    vertical-align: middle;
    margin-right: 8px;
}
.progress-bar {
    background: linear-gradient(90deg, #f39c12, #e74c3c);
    height: 100%;
    border-radius: 10px;
    transition: width 0.3s ease;
}
.progress-text {
    font-size: 12px;
    color: #666;
}
.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.status-pending {
    background: #fff3cd;
    color: #856404;
}
.status-complete {
    background: #d4edda;
    color: #155724;
}
.status-progress {
    background: #cce5ff;
    color: #004085;
}
.status-started {
    background: #f8f9fa;
    color: #6c757d;
}
.reward-badge {
    background: #e74c3c;
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 12px;
}
.no-rewards {
    color: #ccc;
}
.no-customers {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
}
.no-customers h3 {
    margin-top: 20px;
    color: #666;
}
</style>

