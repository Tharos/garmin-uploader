<?php

namespace Garmin;

use Exception;
use Garmin\Exception\InvalidStateException;

/**
 * @author Vojtěch Kohout
 */
class Uploader
{

	const GARMIN_SIGNIN_URL = 'https://connect.garmin.com/signin';

	const GARMIN_UPLOAD_URL = 'http://connect.garmin.com/proxy/upload-service-1.1/json/upload/.fit';

	/** @var IConnector */
	private $connector;

	/** @var array */
	private $userCredentials;

	/** @var array */
	private $cookies;


	/**
	 * @param IConnector $connector
	 */
	public function __construct(IConnector $connector)
	{
		$this->connector = $connector;
	}

	/**
	 * @param string $userName
	 * @param string $password
	 */
	public function setUserCredentials($userName, $password)
	{
		$this->userCredentials = array((string) $userName, (string) $password);
	}

	/**
	 * @param string $file
	 * @return array
	 */
	public function uploadFit($file)
	{
		if ($this->cookies === null) {
			$this->signIn();
		}
		$request = new Request(self::GARMIN_UPLOAD_URL);
		$request->setMethod(Request::METHOD_POST)
				->setPayload($file)
				->setCookies($this->cookies)
				->setHeader('Content-Type', 'application/octet-stream');

		return $this->connector->call($request)->getDecodedPayload();
	}

	public function forceSignOut()
	{
		$this->cookies = null;
	}

	////////////////////
	////////////////////

	/**
	 * @throws InvalidStateException
	 * @throws Exception
	 */
	private function signIn()
	{
		if ($this->userCredentials === null) {
			throw new InvalidStateException('Missing user credentials. Please call method setUserCredentials before uploading a file.');
		}
		$this->initializeSession();
		try {
			$this->submitSignInForm();
		} catch (Exception $e) {
			if ($e->getCode() === Response::ERROR_MISSING_LOCATION) {
				throw new InvalidStateException('Could not sign in. Please double check used credentials.');
			}
			throw $e;
		}
	}

	private function initializeSession()
	{
		$request = new Request(self::GARMIN_SIGNIN_URL);
		$request->setMethod(Request::METHOD_GET);

		$response = $this->connector->call($request);
		$this->cookies = $response->getCookies();
	}

	private function submitSignInForm()
	{
		$request = new Request(self::GARMIN_SIGNIN_URL);
		$request->setMethod(Request::METHOD_POST)
				->setPayload(http_build_query(array(
					'login' => 'login',
					'login:loginUsernameField' => $this->userCredentials[0],
					'login:password' => $this->userCredentials[1],
					'login:signInButton' => 'Sign in',
					'javax.faces.ViewState' => 'j_id1',
				)))
				->setCookies($this->cookies);

		$this->connector->call($request)->checkLocationHeader(); // here we check that sign up was successful
	}

}
