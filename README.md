# Kaliop eZFind Search Engine Bundle

This bundle introduces a wrapper around the legacy eZFind search engine, making it available to developers with the
same search API available by default in eZ Publish 5.


## Features

* swap existing database-based content searches with solr-based searches by simply changing the name of *one* service used
* supports most of the query criteria and sort clauses from the eZPublish kernel
* allows to get back from searches either eZ5 Content objects or raw solr data (in both eZFind-decoded and SOLR-native format)
* allows to sort by score
* allows to use custom SOLR syntax for both query criteria and sort clauses
* unlike the SolrSearchEngineBundle, does *not* overtake all existing content searches
* optimized for speed of execution and memory usage (as much as we can without nuking eZFind)


## Setup

### Installation

You can install the bundle using Composer:

    composer require kaliop/ezfindsearchenginebundle
 
### Configuration

The bundle comes fully configured by default. Here is an example of the complete list of parameters available:

```yaml
    ezfind_search_engine.search_settings.boost_functions:
        - 'recip(ms(NOW/HOUR,attr_publication_date_dt),4e-12,1000,2)'
    # default list of fields returned when *not* returning Contents
    ezfind_search_engine.search_settings.fields_to_return:
        - meta_name_t
        - meta_owner_name_t
        - meta_path_string_ms
        - meta_priority_si
        - meta_score_value:score # Score field needs to be renamed as it won't be passed from eZFind
    # in case you want to use an alternative 'legacy fetch function'. The default is ezfind/search
    ezfind_search_engine.search_settings.legacy_function_handler.module_name: 'ezfind'
    ezfind_search_engine.search_settings.legacy_function_handler.function_name: 'search'
```


## Usage

The simplest way to use this bundle is to simply swap the Search Service that you use for existing queries with the new
one:

```php
    ...
```

For more advanced features, you can swap the Query object with one of a more specific class. This allows you to set more
query parameters, f.e. to speed up the execution of the query by disabling unneeded features 

```php
    ...
```

### Facets

Currently the following FacetBuilders are implemented by the bundle:

```php
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder;
use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\FacetBuilder as KaliopFacetBuilder;
 
$now = new DateTime();
$yearAgo = new DateTime();
$yearAgo->modify('-1 year');
 
$query->facetBuilders = [
    // Kaliop Facet Builders
    new KaliopFacetBuilder\FieldRangeFacetBuilder([
        'name' => 'Numeric field range facet',
        'fieldPath' => 'product/price',
        'start' => 100,
        'end' => 500,
        'gap' => 50,
        'limit' => 8,
    ]),
    new KaliopFacetBuilder\DateRangeFacetBuilder([
        'name' => 'Date range facet',
        'fieldPath' => 'article/publication_date',
        'start' => $yearAgo,
        'end' => $now,
        'gap' => new DateInterval('P1M'),
        'limit' => 12,
    ]),
    // Base eZ Facet Builders
    new FacetBuilder\FieldFacetBuilder([
        'name' => 'Simple field facet',
        'fieldPaths' => 'article/title',
        'limit' => 20,
    ]),
    new FacetBuilder\FieldFacetBuilder([
        'name' => 'Object relation(s) facet',
        'fieldPaths' => 'article/author/id',
        'limit' => 20,
    ]),
    new FacetBuilder\ContentTypeFacetBuilder([
        'name' => 'Content type facet',
    ]),
    new FacetBuilder\CriterionFacetBuilder([
        'name' => 'Criterion facet',
        'filter' => new Criterion\Field('article/title', Criterion\Operator::CONTAINS, 'new'),
    ]),
];
```

## Extending the bundle

### Tagged Services / Criteria

The bundle utilises a series of 'handlers' to convert the search Query into legacy search configuration to be sent to
Solr.

You can add more custom handlers using tagged services, with the following tags:

#### `ezfind_search_engine.content.criterion_handler.filter`

These are supposed to convert criteria that will be added to the "filter" section of the eZFind call (or to the query
string when sorting by score)

#### `ezfind_search_engine.content.sort_clause_handler`

These are supposed to convert sort clauses 


## Troubleshooting

...


## FAQ

* Is 'Location Search' implemented in this bundle?
  A: given the SOLR schema used by eZFind, it is not possible to implement 'Location Search' reliably unless you use
     no multi-located contents whatsoever. Sorry about that

* What is the difference between this bundle and the 'SolrSearchEngineBundle' available since eZPublish 5?
   A1: this bundle uses the same SOLR schema as ezfind. It is thus 100% compatible with the Legacy kernel
   A2: this bundle does not overtake the standard Search service from the eZPublish repository. It is up to the developer
       to decide which queries to send to the database and which ones to send to SOLR.

* Why do I see the same `score` for all results?
  A: when you are not sorting results by score, the bundle optimizes performances by using a pure 'filter query' for the
     Solr request (this should be good because Solr caches filters). This means that all search hits get the same score. 

* Is there any difference between using `$query->filter` and `$query->query`?
  A: when you are not sorting results by score, none.
     When you are sorting results by score, you should use for optimal performaces `$query->query` for all criteria that
     influence the scoring (eg. a search term), and `$query->filter` for all other criteria (eg limitations on content
     type, section, etc...) 

* I tried using the new search service in a command line script but I always get back no results
  A: there is a bug in the way the eZPublish legacy kernel is booted in command line scripts. We provide as courtesy
     an alternative implementation that can be activated by editing the services


## Thanks

Special tahnks to SOkamoto (who got this all started), Crevillo, DClements, MIwaniak, SKlimaszewski
