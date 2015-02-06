<?php

namespace Mikulas\Tracy\QueryPanel;

use Everyman\Neo4j\Query;
use Everyman\Neo4j\Transport;
use Nette\Object;
use Nette\Utils\Html;
use Tracy\Dumper;
use Tracy\QueryPanel\IQuery;


class ElasticSearchQuery extends Object implements IQuery
{

	private $request;

	private $response;

	/**
	 * @param string $request json
	 * @param string $response json
	 */
	public function __construct($request, $response)
	{
		$this->request = json_decode($request, TRUE);
		$this->response = json_decode($response, TRUE);
	}

	/**
	 * @return int
	 */
	public function getResultCount()
	{
		return $this->response['hits']['total'];
	}

	/**
	 * @return Html|string
	 */
	public function getResult()
	{
		if (!isset($this->response['hits']['hits']))
		{
			return NULL;
		}

		$html = Dumper::toHtml($this->response['hits']['hits'], [
			Dumper::COLLAPSE => TRUE,
			Dumper::TRUNCATE => 50,
		]);
		return Html::el()->setHtml($html);
	}

	/**
	 * Arbitrary identifier such as mysql, postgres, elastic, neo4j
	 *
	 * @return string
	 */
	public function getStorageType()
	{
		return 'elastic';
	}

	/**
	 * Database, fulltext index or similar, NULL if not applicable
	 *
	 * @return NULL|string
	 */
	public function getDatabaseName()
	{
	}

	/**
	 * @return Html|string
	 */
	public function getQuery()
	{
		$html = Dumper::toHtml($this->request, [
			Dumper::COLLAPSE_COUNT => 1,
			Dumper::DEPTH => 7,
		]);
		return Html::el()->setHtml($html);
	}

	/**
	 * @return NULL|float ms
	 */
	public function getElapsedTime()
	{
		return isset($this->response['took']) ? $this->response['took'] : NULL;
	}

	/**
	 * e.g. SQL explain
	 *
	 * @return NULL|Html|string
	 */
	public function getInfo()
	{
		$info = $this->response;
		unset($info['hits']['hits']);
		$html = Dumper::toHtml($info, [
			Dumper::COLLAPSE => TRUE,
			Dumper::TRUNCATE => 50,
		]);
		return Html::el()->setHtml($html);
	}

}
