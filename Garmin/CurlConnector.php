<?php

namespace Garmin;

/**
 * @author Vojtěch Kohout
 */
class CurlConnector implements IConnector
{

	/** @var array */
	private $defaultOptions = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_COOKIESESSION => true,
		CURLOPT_FOLLOWLOCATION => false,
		CURLOPT_HEADER => true,
		CURLOPT_SSL_VERIFYPEER => false,
	);


	/**
	 * @param Request $request
	 * @return Response
	 */
	public function call(Request $request)
	{
		$ch = curl_init($request->getUrl());
		curl_setopt_array($ch, $this->defaultOptions);
		if ($request->hasPayload()) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getPayload());
		}
		curl_setopt_array($ch, array(
			CURLOPT_CUSTOMREQUEST => $request->getMethod(),
			CURLOPT_HTTPHEADER => array_merge(array('Expect:'), $request->exportHeaders())
		));

		$result = curl_exec($ch);
		list($headersString, $payload) = explode("\r\n\r\n", $result, 2);
		$headers = array();
		foreach (explode("\r\n", $headersString) as $i => $header) {
			if ($i === 0) continue;
			$headers[] = $header;
		}
		return new Response($headers, $payload);
	}

}
