<?php

namespace Garmin;

use Garmin\Exception\InvalidStateException;

/**
 * @author Vojtěch Kohout
 */
class Response
{

	const ERROR_MISSING_LOCATION = 1;

	/** @var array */
	private $headers;

	/** @var string */
	private $payload;


	/**
	 * @param array $headers
	 * @param string $payload
	 */
	public function __construct(array $headers, $payload)
	{
		$this->headers = $headers;
		$this->payload = (string) $payload;
	}

	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * @return array
	 */
	public function getCookies()
	{
		$cookies = array();
		foreach ($this->headers as $header) {
			$matches = array();
			if (preg_match('#^set-cookie:\s+(.+)=(.+);#Ui', $header, $matches)) { // TODO: unescape?
				$cookies[$matches[1]] = $matches[2];
			}
		}
		return $cookies;
	}

	public function checkLocationHeader()
	{
		$this->getLocationHeader();
	}

	/**
	 * @throws InvalidStateException
	 * @return string
	 */
	public function getLocationHeader()
	{
		foreach ($this->headers as $header) {
			$matches = array();
			if (preg_match('#^location:\s+(.*)$#i', $header, $matches)) {
				return $matches[1];
			}
		}
		throw new InvalidStateException('Missing expected location header in response.', self::ERROR_MISSING_LOCATION);
	}

	/**
	 * @return string
	 */
	public function getPayload()
	{
		return $this->payload;
	}

	/**
	 * @return array
	 */
	public function getDecodedPayload()
	{
		return json_decode($this->payload, true);
	}

}
