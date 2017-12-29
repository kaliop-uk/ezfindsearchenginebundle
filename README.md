# Kaliop eZFind Search Engine Bundle

This bundle introduces a wrapper around the legacy eZFind search engine, making it available to developers with the
same search API available by default in eZ Publish 5.

## Setup

### Installation

You can install the bundle using Composer:

    composer require kaliop/ezfindsearchenginebundle
 
### Configuration

The bundle comes fully configured by default. Here is an example of the complete list of parameters available:

```yaml
    # in case you want to use an alternative 'legacy fetch function'. The default is ezfind/search
    ezfind_search_engine.search_settings.legacy_function_handler.module_name: 'ezfind'
    ezfind_search_engine.search_settings.legacy_function_handler.function_name: 'search'
    ezfind_search_engine.search_settings.boost_functions:
        - 'recip(ms(NOW/HOUR,attr_publication_date_dt),4e-12,1000,2)'
    # default list of fields returned
    ezfind_search_engine.search_settings.fields_to_return:
        - meta_name_t
        - meta_owner_name_t
        - meta_path_string_ms
        - meta_priority_si
        - meta_score_value:score # Score field needs to be renamed as it won't be passed from eZFind
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


## Extending the bundle

### Tagged Services / Criteria

The bundle utilises a series of 'handlers' to convert the search Query and its criteria into legacy search
configuration to be sent to Solr.

You can add more custom handlers using tagged services, with the following tags:

#### ezfind_search_engine.content.criterion_handler.logical

Any logical handler that you need (AND / OR are already implemented)

#### ezfind_search_engine.content.criterion_handler.subtree

Subtree related handlers (e.g. ParentLocationId)

#### ezfind_search_engine.content.criterion_handler.content_type

Content type related handlers (e.g. ContentTypeIdentifier, ContentTypeId)

#### ezfind_search_engine.content.criterion_handler.filter

The rest of the criteria will be added to the "filter" section of the eZFind call


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

* I tried using the new search service in a command line script but I always get back no results
  A: there is a bug in the way the eZPublish legacy kernel is booted in command line scripts. We provide as courtesy
     an alternative implementation that can be activated by editing the services
  