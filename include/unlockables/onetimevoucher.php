<?php
namespace LWS\WOOREWARDS\PRO\Unlockables;

// don't call the file directly
if( !defined( 'ABSPATH' ) ) exit();

include_once LWS_WOOREWARDS_ONE_TIME_VOUCHER_INCLUDES . '/core/qrplanetapi.php';

/* Create a one-time redeemable voucher. */
class OneTimeVoucher extends \LWS\WOOREWARDS\Abstracts\Unlockable
{
	private $apiDomain = '';
	private $secretKey = '';
	private $shortUrl = '';
	private $todo = '';
	private $adminEmail = false;

	function callQrPlanetApi()
	{
		$qr_planet_api = new \LWS\WOOREWARDS\PRO\Core\QrPlanetApi();
		$redeem_link = $qr_planet_api->issueOneTimeCoupon(
			$this->getApiDomain(),
			$this->getSecretKey(),
			$this->getShortUrl()
		);
		return $redeem_link;
	}

	function getInformation()
	{
		return array_merge(parent::getInformation(), array(
			'icon'  => 'lws-icon-shop',
			'short' => __("Allow your customers to claim a voucher that can be redeemed one-time only by using the QR Planet API.", 'woorewards-pro'),
			'help'  => __("View QR Planet's Coupon API documentation.", 'woorewards-pro'),
		));
	}

	function getData($min=false)
	{
		$prefix = $this->getDataKeyPrefix();
		$data = parent::getData();
		$data[$prefix.'todo'] = $this->getTodo();
		$data[$prefix.'apidomain'] = $this->getApiDomain();
		$data[$prefix.'secretkey'] = $this->getSecretKey();
		$data[$prefix.'shorturl'] = $this->getShortUrl();
		$data[$prefix.'dest'] = $this->getAdminEmail();
		return $data;
	}

	function getForm($context='editlist')
	{
		$prefix = $this->getDataKeyPrefix();
		$form = parent::getForm($context);
		$form .= $this->getFieldsetBegin(2, __("Administration", 'woorewards-pro'), 'col50');

		// API domain
		$label   = __("QR Planet account domain", 'woorewards-pro');
		$holder  = "https://white.qrd.by";
		$tooltip = sprintf(__("Enter the domain of the your QR Planet account, with http:// or https://. You may use the custom domain of your QR Planet account manager, if available. No trailing slash (/) at the end. Example: $holder.", 'woorewards-pro'), 'none');
		$form .= <<<EOT
<div class='field-help'>{$tooltip}</div>
<div class='lws-{$context}-opt-title label'>{$label}<div class='bt-field-help'>?</div></div>
<div class='lws-{$context}-opt-input value'>
	<input type='text' id='{$prefix}apidomain' name='{$prefix}apidomain' placeholder='{$holder}' />
</div>
EOT;

		// QR Planet API secret key
		$label   = __("QR Planet API secret key", 'woorewards-pro');
		$form .= "<div class='lws-$context-opt-title label'>$label</div>";
		$form .= "<div class='lws-$context-opt-input value'>";
		$form .= "<input type='text' id='{$prefix}secretkey' name='{$prefix}secretkey'>";
		$form .= "</div>";

		// QR Planet coupon short URL
		$label   = __("QR Planet coupon landing page short URL", 'woorewards-pro');
		$holder  = "qr-code-platform";
		$tooltip = sprintf(__("Enter the short URL of your QR Planet coupon landing page. Example: $holder.", 'woorewards-pro'), 'none');
		$form .= <<<EOT
<div class='field-help'>{$tooltip}</div>
<div class='lws-{$context}-opt-title label'>{$label}<div class='bt-field-help'>?</div></div>
<div class='lws-{$context}-opt-input value'>
	<input type='text' id='{$prefix}shorturl' name='{$prefix}shorturl' placeholder='{$holder}' />
</div>
EOT;

		// recipient
		$label   = __("Administrator recipient", 'woorewards-pro');
		$tooltip = sprintf(__("Who to inform, in addition to the customer, when a customer unlocks the reward. Default is the Website administrator. Set <b>%s</b> for no administrator email.", 'woorewards-pro'), 'none');
		$holder  = \esc_attr(\get_option('admin_email'));
		$form .= <<<EOT
<div class='field-help'>{$tooltip}</div>
<div class='lws-{$context}-opt-title label'>{$label}<div class='bt-field-help'>?</div></div>
<div class='lws-{$context}-opt-input value'>
	<input type='text' id='{$prefix}dest' name='{$prefix}dest' placeholder='{$holder}' />
</div>
EOT;

		// todo
		$label = _x("Todo", "OneTimeVoucher", 'woorewards-pro');
		$placeholder = \esc_attr(\apply_filters('the_wre_unlockable_description', $this->getDescription('edit'), $this->getId()));
		$form .= "<div class='lws-$context-opt-title label'>$label</div>";
		$form .= "<div class='lws-$context-opt-input value'>";
		$form .= "<textarea id='{$prefix}todo' name='{$prefix}todo' placeholder='$placeholder'></textarea>";
		$form .= "</div>";

		$form .= $this->getFieldsetEnd(2);
		return $form;
	}

	function submit($form=array(), $source='editlist')
	{
		$prefix = $this->getDataKeyPrefix();
		$values = \apply_filters('lws_adminpanel_arg_parse', array(
			'post'     => ($source == 'post'),
			'values'   => $form,
			'format'   => array(
				$prefix.'apidomain' => 't',
				$prefix.'secretkey' => 't',
				$prefix.'shorturl' => 't',
				$prefix.'todo' => 't',
				$prefix.'dest' => 't',
			),
			'defaults' => array(
				$prefix.'apidomain' => '',
				$prefix.'secretkey' => '',
				$prefix.'shorturl' => '',
				$prefix.'todo' => '',
				$prefix.'dest' => '',
			),
			'labels'   => array(
				$prefix.'apidomain' => _x("QR Planet account domain", "OneTimeVoucher", 'woorewards-pro'),
				$prefix.'secretkey' => _x("QR Planet API secret key", "OneTimeVoucher", 'woorewards-pro'),
				$prefix.'shorturl' => _x("QR Planet coupon landing page short URL", "OneTimeVoucher", 'woorewards-pro'),
				$prefix.'todo' => _x("Todo", "OneTimeVoucher", 'woorewards-pro'),
				$prefix.'dest' => _x("Administrator recipient", "OneTimeVoucher", 'woorewards-pro'),
			)
		));
		if( !(isset($values['valid']) && $values['valid']) )
			return isset($values['error']) ? $values['error'] : false;

		$valid = parent::submit($form, $source);
		if( $valid === true )
		{
			$this->setApiDomain($values['values'][$prefix.'apidomain']);
			$this->setSecretKey($values['values'][$prefix.'secretkey']);
			$this->setShortUrl($values['values'][$prefix.'shorturl']);
			$this->setTodo($values['values'][$prefix.'todo']);
			$this->setAdminEmail($values['values'][$prefix.'dest']);
		}
		return $valid;
	}

	public function getApiDomain()
	{
		return $this->apiDomain;
	}

	public function setApiDomain($apiDomain='')
	{
		$this->apiDomain = $apiDomain;
		return $this;
	}

	public function getSecretKey()
	{
		return $this->secretKey;
	}

	public function setSecretKey($secretKey='')
	{
		$this->secretKey = $secretKey;
		return $this;
	}

	public function getShortUrl($shortUrl='')
	{
		return $this->shortUrl;
	}

	public function setShortUrl($shortUrl='')
	{
		$this->shortUrl = $shortUrl;
		return $this;
	}

	public function getTodo()
	{
		return $this->todo;
	}

	public function setTodo($todo='')
	{
		$this->todo = $todo;
		return $this;
	}

	public function setTestValues()
	{
		$this->setTodo(__("This is a test. Just ignore it.", 'woorewards-pro'));
		return $this;
	}

	protected function _fromPost(\WP_Post $post)
	{
		$this->setApiDomain(\get_post_meta($post->ID, 'woorewards_custom_apidomain', true));
		$this->setSecretKey(\get_post_meta($post->ID, 'woorewards_custom_secretkey', true));
		$this->setShortUrl(\get_post_meta($post->ID, 'woorewards_custom_shorturl', true));
		$this->setTodo(\get_post_meta($post->ID, 'woorewards_custom_todo', true));
		$this->setAdminEmail(\get_post_meta($post->ID, 'woorewards_custom_dest', true));
		return $this;
	}

	protected function _save($id)
	{
		\update_post_meta($id, 'woorewards_custom_apidomain', $this->getApiDomain());
		\update_post_meta($id, 'woorewards_custom_secretkey', $this->getSecretKey());
		\update_post_meta($id, 'woorewards_custom_shorturl', $this->getShortUrl());
		\update_post_meta($id, 'woorewards_custom_todo', $this->getTodo());
		\update_post_meta($id, 'woorewards_custom_dest', $this->getAdminEmail());
		return $this;
	}

	protected function getAdminEmail()
	{
		return (string)$this->adminEmail;
	}

	protected function setAdminEmail($email)
	{
		$this->adminEmail = $email;
	}

	public function createReward(\WP_User $user, $demo=false)
	{
		/** sends a mail to the administrator with the user information
		 * and the Text specified in the loyalty grid */
		if( !$demo )
		{
			$admin = $this->getAdminEmail();
			if (!$admin)
				$admin = \get_option('admin_email');
			if( \is_email($admin) )
			{
				$body = '<p>' . __("A user unlocked a one-time redeemable voucher.", 'woorewards-pro');
				$body .= '<br/><h2>' . $this->getTitle() . '</h2>';
				$body .= '<h3>' . $this->getCustomDescription() . '</h3></p>';

				$body .= '<p>' . __("It is now up to you to:", 'woorewards-pro');
				$body .= '<blockquote>' . $this->getTodo() . '</blockquote></p>';

				$body .= '<p>' . __("The recipient is:", 'woorewards-pro') . '<ul>';
				$body .= sprintf("<li>%s : <b>%s</b></li>", __("E-mail", 'woorewards-pro'), $user->user_email);
				if( !empty($user->user_login) )
					$body .= sprintf("<li>%s : <b>%s</b></li>", __("Login", 'woorewards-pro'), $user->user_login);
				if( !empty($user->display_name) )
					$body .= sprintf("<li>%s : <b>%s</b></li>", __("Name", 'woorewards-pro'), $user->display_name);
				if( !empty($addr = $this->getShippingAddr($user, 'shipping')) )
					$body .= sprintf("<li>%s : <div>%s</div></li>", __("Shipping address", 'woorewards-pro'), implode('<br/>', $addr));
				if( !empty($addr = $this->getShippingAddr($user, 'billing')) )
					$body .= sprintf("<li>%s : <div>%s</div></li>", __("Billing address", 'woorewards-pro'), implode('<br/>', $addr));
				$body .= '</ul></p>';

				\wp_mail(
					$admin,
					__("A customer unlocked the following reward: ", 'woorewards-pro') . $this->getTitle(),
					$body,
					array('Content-Type: text/html; charset=UTF-8')
				);
			}
			elseif ('none' != $admin) {
				error_log("Cannot get a valid administrator email (see options 'admin_email')");
			}
		}

		// Return reward data array that can be used in the email sent to customers
		return array(
			'todo' => $this->getTodo(),
			'redeem_url' => $this->callQrPlanetApi()
		);
	}

	/** @param $usage must be 'billing' or 'shipping' */
	protected function getShippingAddr($user, $usage='shipping')
	{
		$fname     = \get_user_meta( $user->ID, 'first_name', true );
		$lname     = \get_user_meta( $user->ID, 'last_name', true );
		$address_1 = \get_user_meta( $user->ID, $usage . '_address_1', true );
		$city      = \get_user_meta( $user->ID, $usage . '_city', true );

		if( !(empty($address_1) || empty($city)) )
		{
			$postcode = \get_user_meta( $user->ID, $usage . '_postcode', true );
			if( !empty($postcode) )
				$city = $postcode . " " . $city;

			$country = \get_user_meta( $user->ID, $usage . '_country', true );
			$state = \get_user_meta( $user->ID, $usage . '_state', true );
			static $countries = array();
			static $states = array();
			if( empty($countries) && \LWS\Adminpanel\Tools\Conveniences::isWC() )
			{
				try{
					@include_once WP_PLUGIN_DIR . '/woocommerce/includes/class-wc-countries.php';
					$countries = \WC()->countries->countries;
					$states = \WC()->countries->states;
					if( isset($countries[$country]) )
					{
						if( isset($states[$country]) )
						{
							$lstates = $states[$country];
							if( isset($lstates[$state]) )
								$state = $lstates[$state];
						}
						$country = $countries[$country];
					}
				}catch (\Exception $e){
					error_log($e->getMessage());
				}
			}

			return array(
				$fname . ' ' . $lname,
				$address_1,
				\get_user_meta( $user->ID, $usage . '_address_2', true ),
				$city,
				$country,
				$state
			);
		}
		return array();
	}

	public function getDisplayType()
	{
		return _x("One-time redeemable voucher", "getDisplayType", 'woorewards-pro');
	}

	/** For point movement historic purpose. Can be override to return a reason.
	 *	Last generated coupon code is consumed by this function. */
	public function getReason($context='backend')
	{
		return $this->getCustomDescription();
	}

	/**	Event categories, used to filter out events from pool options.
	 *	@return array with category_id => category_label. */
	public function getCategories()
	{
		return array_merge(parent::getCategories(), array(
			'sponsorship' => _x("Referee", "unlockable category", 'woorewards-pro'),
			'miscellaneous' => __("Miscellaneous", 'woorewards-pro')
		));
	}

	/* When a one-time redeemable voucher is unlocked
	 * Override the apply() function in \LWS\WOOREWARDS\Abstracts\Unlockable
	*/
	function apply(\WP_User $user, $mailTemplate = 'wr_new_one_time_voucher')
	{
		$reward = $this->createReward($user);
		if ($reward !== false) {
			$this->incrRedeemCount($user->ID);
			$this->sendMail($user, $reward, $mailTemplate);
			return true;
		}
		return false;
	}
}