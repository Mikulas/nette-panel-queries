<?php

namespace Mikulas\Diagnostics\Queries;

use dibi;
use DibiEvent;


class DibiQuery extends Query
{

	/** @var DibiEvent */
	protected $event;

	public function __construct($event)
	{
		$this->event = $event;
	}

	/**
	 * @return string highlit hmtl
	 */
	public function getQuery()
	{
		return dibi::dump($this->event->sql, TRUE);
	}

	/**
	 * @return float ms
	 */
	public function getDuration()
	{
		return $this->event->time * 1000;
	}

	/**
	 * @return int|NULL
	 */
	public function getRowCount()
	{
		return $this->event->result->count();
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Dibi';
	}

	/**
	 * @return string hex 00FF00
	 */
	public function getColor()
	{
		return 'FDF5CE';
	}

}
