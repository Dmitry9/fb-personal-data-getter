<?php

namespace App;

use \Facebook\Facebook;
use \Facebook\Exceptions\FacebookResponseException;
use \Facebook\Exceptions\FacebookSDKException;
use \Exception;

class FbConnector {

	private $accessToken = '';
	private $fb = null;
	private $helper = null;
	private $redirectionURL = '';
	private $fbPermissions = [];
	private $fbUserProfile = '';
	private $tokenName = '';

	/**
	 * FbConnector constructor.
	 *
	 * @param Config $oConfig
	 *
	 * @throws Exception
	 */
	public function __construct(Config $oConfig) {
		$config = $oConfig->get('fb');
		//Setup Facebook SDK
		$this->fb = new Facebook(array(
			'app_id' => $config['AppId'],
			'app_secret' => $config['AppSecret'],
			'default_graph_version' => $config['GraphVersion'],
		));
		if ( ! empty( $config['FbPermissions'] ) ) {
			$this->fbPermissions = $config['FbPermissions'];
		}
		$this->redirectionURL = $config['RedirectionURL'];
		$this->tokenName = $config['TokenName'];

		// Get redirect login helper
		$this->helper = $this->fb->getRedirectLoginHelper();

		// Try to get access token
		try {
			if (isset($_SESSION[$this->tokenName])) {
				$this->accessToken = $_SESSION[$this->tokenName];
			} else {
				$this->accessToken = $this->helper->getAccessToken();
			}
		} catch (FacebookResponseException $e) {
			throw new Exception('Graph returned an error: ' . $e->getMessage());
		} catch (FacebookSDKException $e) {
			throw new Exception('Facebook SDK returned an error: ' . $e->getMessage());
		}
	}

	/**
	 * Get access token
	 * @return \Facebook\Authentication\AccessToken|null|string
	 */
	public function getAccessToken() {
		return $this->accessToken;
	}

	/**
	 * Set default access token to be used in the script
	 */
	public function checkAccessToken() {
		if (!isset($_SESSION[$this->tokenName])) {
			// Put short-lived access token in session
			$_SESSION[$this->tokenName] = (string) $this->accessToken;

			// OAuth 2.0 client handler helps to manage access tokens
			$oAuth2Client = $this->fb->getOAuth2Client();

			// Exchanges a short-lived access token for a long-lived one
			$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION[$this->tokenName]);
			$_SESSION[$this->tokenName] = (string) $longLivedAccessToken;
		}
		$this->fb->setDefaultAccessToken($_SESSION[$this->tokenName]);
	}

	/**
	 * Getting user facebook profile info
	 * @return array|string
	 * @throws Exception
	 */
	public function getFbUserData() {
		try {
			$profileRequest = $this->fb->get('/me?fields=name,first_name,last_name,email,link,gender,locale,picture');
			$this->fbUserProfile = $profileRequest->getGraphNode()->asArray();
		} catch (FacebookResponseException $e) {
			session_destroy();
			throw new Exception('Graph returned an error: ' . $e->getMessage());
		} catch (FacebookSDKException $e) {
			throw new Exception('Facebook SDK returned an error: ' . $e->getMessage());
		}
		return $this->fbUserProfile;
	}
	
	/**
	 * Get login url
	 * @return string
	 */
	public function getLoginUrl() {
		return $this->helper->getLoginUrl($this->redirectionURL, $this->fbPermissions);
	}

	/**
	 * Get logout url
	 * @return string
	 */
	public function getLogoutUrl() {
		return $this->helper->getLogoutUrl($this->accessToken, $this->redirectionURL.'logout.php');
	}

	/**
	 * Logout user from FB
	 */
	public function logOut() {
		// Remove access token from session
		unset($_SESSION[$this->tokenName]);
	}
}