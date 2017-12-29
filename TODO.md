* check how we can handle deeply nested AND/OR clauses

* check list of all missing criteria converters compared to std search service

* check behaviour of all existing criteria and sort converters

* introduce support for facets


* escape search inputs to stop possible issues with special characters breaking searches => test if it's already done in ezfind

* add legacy search extension that extends ezfind and adds extra features:
    + more parameters for a fetch function similar to ezfind/search
    + extend the backoffice GUI to allow to schedule content reindexation; see solr view of a content
