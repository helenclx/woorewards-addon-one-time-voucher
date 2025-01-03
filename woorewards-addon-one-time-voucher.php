<?php

/**
 * Plugin Name: MyRewards Addon : One-Time Redeemable Voucher
 * Description: Generate one-time redeemable vouchers using the QR Planet API.
 * Author: Helen Chong
 * Author URI: https://helenchong.dev
 * Version: 1.0
 */

 // don't call the file directly
if (!defined('ABSPATH')) exit();

final class LWS_WooRewards_One_Time_Voucher
{
	public static function init()
	{
		static $instance = false;
		if (!$instance) {
			$instance = new self();
			$instance->defineConstants();
			add_filter('lws-ap-release-woorewards-addon-one-time-voucher', function ($rc) {return ($rc . 'pro');});
			add_action('lws_adminpanel_plugins', array($instance, 'plugin'), 100); // priority after main license install
		}
		return $instance;
	}

	private function install()
	{
		spl_autoload_register(array($this, 'autoload'));
		require_once LWS_WOOREWARDS_ONE_TIME_VOUCHER_INCLUDES . '/core/qrplanetapi.php';

		$this->setupEmails();
	}

	/**
	 * Define the plugin constants
	 *
	 * @return void
	 */
	private function defineConstants()
	{
		define('LWS_WOOREWARDS_ONE_TIME_VOUCHER_FILE', __FILE__);

		define('LWS_WOOREWARDS_ONE_TIME_VOUCHER_PATH', dirname(LWS_WOOREWARDS_ONE_TIME_VOUCHER_FILE));
		define('LWS_WOOREWARDS_ONE_TIME_VOUCHER_INCLUDES', LWS_WOOREWARDS_ONE_TIME_VOUCHER_PATH . '/include');

		define('LWS_WOOREWARDS_ONE_TIME_VOUCHER_URL', plugins_url('', LWS_WOOREWARDS_ONE_TIME_VOUCHER_FILE));
		define('LWS_WOOREWARDS_ONE_TIME_VOUCHER_CSS', plugins_url('/styling/css', LWS_WOOREWARDS_ONE_TIME_VOUCHER_FILE));
		define('LWS_WOOREWARDS_ONE_TIME_VOUCHER_DOMAIN', 'woorewards-one-time-voucher');
	}

	/** autoload core and collection classes. */
	public function autoload($class)
	{
		if (substr($class, 0, 19) == 'LWS\WOOREWARDS\PRO\\') {
			$rest = substr($class, 19);
			$publicNamespaces = array(
				'Core', 'Unlockables', 'Mails'
			);
			$publicClasses = array(
				'OneTimeVoucher',
				'QrPlanetApi',
			);

			if (in_array(explode('\\', $rest, 2)[0], $publicNamespaces) || in_array($rest, $publicClasses)) {
				$basename = str_replace('\\', '/', strtolower($rest));
				$filepath = LWS_WOOREWARDS_ONE_TIME_VOUCHER_INCLUDES . '/' . $basename . '.php';
				@include_once $filepath;
				return true;
			}
		}
	}

	/** Register Email templates */
	protected function setupEmails()
	{
		require_once LWS_WOOREWARDS_ONE_TIME_VOUCHER_INCLUDES . '/mails/newonetimevoucher.php';
		new \LWS\WOOREWARDS\PRO\Mails\NewOneTimeVoucher();
	}

	public function plugin()
	{
		if (defined('LWS_WOOREWARDS_ACTIVATED') && LWS_WOOREWARDS_ACTIVATED) {
			$minVers = '4.2.5';
			$this->install();
			if (defined('LWS_WOOREWARDS_PRO_VERSION') && \version_compare(LWS_WOOREWARDS_PRO_VERSION, $minVers, '>=')) {
				$ret = \apply_filters('lws_manager_instance', false);
				if ($ret) {
					$ret->instance->register(__FILE__, 'woorewards', 'woorewards-one-time-voucher');
				}
				\add_action('lws_woorewards_registration', function () {
					\LWS\WOOREWARDS\Abstracts\Unlockable::register('\LWS\WOOREWARDS\PRO\Unlockables\OneTimeVoucher', LWS_WOOREWARDS_ONE_TIME_VOUCHER_INCLUDES . '/unlockables/onetimevoucher.php');
				});
			} elseif (\is_admin()) {
				\lws_admin_add_notice_once('woorewards-one-time-voucher' . '-nolic', sprintf(
					__('The <i>%1$s</i> requires MyRewards Pro %2$s or higher. Please update the WooRewards plugin', 'woorewards-one-time-voucher'),
					\get_plugin_data(__FILE__, false)['Name'],
					$minVers
				), array('level' => 'warning'));
			}
		}
	}
}

LWS_WooRewards_One_Time_Voucher::init();
