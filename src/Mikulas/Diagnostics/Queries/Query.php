<?php

namespace Mikulas\Diagnostics\Queries;

use Nette\Object;


abstract class Query extends Object
{

	/**
	 * User friendly name for Query type
	 * Must be same for the same connection type
	 * @return string
	 */
	abstract public function getName();

	/**
	 * @return string highlit html
	 */
	abstract public function getQuery();

	/**
	 * @return float ms
	 */
	abstract public function getDuration();

	/**
	 * @return int|NULL
	 */
	abstract public function getRowCount();

	/**
	 * @return string hex 00FF00
	 */
	abstract public function getColor();

}
