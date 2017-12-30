* fix behaviour for when the value for a criterion has a space in it (is it the same for fulltext vs eg. remote_id ?)

* check behaviour of all existing criteria
  - field*: todo: sort out current situation with 2 separate converters
  - logicalNot: todo (is it possible at all ?)

* check behaviour of all existing sort converters

* check list of all missing criteria converters compared to std search service (for Content searches)
  - contenttypegroup: can not do
  - datemetadata: todo
  - languagecode: todo
  - maplocationdistance: todo
  - usermetadata: todo
  - locationId: todo
  - locationPriority: todo
  - locationRemoteId: can not do
  - permissionSubtree: ?
  - visibility: ?

* add new criteria based on data available in ezfind (solr):
  - raw solr filter
  - other ?

* check list of all missing sort converters compared to std search service (for Content searches)

* add new sort converters based on data available in ezfind (solr) ?

* add unit tests (with travis and kaliop mig bundle for loading sample content) 

* introduce support for facets

* add legacy search extension that extends ezfind and adds extra features:
    + more parameters for a fetch function similar to ezfind/search
        - eg: avoid highlighting when not needed, see ezfeZPSolrQueryBuilder
    + extend the backoffice GUI to allow to schedule content reindexation; see solr view of a content
