Queries Panel
=============

<h3><em>One panel to rule them all.</em></h3>

![screenshot 2014-05-04 12 20 42](https://cloud.githubusercontent.com/assets/192200/2872777/c1e838d4-d376-11e3-8e59-0e8279565311.png)

**todo**
- [ ] add event callback to queries

```php
/** @var QueryPanel $panel */
$panel = $container->getService('queryPanel');
Nette\Diagnostics\Debugger::getBar()->addPanel($panel);

/** @var \DibiConnection $dibi */
$dibi = $container->getService('dibiConnection');
$dibi->onEvent[] = function(\DibiEvent $event) use ($panel) {
	$panel->addQuery(new DibiQuery($event));
};

/** @var Neo4j $neo4j */
$neo4j = $container->getService('neo4j');
$neo4j->onEvent[] = function($command, $result) use ($panel, $neo4j) {
	if (! $command instanceof GetServerInfo)
	{
		$panel->addQuery(new Neo4jQuery($command, $result, $neo4j->getTransport()));
	}
};

/** @var ElasticSearch $elastic */
$elastic = $container->getService('elastic');
$request = NULL;
$elastic->onEvent[] = function($message, $content) use ($panel, &$request) {
	if ($message === 'Request Body')
	{
		$request = $content[0];
		return;
	}
	if ($message === 'Response')
	{
		$panel->addQuery(new ElasticSearchQuery($request, $content[0]));
		$request = NULL;
	}
};
```
