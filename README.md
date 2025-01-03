# WooRewards Add-on - One-Time Redeemable Vouchers

A WordPress plugin and an add-on to [Long Watch Studio](https://plugins.longwatchstudio.com/)'s [WooRewards](https://plugins.longwatchstudio.com/product/woorewards/) (also known as MyRewards), a [WooCommerce](https://woocommerce.com/) plugin.

Add one-time redeemable vouchers as a reward option in your points and rewards system powered by WooRewards for customers to unlock, with the vouchers being issued by using the [QR Planet API](https://qrplanet.com/qr-code-api).

## Features

* Allow customers to unlock one-time redeemable vouchers by spending WooRewards points.
* Issue one-time redeemable vouchers to cutsomers by using the QR Planet API.
* Email voucher redemption link and instructions to customers.

Information for how QR Planet's one-time redeemlable coupons work can be found on [QR Planet's help article](https://qrplanet.com/help/article/one-time-redeemable-qr-code-coupons).

## Requirements

This plugin requires the following WordPress plugins to be isntalled to function:
* [WooCommerce](https://woocommerce.com/)
* PRO version of [WooRewards](https://plugins.longwatchstudio.com/product/woorewards/) (also named MyRewards) by Long Watch Studio

To take full advantage of the QR Planet API, you also need a [QR Planet](https://qrplanet.com/) account.

## Installation

1. Download woorewards-addon-one-time-voucher.zip from the Releases tab of this repository.
1. There are two ways to install this plugin after downloading the plugin's .zip file:
    1. On the WordPress admin dashboard, go to the "Plugins" and then "Add New Plugin" screen, click the "Upload Plugin" button, click the "Browse" button to locate the woorewards-addon-one-time-voucher.zip file you downloaded, then upload the .zip file.
    1. Extract woorewards-addon-one-time-voucher.zip and upload the plugin files to the `/wp-content/plugins` directory of your WordPress website.
1. Activate the plugin through the "Plugins" and "Installed Plugins" screen in the WordPress admin dashboard.

## Instructions

Set up a points and rewards system in the WordPress dashboard's Reward page, by going to the WooRewards settings page and Points and Rewards tab.

After setting up a points and rewards system, you may add a "One-time redeemable voucher" reward, which is located under the "Miscellaneous" reward category. The reward setting popup of one-time redeemable vouchers include the following options:

* Reward title
* Reward description
* Featured Image for the reward
* Points needed to unlock the voucher reward
* QR Planet account domain, API secret key and coupon landing page short URL
* Administrator recipient email for informing an administrator in addition to the customer when the reward is unlocked
* Todo for the email sent to the administrator about the reward unlocked

Ensure customers who unlock the voucher reward will receive an email with voucher redemption link and instructions, by enabling it on both the One-time redeemable voucher reward setting popup and the Emails tab on WooRewards' Appearance page. You may customise the CSS of the email template on the email tab.

Documentations for the WooRewards plugin can be found on [Long Watch Studio's website](https://plugins.longwatchstudio.com/kbtopic/wr/).

## Authors

[Helen Chong](https://helenchong.dev/)

## License

Licensed under [GPLv3](https://www.gnu.org/licenses/gpl-3.0.html) or later.