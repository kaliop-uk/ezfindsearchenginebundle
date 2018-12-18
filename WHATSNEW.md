Version 1.4
===========

* New: a new Query Criterion, `EzFindText`, can now be used to fully emulated legacy 'ezfind searches', i.e. do a
    full-text search across all indexed content fields, instead of matching the `ezf_df_text` field as is done when using
    a `FullText` criterion. This can greatly improve relevancy sorting.
    
    Ex:
    
        use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\Criterion\EzFindText;
        use Kaliop\EzFindSearchEngineBundle\API\Repository\Values\Content\Query\SortClause\Score; 
        ...
        $query->query = new EzFindText('hello world');
        $query->sortClauses = [new Score('descending')];
        
    *NB* the `EzFindText` criterion should be used as unique member of your query's `query` member.
    When using it, use the query's `filter` member to add any other criteria.
        
Version 1.3
===========

* Improved: made it easier to change the default values for QueryHandler, EnableElevation and ForceElevation by
    subclassing the SearchService

* Improved: the FullText Criterion will now treat text surrounded by double quotes as meaning 'exact match',
    while still doing its magic mangling for text beginning or ending with a wildcard character

* Improved:  declare compatibility with Kaliop Migrations bundle v5


Version 1.2
===========

* Changed: the SearchService will now throw exceptions by default when there is an error reported by eZFind in the communication
    to SOLR.

    You can disable this behaviour by altering symfony parameter `ezfind_search_engine.search_settings.throw_errors`.

    For the moment the exception thrown is a `Kaliop\EzFindSearchEngineBundle\Core\Base\Exception\eZFindException`; in the
    future we might be able to identify better the different error cases using subclasses thereof.

* BC: the constructor of the SearchService has changed signature. In case you had extended it, please revise your subclasses.


Version 1.1
===========

* New: added support for Sf profiler to be able to analyze the amount of Solr requests sent and the time taken
