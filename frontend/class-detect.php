<?php

namespace PWAPP\Frontend;


use PWAPP\Inc\Cookie;
use Mobile_Detect;

/**
 *
 * Main class for detecting the user's device and browser.
 *
 */
class Detect {


	/* ----------------------------------*/
	/* Methods							 */
	/* ----------------------------------*/

	/**
	 *
	 * Check the browser's user agent and return true if the device is a supported smartphone
	 *
	 */
	public function detect_device()
	{

		$is_supported_device = 0;
		$is_supported_os = 0;
		$is_supported_browser = 0;

		$detect = new Mobile_Detect();

		if ($detect->isMobile() && !$detect->isTablet())
			$is_supported_device = 1;

		if ($detect->is('iOS') || $detect->is('AndroidOS') || $detect->is('WindowsPhoneOS') ||  $detect->is('WindowsMobileOS')) {
			$is_supported_os = 1;

		} else {

			// Assume we have FirefoxOS, but this part should be replaced with a proper detection
			if ($detect->isMobile() && $detect->is('Firefox') && stripos(strtolower($_SERVER['HTTP_USER_AGENT']), 'android') === false)
				$is_supported_os = 1;
		}

		if ($detect->is('WebKit') || $detect->is('Firefox') || ($detect->is('IE') && intval($detect->version('IE')) >= 10))
			$is_supported_browser = 1;

		// set load app variable
		$load_app = false;

		if ($is_supported_device && $is_supported_os && $is_supported_browser) {
			$load_app = true;
		}

		// set load app cookie
		$this->set_load_app_cookie(intval($load_app));

		return $load_app;
	}


	/**
	 *
	 * Set the set_load_app_cookie
	 * The cookie is set in a separate method to allow mocking for unit testing.
	 *
	 * @param $value
	 */
	protected function set_load_app_cookie($value)
	{
		$PWAPP_Cookie = new Cookie();
		$PWAPP_Cookie->set_cookie('load_app', $value);
	}
}
