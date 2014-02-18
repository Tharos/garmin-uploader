<?php

namespace Garmin;

use Garmin\Exception\InvalidArgumentException;

/**
 * @author Vojtěch Kohout
 */
class Request
{

	const METHOD_GET = 'GET';

	const METHOD_POST = 'POST';

	/** @var string */
	private $url;

	/** @var string */
	private $method = self::METHOD_GET;

	/** @var array */
	private $headers = array(
		'Content-Type' => 'application/x-www-form-urlencoded',
		'Connection' => 'close',
		'Cookie' => array(),
	);

	/** @var string */
	private $payload;


	/**
	 * @param string $url
	 */
	public function __construct($url)
	{
		$this->setUrl($url);
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param string $url
	 * @throws InvalidArgumentException
	 * @return self
	 */
	public function setUrl($url)
	{
		if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
			throw new InvalidArgumentException('Given argument is not a valid URL (including cheman): ' . $url);
		}
		$this->url = $url;
		return $this;
	}

	/**
	 * @param string $name
	 * @param string|null $value
	 * @throws InvalidArgumentException
	 * @return self
	 */
	public function setHeader($name, $value)
	{
		$name = $this->normalizeHeaderName($name);
		if ($value === null) {
			unset($this->headers[$name]);
			return $this;
		}
		if ($name === 'Cookie') {
			throw new InvalidArgumentException('Do not set Cookies header via setHeader method. Use addCookie instead.');
		}
		$this->headers[$name] = (string) $value;
		return $this;
	}

	/**
	 * @param string $payload
	 * @return self
	 */
	public function setPayload($payload)
	{
		$this->payload = (string) $payload;
		$this->setHeader('Content-Length', strlen($payload));
		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasPayload()
	{
		return $this->payload !== null;
	}

	/**
	 * @return string|null
	 */
	public function getPayload()
	{
		return $this->payload;
	}

	/**
	 * @return bool
	 */
	public function hasCookies()
	{
		return !empty($this->headers['Cookie']);
	}

	/**
	 * @param array $cookies
	 * @return self
	 */
	public function setCookies(array $cookies)
	{
		$this->headers['Cookie'] = array();
		foreach ($cookies as $name => $value) {
			$this->addCookie($name, $value);
		}
		return $this;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @return self
	 */
	public function addCookie($name, $value)
	{
		$this->headers['Cookie'][(string) $name] = (string) $value;
		return $this;
	}

	/**
	 * @param string $method
	 * @throws InvalidArgumentException
	 * @return self
	 */
	public function setMethod($method)
	{
		if ($method !== self::METHOD_GET and $method !== self::METHOD_POST) {
			throw new InvalidArgumentException('Unsupported method give: ' . $method);
		}
		$this->method = $method;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @return array
	 */
	public function exportHeaders()
	{
		$headers = array();
		foreach ($this->headers as $name => $value) {
			if ($name !== 'Cookie') {
				$headers[] = $name . ': ' . $value; // TODO: escape?
			} else {
				$cookies = array();
				foreach ($value as $cookieName => $cookieValue) {
					$cookies[] = $cookieName . '=' . $cookieValue; // TODO: escape?
				}
				if (!empty($cookies)) {
					$headers[] = 'Cookie: ' . implode('; ', $cookies);
				}
			}
		}
		return $headers;
	}

	////////////////////
	////////////////////

	/**
	 * @param string $name
	 * @return string
	 */
	private function normalizeHeaderName($name)
	{
		return implode('-', array_map('ucfirst', explode('-', strtolower($name))));
	}
	
}
