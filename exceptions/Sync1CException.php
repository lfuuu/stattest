<?php

class Sync1CException extends Exception
{
	public function triggerError()
	{
		trigger_error(
			'Ошибка синхронизации с 1С<br/>' . str_replace("\n\n", '<br/>', $this->getMessage())
		);
	}
}