* check for support of SCORE field

* check how we can handle deeply nested AND/OR clauses

* check list of all missing criteria converters compared to std search service

* check behaviour of all existing criteria and sort converters


* Make 4 services to handle the different criteria rather than loading them into the 4 different fields
* Rename the methods convertCriteria and canConvertCriteria to canHandle and handle
* Inject the handlers into the search service

* escape search inputs to stop possible issues with special characters breaking searches => test if it's already done in ezfind

* add legacy search extension that extends ezfind and adds extra features:
    + more parameters for fetch function similar to ezfind/search
    + extend the backoffice GUI to allow to schedule content reindexation; see solr view of a content

