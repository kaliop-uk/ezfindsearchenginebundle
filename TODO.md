* fix behaviour for when the value for a criterion has a space in it (is it the same for fulltext vs eg. remote_id ?)

* check behaviour of all implemented criteria converters compared to std search service
  - fulltext: might currently differ
  - objectstateid: what happens when many states are passed in?

* check what happens when an ezfindtext criterion is used in the 'filter' member of a query, or when it is used together
  with other filters 

* check list of all missing criteria converters compared to std search service (for Content searches)
  - contenttypegroup: can not do
  - datemetadata: fails with operator IN
  - languagecode: todo
  - maplocationdistance: todo
  - locationRemoteId: can not do
  - permissionSubtree: ?
  - visibility: ?

* check list of all missing sort converters compared to std search service (for Content searches)
  - mapLocationName
  - sectionName: can not do
  - sectionIdentifier: can not do

* add new criteria based on data available in ezfind (solr): what ?

* add new sort converters based on data available in ezfind (solr): what ?

* decide what to do with parameter '$fieldFilters' for findContent and findSingle

* see all TODOs in the SearchService

* add unit tests (with travis and kaliop mig bundle for loading sample content) 

* add legacy search extension that extends ezfind and adds extra features:
    + more parameters for a fetch function similar to ezfind/search
        - eg: avoid highlighting when not needed, see ezfeZPSolrQueryBuilder
    + extend the backoffice GUI to allow to schedule content reindexation; see solr view of a content
