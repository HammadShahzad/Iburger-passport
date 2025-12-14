<?php
/**
 * Stamp Added Email Template
 * 
 * @var string $customer_name
 * @var array $stamps_added
 * @var int $total_collected
 * @var int $total_countries
 * @var string $passport_url
 */

if (!defined('ABSPATH')) exit;

$site_name = get_bloginfo('name');
$primary_color = '#006400';
$accent_color = '#C9A227';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($site_name); ?> - New Stamp Added!</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, <?php echo $primary_color; ?> 0%, #004d00 100%); padding: 40px 40px 30px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">🍔</div>
                            <h1 style="color: <?php echo $accent_color; ?>; font-size: 28px; margin: 0; font-weight: 700; letter-spacing: 2px;">NEW STAMP ADDED!</h1>
                            <p style="color: #ffffff; font-size: 14px; margin: 12px 0 0; opacity: 0.9;">Your passport is growing</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #333; font-size: 16px; line-height: 1.6; margin: 0 0 24px;">
                                Hi <strong><?php echo esc_html($customer_name); ?></strong>,
                            </p>
                            <p style="color: #333; font-size: 16px; line-height: 1.6; margin: 0 0 32px;">
                                Great news! You've just added new stamp(s) to your Burger Passport:
                            </p>
                            
                            <!-- Stamps Added -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 32px;">
                                <?php foreach ($stamps_added as $stamp): ?>
                                <tr>
                                    <td style="background: #f9f9f9; border-radius: 12px; padding: 20px; margin-bottom: 12px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td width="60" style="font-size: 40px; vertical-align: middle;">
                                                    <?php echo esc_html($stamp['flag']); ?>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <div style="color: <?php echo $primary_color; ?>; font-size: 18px; font-weight: 700; margin-bottom: 4px;">
                                                        <?php echo esc_html($stamp['name']); ?>
                                                    </div>
                                                    <div style="color: #888; font-size: 12px; letter-spacing: 2px; text-transform: uppercase;">
                                                        <?php echo esc_html($stamp['code']); ?>
                                                    </div>
                                                </td>
                                                <td width="60" style="text-align: right; vertical-align: middle;">
                                                    <span style="background: <?php echo $primary_color; ?>; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">✓ ADDED</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td style="height: 12px;"></td></tr>
                                <?php endforeach; ?>
                            </table>
                            
                            <!-- Progress -->
                            <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 32px;">
                                <div style="color: <?php echo $primary_color; ?>; font-size: 14px; font-weight: 600; margin-bottom: 8px; letter-spacing: 1px;">YOUR PROGRESS</div>
                                <div style="color: <?php echo $primary_color; ?>; font-size: 36px; font-weight: 700;">
                                    <?php echo intval($total_collected); ?> / <?php echo intval($total_countries); ?>
                                </div>
                                <div style="color: #666; font-size: 14px; margin-top: 8px;">countries collected</div>
                                
                                <?php if ($total_collected >= $total_countries): ?>
                                <div style="background: <?php echo $accent_color; ?>; color: white; padding: 12px 24px; border-radius: 8px; display: inline-block; margin-top: 16px; font-weight: 600;">
                                    🎉 REWARD UNLOCKED!
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- CTA Button -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <a href="<?php echo esc_url($passport_url); ?>" style="display: inline-block; background: <?php echo $primary_color; ?>; color: white; text-decoration: none; padding: 16px 40px; border-radius: 50px; font-size: 16px; font-weight: 600; letter-spacing: 1px;">
                                            VIEW MY PASSPORT →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f9f9f9; padding: 30px 40px; text-align: center; border-top: 1px solid #eee;">
                            <p style="color: #888; font-size: 13px; margin: 0 0 8px;">
                                Keep collecting stamps to unlock your free reward!
                            </p>
                            <p style="color: #aaa; font-size: 12px; margin: 0;">
                                <?php echo esc_html($site_name); ?> • Burger Passport Loyalty Program
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

