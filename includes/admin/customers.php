<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get all burger countries
$all_countries = get_posts(array(
    'post_type' => 'burger_country',
    'posts_per_page' => -1,
    'post_status' => 'publish'
));
$stamps_required = count($all_countries);

// Get all users with stamps OR all customers if viewing add-stamps
$view_user_id = isset($_GET['manage_user']) ? intval($_GET['manage_user']) : 0;

$users = get_users(array(
    'meta_key' => '_iburger_stamps',
    'meta_compare' => 'EXISTS'
));

// Also search for users to add stamps to
$search_results = array();
if (isset($_GET['search_user']) && !empty($_GET['search_user'])) {
    $search_term = sanitize_text_field($_GET['search_user']);
    $search_results = get_users(array(
        'search' => '*' . $search_term . '*',
        'search_columns' => array('user_login', 'user_email', 'display_name'),
        'number' => 20,
    ));
}

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
        'stamps' => $stamps,
        'unique_country_ids' => $unique_countries,
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

// Get manage user data if set
$manage_user_data = null;
if ($view_user_id) {
    $manage_user = get_user_by('id', $view_user_id);
    if ($manage_user) {
        $stamps = get_user_meta($view_user_id, '_iburger_stamps', true);
        if (!is_array($stamps)) $stamps = array();
        $unique_countries = array_unique(array_column($stamps, 'country_id'));
        
        $manage_user_data = array(
            'user' => $manage_user,
            'stamps' => $stamps,
            'unique_country_ids' => $unique_countries,
        );
    }
}
?>

<div class="wrap iburger-admin-wrap">
    <h1>
        <span class="dashicons dashicons-groups"></span>
        <?php _e('Customer Passports', 'iburger-passport'); ?>
    </h1>
    
    <!-- Search/Add User Section -->
    <div class="search-user-section" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #ddd;">
        <h3 style="margin-top: 0;">🔍 <?php _e('Find Customer to Add Stamps', 'iburger-passport'); ?></h3>
        <form method="get" style="display: flex; gap: 10px; align-items: center;">
            <input type="hidden" name="page" value="iburger-passport-customers">
            <input type="text" name="search_user" value="<?php echo esc_attr($_GET['search_user'] ?? ''); ?>" placeholder="<?php _e('Search by name or email...', 'iburger-passport'); ?>" style="width: 300px; padding: 8px;">
            <button type="submit" class="button button-primary"><?php _e('Search', 'iburger-passport'); ?></button>
            <?php if (!empty($_GET['search_user'])): ?>
                <a href="?page=iburger-passport-customers" class="button"><?php _e('Clear', 'iburger-passport'); ?></a>
            <?php endif; ?>
        </form>
        
        <?php if (!empty($search_results)): ?>
        <div style="margin-top: 15px;">
            <strong><?php _e('Results:', 'iburger-passport'); ?></strong>
            <table class="wp-list-table widefat" style="margin-top: 10px;">
                <tbody>
                    <?php foreach ($search_results as $suser): ?>
                    <tr>
                        <td><strong><?php echo esc_html($suser->display_name); ?></strong></td>
                        <td><?php echo esc_html($suser->user_email); ?></td>
                        <td style="text-align: right;">
                            <a href="?page=iburger-passport-customers&manage_user=<?php echo $suser->ID; ?>" class="button button-small">
                                ✍️ <?php _e('Manage Stamps', 'iburger-passport'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($manage_user_data): ?>
    <!-- Manage User Stamps Panel -->
    <div id="manage-stamps-panel" style="background: white; padding: 25px; border-radius: 8px; margin-bottom: 20px; border: 2px solid #006400;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h2 style="margin: 0; color: #006400;">
                    ✍️ <?php printf(__('Manage Stamps: %s', 'iburger-passport'), esc_html($manage_user_data['user']->display_name)); ?>
                </h2>
                <p style="margin: 5px 0 0; color: #666;"><?php echo esc_html($manage_user_data['user']->user_email); ?></p>
            </div>
            <a href="?page=iburger-passport-customers" class="button">← <?php _e('Back to List', 'iburger-passport'); ?></a>
        </div>
        
        <div class="stamps-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
            <?php foreach ($all_countries as $country): 
                $flag = get_post_meta($country->ID, '_burger_country_flag', true);
                $code = get_post_meta($country->ID, '_burger_country_code', true);
                $has_stamp = in_array($country->ID, $manage_user_data['unique_country_ids']);
            ?>
            <div class="stamp-card" style="background: <?php echo $has_stamp ? '#dcfce7' : '#f5f5f5'; ?>; padding: 15px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 32px;"><?php echo esc_html($flag); ?></span>
                    <div>
                        <strong><?php echo esc_html($country->post_title); ?></strong><br>
                        <span style="color: #888; font-size: 12px;"><?php echo esc_html($code); ?></span>
                    </div>
                </div>
                <div>
                    <?php if ($has_stamp): ?>
                        <span style="color: #16a34a; margin-right: 8px;">✓</span>
                        <button class="button button-small remove-stamp-btn" 
                                data-user="<?php echo $view_user_id; ?>" 
                                data-country="<?php echo $country->ID; ?>"
                                data-name="<?php echo esc_attr($country->post_title); ?>">
                            ❌ <?php _e('Remove', 'iburger-passport'); ?>
                        </button>
                    <?php else: ?>
                        <button class="button button-primary button-small add-stamp-btn" 
                                data-user="<?php echo $view_user_id; ?>" 
                                data-country="<?php echo $country->ID; ?>"
                                data-name="<?php echo esc_attr($country->post_title); ?>">
                            ➕ <?php _e('Add', 'iburger-passport'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (empty($customers_data) && !$manage_user_data): ?>
        <div class="no-customers">
            <span class="dashicons dashicons-book-alt" style="font-size: 48px; width: 48px; height: 48px; color: #ccc;"></span>
            <h3><?php _e('No Passport Holders Yet', 'iburger-passport'); ?></h3>
            <p><?php _e('Customers will appear here once they start collecting burger stamps!', 'iburger-passport'); ?></p>
            <p><?php _e('Use the search above to find customers and add stamps manually.', 'iburger-passport'); ?></p>
        </div>
    <?php elseif (!$manage_user_data): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Customer', 'iburger-passport'); ?></th>
                    <th><?php _e('Email', 'iburger-passport'); ?></th>
                    <th><?php _e('Total Stamps', 'iburger-passport'); ?></th>
                    <th><?php _e('Unique Countries', 'iburger-passport'); ?></th>
                    <th><?php _e('Progress', 'iburger-passport'); ?></th>
                    <th><?php _e('Status', 'iburger-passport'); ?></th>
                    <th><?php _e('Actions', 'iburger-passport'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers_data as $data): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($data['user']->display_name); ?></strong>
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
                        <td>
                            <a href="?page=iburger-passport-customers&manage_user=<?php echo $data['user']->ID; ?>" class="button button-small">
                                ✍️ <?php _e('Manage', 'iburger-passport'); ?>
                            </a>
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
    background: linear-gradient(90deg, #006400, #16a34a);
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

<script>
jQuery(document).ready(function($) {
    var adminNonce = '<?php echo wp_create_nonce('iburger_admin_nonce'); ?>';
    
    $('.add-stamp-btn').on('click', function() {
        var $btn = $(this);
        var userId = $btn.data('user');
        var countryId = $btn.data('country');
        var countryName = $btn.data('name');
        
        if (!confirm('Add stamp "' + countryName + '" to this customer?')) {
            return;
        }
        
        $btn.prop('disabled', true).text('...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'iburger_admin_add_stamp',
                nonce: adminNonce,
                user_id: userId,
                country_id: countryId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                    $btn.prop('disabled', false).text('➕ Add');
                }
            },
            error: function() {
                alert('Error adding stamp');
                $btn.prop('disabled', false).text('➕ Add');
            }
        });
    });
    
    $('.remove-stamp-btn').on('click', function() {
        var $btn = $(this);
        var userId = $btn.data('user');
        var countryId = $btn.data('country');
        var countryName = $btn.data('name');
        
        if (!confirm('Remove stamp "' + countryName + '" from this customer?')) {
            return;
        }
        
        $btn.prop('disabled', true).text('...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'iburger_admin_remove_stamp',
                nonce: adminNonce,
                user_id: userId,
                country_id: countryId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                    $btn.prop('disabled', false).text('❌ Remove');
                }
            },
            error: function() {
                alert('Error removing stamp');
                $btn.prop('disabled', false).text('❌ Remove');
            }
        });
    });
});
</script>
