<?php
/**
 * Abstract class in /BazeZF/Error/Debugger
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

abstract class BaseZF_Error_Debugger_Abstract
{
	protected $_exception;

	public function __construct(Exception $exception)
	{
		$this->_exception = $exception;

		return $this->_render();
	}

	abstract protected function _render();
}

