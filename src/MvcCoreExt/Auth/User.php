<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license		https://mvccore.github.io/docs/mvccore/3.0.0/LICENCE.md
 */

class MvcCoreExt_Auth_User extends MvcCoreExt_Auth_Abstract_User {

	/** @var bool */
	protected $autoInit = FALSE;

	/** @var MvcCore_Session */
	protected static $session = NULL;

	/**
	 * Try to get user model instance from 
	 * any place by session username record 
	 * if there is any or return null.
	 * @return MvcCoreExt_Auth_User|null
	 */
	public static function GetUserBySession () {
		$result = NULL;
		$session = static::getSession();
		if (!isset($session->uname)) return $result;
		$cfg = MvcCore_Config::GetSystem();
		$allCredentials = $cfg->credentials;
		foreach ($allCredentials as & $credentials) {
			if ($credentials->username === $session->uname) {
				$result = new static();
				$result->setUp(array(
					'UserName'	=> $credentials->username,
					'FullName'	=> $credentials->fullname,
				));
				break;
			}
		}
		return $result;
	}

	/**
	 * Get user instance if the username exists and hashed password is the same
	 * @param string $username
	 * @param string $password 
	 * @return MvcCoreExt_Auth_User|null
	 */
	public static function Authenticate ($uniqueUserName = '', $password = '') {
		$result = NULL;
		$hashedPassword = static::GetPasswordHash($password);
		$cfg = MvcCore_Config::GetSystem();
		$allCredentials = $cfg->credentials;
		foreach ($allCredentials as & $credentials) {
			if ($credentials->username === $uniqueUserName) {
				if ($credentials->password === $hashedPassword) {
					$result = new static();
					$result->setUp(array(
						'UserName'	=> $credentials->username,
						'FullName'	=> $credentials->fullname,
					));
				}
				break;
			}
		}
		return $result;
	}

	/**
	 * Set up unique user name in session namespace
	 * @param MvcCoreExt_Auth_User $user
	 * @return void
	 */
	public static function StoreInSession ($user = NULL) {
		static::GetSession()->uname = $user->UserName;
	}

	/**
	 * Clear unique user name from session
	 * @return void
	 */
	public static function ClearFromSession () {
		static::GetSession()->Destroy();
	}

	/**
	 * Get session to get/set/clear username,
	 * is session is not started - start the session.
	 * @return MvcCore_Session
	 */
	protected static function & getSession () {
		if (is_null(static::$session)) {
			MvcCore::SessionStart(); // start session if not started or do nothing if session has been started already
			static::$session = MvcCore_Session::GetNamespace(__CLASS__);
			static::$session->SetExpirationSeconds(
				MvcCoreExt_Auth::GetInstance()->GetConfig()->expirationSeconds
			);
		}
		return static::$session;
	}
}