<?php
namespace LWS\WOOREWARDS\PRO\Mails;

// don't call the file directly
if( !defined( 'ABSPATH' ) ) exit();

/** Setup mail about unlocking one-time redeemable voucher.
 * $data should be an array as:
 *	*	'user' => a WP_User instance
 *	*	'type' => the reward type (origin Unlockable type)
 *	* 'unlockable' => a Unlockable instance
 *	*	'reward' => depends on Unlockable type: WC_Coupon, string, array... */
class NewOneTimeVoucher
{
	protected $template = 'wr_new_one_time_voucher';

	public function __construct()
	{
		add_filter( 'lws_woorewards_mails', array($this, 'addTemplate'), 20 );
		add_filter( 'lws_mail_settings_' . $this->template, array( $this , 'settings' ) );
		add_filter( 'lws_mail_body_' . $this->template, array( $this , 'body' ), 10, 3 );
	}

	public function addTemplate($arr)
	{
		$arr[] = $this->template;
		return $arr;
	}

	public function settings( $settings )
	{
		$settings['domain']        = 'woorewards';
		$settings['settings']      = 'One-Time Redeemable Voucher';
		$settings['settings_name'] = __("One-Time Redeemable Voucher", 'woorewards-pro');
		$settings['about']         = __("Sent to customers when they unlock a one-time redeemable voucher", 'woorewards-pro');
		$settings['subject']       = __("New One-Time Redeemable Voucher Unlocked!", 'woorewards-pro');
		$settings['title']         = __("New One-Time Redeemable Voucher Unlocked", 'woorewards-pro');
		$settings['header']        = __("You have unlocked a one-time redeemable voucher!", 'woorewards-pro');
		$settings['footer']        = __("Powered by MyRewards", 'woorewards-pro');
		$settings['doclink']       = \LWS\WOOREWARDS\PRO\DocLinks::get('emails');
		$settings['icon']          = 'lws-icon-shop';
		$settings['css_file_url']  = LWS_WOOREWARDS_ONE_TIME_VOUCHER_CSS . '/mails/newonetimevoucher.css';
		$settings['fields']['enabled'] = array(
			'id' => 'lws_woorewards_enabled_mail_' . $this->template,
			'title' => __("Enabled", 'woorewards-pro'),
			'type' => 'box',
			'extra'=> array(
				'default' => '',
				'layout' => 'toggle',
			),
		);
		$settings['about'] .= '<br/><span class="lws_wr_email_shortcode_help">'.sprintf(__("Use the shortcode %s to insert the name of the user", 'woorewards-pro'),'<b>[user_name]</b>').'</span>';
		return $settings;
	}


	public function body( $html, $data, $settings )
	{
		if( !empty($html) )
			return $html;
		if( $demo = \is_wp_error($data) )
			$data = $this->placeholders();

		$html = \apply_filters('lws_woorewards_one_time_voucher_custom_type_mail_content', false, $data, $settings, $demo);
		return !empty($html) ? $html : $this->getDefault($data, $settings, $demo);
	}

	protected function getDefault($data, $settings, $demo=false)
	{
		$values = array(
			'title'  => $data['unlockable']->getTitle(),
			'detail' => $data['unlockable']->getCustomDescription()
		);

		$redeem_link = 'https://white.qrd.by/123456';
		if( \is_array($data['reward']) && isset($data['reward']['redeem_url']) && !empty($data['reward']['redeem_url']) )
			$redeem_link = $data['reward']['redeem_url'];

		if( empty($img = $data['unlockable']->getThumbnailImage()) && $demo )
			$img = "<div class='lws-voucher-thumbnail lws-icon lws-icon-image'></div>";

		return <<<EOT
<tr><td class='lws-middle-cell'>
	<table class='lwss_selectable lws-voucher-table' data-type='Voucher Table'>
		<tr>
			<td><div class='lwss_selectable lws-voucher-img' data-type='Voucher Image'>{$img}</div></td>
			<td>
				<div class='lwss_selectable lws-voucher-title' data-type='Voucher Title'>{$values['title']}</div>
				<div class='lwss_selectable lws-voucher-detail' data-type='Voucher Description'>{$values['detail']}</div>
			</td>
		</tr>
	</table>
	<p class='lwss_selectable lws-voucher-instruct' data-type='Voucher Instructions'>Here is the link to your voucher: 👇</p>
	<p class='lwss_selectable lws-voucher-link' data-type='Voucher Link'><a href='{$redeem_link}'>{$redeem_link}</a></p>
	<p class='lwss_selectable lws-voucher-extra' data-type='Voucher Extra Info'>NOTE: This voucher will be expired 30 days after you received this email.</p>
</td></tr>
EOT;
	}

	protected function placeholders()
	{
		$unlockable = \LWS\WOOREWARDS\Collections\Unlockables::instanciate()->create('lws_woorewards_pro_unlockables_onetimevoucher')->last();
		$unlockable->setTitle('One-Time Voucher for Physical Store');
		$unlockable->setDescription('Voucher for our physical store that can only be redeemed once.');
		$user = \wp_get_current_user();

		return array(
			'user' => $user,
			'type' => $unlockable->getType(),
			'unlockable' => $unlockable,
			'reward' => array()
		);
	}
}
