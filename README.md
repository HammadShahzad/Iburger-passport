# 🍔 iBurger Passport Loyalty Plugin

A creative WordPress/WooCommerce loyalty plugin where customers collect burger stamps from different countries on their digital passport. Complete your passport and earn FREE rewards!

## Features

- **🌍 Animated Passport Interface** - Beautiful vintage-style passport with page-flip animations
- **📬 Country-Based Stamps** - Create different burger countries (American, Mexican, Italian, etc.)
- **🔗 Product Linking** - Link WooCommerce products to specific burger countries
- **✅ Automatic Stamp Collection** - Stamps are automatically added when orders complete
- **🔢 Manual Order Verification** - Customers can manually add orders to claim stamps
- **🎁 Free Product Rewards** - After collecting X unique stamps, customers get a free product
- **📱 Mobile Responsive** - Works beautifully on all devices
- **🎨 Customizable** - Change passport titles, stamps required, and reward products

## Installation

1. Upload the `iburger-passport-loyalty` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **iBurger Passport** in the admin menu to configure

## Setup Guide

### Step 1: Create Burger Countries

1. Go to **iBurger Passport → Burger Countries → Add New**
2. Enter the country name (e.g., "American Classic")
3. Add a country code (e.g., "USA")
4. Add a flag emoji (e.g., 🇺🇸)
5. Optionally upload a custom stamp image
6. Link WooCommerce products that earn this stamp
7. Publish the country

### Step 2: Configure Reward Settings

1. Go to **iBurger Passport → Settings**
2. Set how many unique stamps are needed (e.g., 6)
3. Select the reward product (the free item customers receive)
4. Customize passport title and messages
5. Save settings

### Step 3: Display the Passport

The passport automatically appears in WooCommerce **My Account** under "🍔 My Passport".

You can also use the shortcode anywhere:

```
[iburger_passport]
```

## How It Works

1. **Customer Orders** - Customer purchases a burger product
2. **Order Completes** - When order status changes to "Completed"
3. **Stamp Added** - The corresponding country stamp is automatically added
4. **Collect Stamps** - Customer collects stamps from different countries
5. **Earn Reward** - After reaching the goal, customer claims their coupon
6. **Free Product** - Coupon gives 100% off the reward product

## Passport Features

- **Page Navigation** - Click arrows or swipe to flip pages
- **Keyboard Support** - Use arrow keys to navigate
- **Stamp Animations** - Stamps appear with realistic ink-stamp effect
- **Progress Tracker** - Visual progress bar showing journey completion
- **Reward Celebration** - Confetti animation when unlocking rewards
- **Coupon Management** - View and copy reward coupons

## Admin Features

- **Dashboard** - Overview of countries, stamps, and active users
- **Customer Passports** - View all customers and their progress
- **Settings** - Configure stamps required and reward product
- **Burger Countries** - Manage all country stamps and linked products

## Example Country Setup

| Country Name | Code | Emoji | Products |
|-------------|------|-------|----------|
| American Classic | USA | 🇺🇸 | Classic Burger, BBQ Burger |
| Mexican Fiesta | MEX | 🇲🇽 | Jalapeño Burger, Taco Burger |
| Italian Dream | ITA | 🇮🇹 | Caprese Burger, Marinara Burger |
| French Gourmet | FRA | 🇫🇷 | Brie Burger, Truffle Burger |
| Japanese Fusion | JPN | 🇯🇵 | Teriyaki Burger, Wasabi Burger |
| Australian Outback | AUS | 🇦🇺 | Outback Burger, Beetroot Burger |

## Requirements

- WordPress 5.8+
- WooCommerce 5.0+
- PHP 7.4+

## Frequently Asked Questions

**Q: Can customers get the same stamp multiple times?**
A: Yes! The stamp count shows how many times they've visited each country.

**Q: Do stamps count toward the goal if they're duplicates?**
A: No, only unique country stamps count toward the reward.

**Q: What happens when a customer claims their reward?**
A: They receive a unique coupon code for 100% off the reward product, valid for 30 days.

**Q: Can customers earn multiple rewards?**
A: Yes! After claiming one reward, they can continue collecting and earn another.

**Q: Can I customize the stamp appearance?**
A: Yes, you can upload custom stamp images for each country.

## Support

For support, please contact [your support email/link].

## Changelog

### 1.0.5
- Updated color scheme to match International Burgers brand (Green/White)
- Fixed text overflow issues for long email addresses on passport
- Improved button styling and shadow effects
- Switched to clean light background theme

### 1.0.4
- Fixed UI clipping issues on mobile devices
- Added dynamic height adjustment for passport pages
- Optimized animations for better performance
- Refined responsive grid layout (4 stamps per page)

### 1.0.3
- Complete UI/UX redesign with premium dark theme
- Modern glassmorphism effects and smooth animations
- Better typography with Cormorant Garamond & Outfit fonts
- Improved stamp cards with colored variants
- Enhanced progress tracking visualization
- Mobile-optimized responsive design

### 1.0.2
- Fixed WooCommerce endpoint using proper woocommerce_get_query_vars filter
- Registered endpoint at priority 0 for proper timing
- More robust endpoint registration

### 1.0.1
- Fixed WooCommerce endpoint 404 issue
- Added query vars for proper URL routing
- Improved permalink flush on activation

### 1.0.0
- Initial release
- Animated passport interface
- Stamp collection system
- Reward coupon generation
- WooCommerce integration
- Admin dashboard and settings

---

Made with ❤️ for burger lovers everywhere!

# Iburger-passport
