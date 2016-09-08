<?php

/**
 * Validates 'sms:' phone number in URI to included only alphanumeric, hyphens, underscore, and optional leading "+"
 */
class HTMLPurifier_URIScheme_sms extends HTMLPurifier_URIScheme {

    public $browsable = false;
    public $may_omit_host = true;

    public function doValidate(&$uri, $config, $context) {
        $uri->userinfo = null;
        $uri->host     = null;
        $uri->port     = null;
		
		// my legal phone # chars:  alphanumeric, underscore, hyphen, optional "+" for the first character.  That's it.  But you can allow whatever you want.  Just change this:
		$validCalltoPhoneNumberPattern = '/^\+?[a-zA-Z0-9_-]+$/i'; // <---whatever pattern you want to force phone numbers to match
		$proposedPhoneNumber = $uri->path;
		
		if (preg_match($validCalltoPhoneNumberPattern, $proposedPhoneNumber) !== 1) {
			
			// submitted phone # inside the href attribute value looks bad; reject the phone number, and let HTMLpurifier remove the whole href attribute on the submitted <a> tag.
			return false;
		} else {
			// submitted phone # inside the href attribute value looks OK; accept the phone number; HTMLpurifier should NOT strip the href attribute on the submitted <a> tag.
			return true;
		}
    }
}

// vim: et sw=4 sts=4
