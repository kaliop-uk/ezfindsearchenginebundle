Version 1.3 (unreleased)
========================

* Improved: made it easier to change the default values for QueryHandler, EnableElevation and ForceElevation by
    subclassing the SearchService 


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
