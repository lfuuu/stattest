<?php

class IncorrectRequestParametersException extends Exception
{
	public function __construct()
	{
		parent::__construct('Incorrect request parameters');
	}
}