<?php

namespace Garmin;

/**
 * @author Vojtěch Kohout
 */
class DirectConnector implements IConnector
{

	/** @var array */
	private $defaultHttpContextParams = array(
		'protocol_version'=>'1.1',
		'follow_location' => false,
	);

	/**
	 * @param Request $request
	 * @return Response
	 */
	public function call(Request $request)
	{
		$params = $this->defaultHttpContextParams;
		$params['method'] = $request->getMethod();
		$params['header'] = implode("\r\n", $request->exportHeaders());
		if ($request->hasPayload()) {
			$params['content'] = $request->getPayload();
		}
		$context = stream_context_create(array('http' => $params));

		$payload = file_get_contents($request->getUrl(), null, $context);

		return new Response($http_response_header, $payload);
	}

}
