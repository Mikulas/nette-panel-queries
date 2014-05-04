<?php

namespace Mikulas\Diagnostics;

use Mikulas\Diagnostics\Queries\Query;
use Nette\Latte\Engine;
use Nette\Diagnostics\Debugger;
use Nette\Diagnostics\IBarPanel;
use Nette\Object;
use Nette\Templating\FileTemplate;


class QueryPanel extends Object implements IBarPanel
{

	/** @var Query[] */
	private $queries = [];

	/** @var array times */
	private $timeline = [];

	/**
	 * @param Query $query
	 */
	public function addQuery(Query $query)
	{
		// TODO save current time for timeline rendering
		$this->queries[] = $query;
		$this->timeline[] = microtime(TRUE);
	}

	/**
	 * @return string html
	 */
	public function getTab()
	{
		return '<span id="mikulas-diagnostics-queryPanel">'
		. '<img width="16" height="16" title="" alt="" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDEwMCAxMDAiIGhlaWdodD0iMTAwcHgiIHZlcnNpb249IjEuMSIgdmlld0JveD0iMCAwIDEwMCAxMDAiIHdpZHRoPSIxMDBweCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+PGcgaWQ9IkxheWVyXzEiPjxnPjxwb2x5Z29uIGZpbGw9IiM3NTlDM0UiIHBvaW50cz0iNTAsMjguNzc4IDc5LjgyMywzNy41NjggNzkuODIzLDI4LjkxMiA1MCwxNCAgICIvPjxwb2x5Z29uIGZpbGw9IiM3NTlDM0UiIHBvaW50cz0iNzMuODM5LDQ3LjY0IDUwLDQ2LjQ3MiA1MCw2MC44NzIgNzMuODM5LDU3LjI3MiAgICIvPjxwb2x5Z29uIGZpbGw9IiM0QjYxMkMiIHBvaW50cz0iMjYuMTYyLDQ3LjY0IDUwLDQ2LjQ3MiA1MCw2MC44NzIgMjYuMTYyLDU3LjI3MiAgICIvPjxwb2x5Z29uIGZpbGw9IiM0QjYxMkMiIHBvaW50cz0iNTAsMjguNzc4IDIwLjE3NywzNy41NjggMjAuMTc3LDI4LjkxMiA1MCwxNCAgICIvPjxwb2x5Z29uIGZpbGw9IiMzQzQ5MjkiIHBvaW50cz0iMjAuMTc3LDM3LjU2OCA1MCwyOC43NzggNzkuODIzLDM3LjU2OCA1MCw0MS4yMDkgICAiLz48cG9seWdvbiBmaWxsPSIjNzU5QzNFIiBwb2ludHM9IjY1Ljg3NSw2OS40NCA1MCw3NC45MzggNTAsODYgNjUuODc1LDc4LjA2MiAgICIvPjxwb2x5Z29uIGZpbGw9IiM0QjYxMkMiIHBvaW50cz0iMzQuMTI1LDY5LjQ0IDUwLDc0LjkzOCA1MCw4NiAzNC4xMjUsNzguMDYyICAgIi8+PHBvbHlnb24gZmlsbD0iI0I3Q0E5RCIgcG9pbnRzPSI2NS44NzUsNjkuNDQgNTAsNjUuOTI4IDM0LjEyNSw2OS40NCA1MCw3NC45MzggICAiLz48L2c+PC9nPjwvc3ZnPg==" />'
		. $this->getTitle()
		. '</span>';
	}

	/**
	 * @return string
	 */
	private function getTitle()
	{
		$total = 0;
		foreach ($this->queries as $query)
		{
			$total += $query->getDuration();
		}
		$total = round($total, 2);

		$c = count($this->queries);
		return ($c === 0
			? 'no queries'
			: ($c === 1
				? '1 query'
				: "$c queries"
			))
		. " / {$total}&thinsp;ms";
	}

	/**
	 * @return string html
	 */
	public function getPanel()
	{
		$template = new FileTemplate(__DIR__ . '/template.latte');
		$template->onPrepareFilters[] = function($template) {
			$template->registerFilter(new Engine);
		};
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');

		$template->title = $this->getTitle();
		$template->colors = $this->getColors();
		$template->timeline = $this->getTimelineBlocks();
		$template->queries = $this->queries;
		$template->timesPerStorage = $this->getTimesPerType();

		ob_start();
		$template->render();
		return ob_get_clean();
	}

	private function getTimesPerType()
	{
		$times = [];
		foreach ($this->queries as $query)
		{
			$n = $query->getName();
			if (!isset($times[$n]))
			{
				$times[$n] = 0;
			}
			$duration = $query->getDuration();
			$times[$n] += $duration;
		}

		arsort($times);
		return $times;
	}

	private function getColors()
	{
		$colors = [];
		foreach ($this->queries as $query)
		{
			$colors[$query->getName()] = $query->color;
		}

		return $colors;
	}

	private function getTimeline()
	{
		$timeline = [];

		$zero = $_SERVER["REQUEST_TIME_FLOAT"];

		foreach ($this->timeline as $i => $endAbs)
		{
			$query = $this->queries[$i];

			$end = $endAbs - $zero;
			$start = $end - $query->getDuration() / 1000;
			$timeline[] = [
				'start' => $start,
				'end' => $end,
				'query' => $query,
			];
		}

		return $timeline;
	}

	private function getTimelineBlocks()
	{
		$timeline = $this->getTimeline();
		$blocks = [];
		$max = microtime(TRUE) - $_SERVER["REQUEST_TIME_FLOAT"];

		$start = 0;
		foreach ($timeline as $i => $query)
		{
			$blocks[] = (object) [
				'start' => $start,
				'end' => $query['start'],
				'percent' => ($query['start'] - $start) / $max * 100,
				'query' => NULL,
				'i' => NULL
			];
			$blocks[] = (object) [
				'start' => $query['start'],
				'end' => $query['end'],
				'percent' => ($query['end'] - $query['start']) / $max * 100,
				'query' => $query['query'],
				'i' => $i
			];
			$start = $query['end'];
		}
		$blocks[] = (object) [
			'start' => $start,
			'end' => $max,
			'percent' => ($max - $start) / $max * 100,
			'query' => NULL,
			'i' => NULL,
		];

		return $blocks;
	}

}
