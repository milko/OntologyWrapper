// ":inventory:dataset" (930962)
// [930962]
db.getCollection("_units").ensureIndex( { "@8c" : 1 }, { name : "ix_@8c", sparse : true } )

// ":germplasm:identifier" (908712)
// [908712]
db.getCollection("_units").ensureIndex( { "@14d" : 1 }, { name : "ix_@14d", sparse : true } )
// [123768]
db.getCollection("_units").ensureIndex( { "@14e.@14d" : 1 }, { name : "ix_@14e.@14d", sparse : true } )

// ":location:country" (854696)
// [182722]
db.getCollection("_units").ensureIndex( { "@95" : 1 }, { name : "ix_@95", sparse : true } )
// [594212]
db.getCollection("_units").ensureIndex( { "@414.@95" : 1 }, { name : "ix_@414.@95", sparse : true } )
// [90691]
db.getCollection("_units").ensureIndex( { "@413.@95" : 1 }, { name : "ix_@413.@95", sparse : true } )

// "mcpd:ACCENUMB" (828308)
// [748219]
db.getCollection("_units").ensureIndex( { "@232" : 1 }, { name : "ix_@232", sparse : true } )
// [94511]
db.getCollection("_units").ensureIndex( { "@14e.@232" : 1 }, { name : "ix_@14e.@232", sparse : true } )

// "mcpd:INSTCODE" (748219)
// [748219]
db.getCollection("_units").ensureIndex( { "@231" : 1 }, { name : "ix_@231", sparse : true } )

// "mcpd:SAMPSTAT" (729697)
// [144721]
db.getCollection("_units").ensureIndex( { "@23f" : 1 }, { name : "ix_@23f", sparse : true } )
// [584976]
db.getCollection("_units").ensureIndex( { "@416.@23f" : 1 }, { name : "ix_@416.@23f", sparse : true } )

// ":inventory:GENESYS" (701688)
// [701688]
db.getCollection("_units").ensureIndex( { "@93" : 1 }, { name : "ix_@93", sparse : true } )

// ":name" (586099)
// [21210]
db.getCollection("_units").ensureIndex( { "@1f" : 1 }, { name : "ix_@1f", sparse : true } )
// [160191]
db.getCollection("_units").ensureIndex( { "@16e.@1f" : 1 }, { name : "ix_@16e.@1f", sparse : true } )
// [340478]
db.getCollection("_units").ensureIndex( { "@154.@1f" : 1 }, { name : "ix_@154.@1f", sparse : true } )
// [126135]
db.getCollection("_units").ensureIndex( { "@414.@16e.@1f" : 1 }, { name : "ix_@414.@16e.@1f", sparse : true } )
// [194629]
db.getCollection("_units").ensureIndex( { "@416.@1f" : 1 }, { name : "ix_@416.@1f", sparse : true } )

// "mcpd:MLSSTAT" (531364)
// [531364]
db.getCollection("_units").ensureIndex( { "@417.@249" : 1 }, { name : "ix_@417.@249", sparse : true } )

// "mcpd:COLLNUMB" (376552)
// [145044]
db.getCollection("_units").ensureIndex( { "@236" : 1 }, { name : "ix_@236", sparse : true } )
// [231508]
db.getCollection("_units").ensureIndex( { "@414.@236" : 1 }, { name : "ix_@414.@236", sparse : true } )

// ":location:admin-1" (286911)
// [79385]
db.getCollection("_units").ensureIndex( { "@98" : 1 }, { name : "ix_@98", sparse : true } )
// [207526]
db.getCollection("_units").ensureIndex( { "@414.@98" : 1 }, { name : "ix_@414.@98", sparse : true } )

// "mcpd:COLLSRC" (279070)
// [48755]
db.getCollection("_units").ensureIndex( { "@23e" : 1 }, { name : "ix_@23e", sparse : true } )
// [230315]
db.getCollection("_units").ensureIndex( { "@416.@23e" : 1 }, { name : "ix_@416.@23e", sparse : true } )

// "mcpd:DUPLSITE" (229063)
// [229217]
db.getCollection("_units").ensureIndex( { "@415.@150.@246" : 1 }, { name : "ix_@415.@150.@246", sparse : true } )

// ":inventory:DUPLSITE" (229063)
// [229217]
db.getCollection("_units").ensureIndex( { "@415.@150.@92" : 1 }, { name : "ix_@415.@150.@92", sparse : true } )

// ":taxon:epithet" (227726)
// [223727]
db.getCollection("_units").ensureIndex( { "@10b" : 1 }, { name : "ix_@10b", sparse : true } )

// ":taxon:genus" (226681)
// [223196]
db.getCollection("_units").ensureIndex( { "@fd" : 1 }, { name : "ix_@fd", sparse : true } )

// ":taxon:species:name" (226674)
// [223189]
db.getCollection("_units").ensureIndex( { "@10d" : 1 }, { name : "ix_@10d", sparse : true } )

// ":taxon:designation:use" (206586)
// [206767]
db.getCollection("_units").ensureIndex( { "@126" : 1 }, { name : "ix_@126", sparse : true } )

// ":taxon:crop" (133633)
// [133702]
db.getCollection("_units").ensureIndex( { "@11e" : 1 }, { name : "ix_@11e", sparse : true } )

// ":taxon:crop:group" (132951)
// [133015]
db.getCollection("_units").ensureIndex( { "@11f" : 1 }, { name : "ix_@11f", sparse : true } )

// ":taxon:crop:category" (132951)
// [133015]
db.getCollection("_units").ensureIndex( { "@120" : 1 }, { name : "ix_@120", sparse : true } )

// ":taxon:annex-1" (130949)
// [131071]
db.getCollection("_units").ensureIndex( { "@148" : 1 }, { name : "ix_@148", sparse : true } )

// ":taxon:familia" (59920)
// [60120]
db.getCollection("_units").ensureIndex( { "@f7" : 1 }, { name : "ix_@f7", sparse : true } )

// ":entity:acronym" (31281)
// [13057]
db.getCollection("_units").ensureIndex( { "@4d" : 1 }, { name : "ix_@4d", sparse : true } )
// [17641]
db.getCollection("_units").ensureIndex( { "@16e.@4d" : 1 }, { name : "ix_@16e.@4d", sparse : true } )
