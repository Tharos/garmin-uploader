<?php

namespace Garmin;

/**
 * @author Vojtěch Kohout
 */
interface IConnector
{

	/**
	 * @param Request $request
	 * @return Response
	 */
	public function call(Request $request);
	
}
