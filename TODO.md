* test handling of deeply nested AND/OR clauses

* check behaviour of all existing criteria and sort converters
  - field*: todo
  - subtree: todo

* check list of all missing criteria converters compared to std search service
  - contenttypegroup: can not do
  - datemetadata: todo
  - languagecode: todo
  - maplocationdistance: todo
  - usermetadata: todo

* introduce support for facets


* escape search inputs to stop possible issues with special characters breaking searches => test if it's already done in ezfind

* add legacy search extension that extends ezfind and adds extra features:
    + more parameters for a fetch function similar to ezfind/search
        - eg: avoid highlighting when not needed, see ezfeZPSolrQueryBuilder
    + extend the backoffice GUI to allow to schedule content reindexation; see solr view of a content
