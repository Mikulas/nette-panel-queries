<?php

namespace Mikulas\Diagnostics\Queries;

use Everyman\Neo4j\Command;
use Everyman\Neo4j\Query\ResultSet;
use Everyman\Neo4j\Query as NeoQuery;
use Everyman\Neo4j\Transport;
use ReflectionClass;


class Neo4jQuery extends Query
{

	/** @var Command */
	private $command;

	/** @var ResultSet */
	private $result;

	/** @var Transport */
	private $transport;

	public function __construct(Command $command, ResultSet $result, Transport $transport)
	{
		$this->command = $command;
		$this->result = $result;
		$this->transport = $transport;
	}

	/**
	 * @return string highlit html
	 */
	public function getQuery()
	{
		$class = new ReflectionClass($this->command);
		$prop = $class->getProperty('query');
		$prop->setAccessible(TRUE);
		/** @var NeoQuery $query */
		$query = $prop->getValue($this->command);

		return $this->formatQuery($query->getQuery());
	}

	/**
	 * @return float ms
	 */
	public function getDuration()
	{
		$class = new ReflectionClass($this->transport);
		$prop = $class->getProperty('handle');
		$prop->setAccessible(TRUE);
		$ch = $prop->getValue($this->transport);

		return curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000;
	}

	/**
	 * @return int|NULL
	 */
	public function getRowCount()
	{
		return $this->result->count();
	}

	private function formatQuery($query)
	{
		$query = preg_replace('~^\s+~m', '', $query);

		$query = preg_replace('~\b(AS|ASSERT|CONSTRAINT|CREATE|DELETE|DROP|FOREACH|IS|LIMIT|(OPTIONAL\s+)?MATCH|MERGE|MATCH|ON|ORDER BY|REMOVE|RETURN|SET|SKIP|UNIQUE|WHERE|WITH)\b~',
			'<strong style="color: blue">$0</strong>', $query);

		return "<pre>$query</pre>";
	}

	/**
	 * User friendly name for Query type
	 * Must be same for the same connection type
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Neo4j';
	}

	/**
	 * @return string hex 00FF00
	 */
	public function getColor()
	{
		return '5CC8EF';
	}

}
