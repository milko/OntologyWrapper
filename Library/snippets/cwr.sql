--
-- Set CWR-CK identifier
--
UPDATE `DATA-CK`
SET `__KEY`
	= CONCAT_WS( '/',
		CONCAT_WS( '-',
			`cwr:ck:CWRCODE`,
			`cwr:ck:NUMB` ),
		`:inventory:INSTCODE`,
		`:taxon:epithet`,
		`cwr:ck:TYPE` );

--
-- Set CWR-IN taxon epithet
--
UPDATE `DATA-IN`
SET `:taxon:epithet`
	= CONCAT_WS( ' ',
		`:taxon:genus`,
		`:taxon:species`,
		`:taxon:infraspecies` );

--
-- Set CWR-IN identifier
--
UPDATE `DATA-IN`
SET `__KEY`
	= CONCAT_WS( '/',
		CONCAT_WS( '-',
			`:inventory:NICODE`,
			`cwr:in:NIENUMB` ),
		`:inventory:INSTCODE`,
		`:taxon:epithet` );

//
// Set duplicates.
//
TRUNCATE TABLE `DUPS`;
INSERT INTO `DUPS`
SELECT
	`__KEY`, COUNT(*), `__ID`
FROM
	`DATA-IN`
GROUP BY
	`__KEY`
HAVING COUNT(*) > 1

--
-- Table structure for table `EURISCO_CWR`
--

DROP TABLE IF EXISTS `MCPD`;
CREATE TABLE IF NOT EXISTS `MCPD` (
  `:inventory:dataset` tinytext DEFAULT NULL COMMENT 'Source dataset or catalogue',
  `:inventory:NICODE` tinytext DEFAULT NULL COMMENT 'National Inventory code',
  `:inventory:INSTCODE` tinytext DEFAULT NULL COMMENT 'Institute code',
  `:unit:collection` tinytext DEFAULT NULL COMMENT 'Germplasm collection',
  `mcpd:ACCENUMB` tinytext DEFAULT NULL COMMENT 'Accession number',
  `mcpd:ACQDATE` tinytext DEFAULT NULL COMMENT 'Acquisition date',
  `mcpd:STORAGE` tinytext DEFAULT NULL COMMENT 'Type of germplasm storage',
  `:taxon:crop` text DEFAULT NULL COMMENT 'Common crop names',
  `:taxon:genus` tinytext DEFAULT NULL COMMENT 'Accession genus',
  `:taxon:species` tinytext DEFAULT NULL COMMENT 'Accession species',
  `:taxon:species:author` tinytext DEFAULT NULL COMMENT 'Accession species authority',
  `:taxon:infraspecies` tinytext DEFAULT NULL COMMENT 'Accession infraspecies epithet',
  `:taxon:infraspecies:author` tinytext DEFAULT NULL COMMENT 'Accession infraspecies authority',
  `:taxon:epithet` tinytext DEFAULT NULL COMMENT 'Accession scientific name',
  `:taxon:names` text DEFAULT NULL COMMENT 'Common crop names',
  `:taxon:annex-1` tinytext DEFAULT NULL COMMENT 'Annex-1 group',
  `mcpd:MLSSTAT` tinytext DEFAULT NULL COMMENT 'Multi Lateral System status',
  `mcpd:AEGISSTAT` tinytext DEFAULT NULL COMMENT 'AEGIS status',
  `mcpd:AVAILABLE` tinytext DEFAULT NULL COMMENT 'Availability status',
  `mcpd:SAMPSTAT` tinytext DEFAULT NULL COMMENT 'Biological sample status',
  `mcpd:COLLSRC` tinytext DEFAULT NULL COMMENT 'Acquisition or collecting source code',
  `mcpd:COLLCODE` tinytext DEFAULT NULL COMMENT 'Collecting institute code',
  `mcpd:COLLDESCR` text DEFAULT NULL COMMENT 'Collector names',
  `mcpd:COLLNUMB` tinytext DEFAULT NULL COMMENT 'Collecting number',
  `mcpd:COLLDATE` tinytext DEFAULT NULL COMMENT 'Collecting date',
  `:location:country` tinytext DEFAULT NULL COMMENT 'Country of collecting site',
  `:location:locality` text DEFAULT NULL COMMENT 'Location of collecting site',
  `mcpd:LATITUDE` tinytext DEFAULT NULL COMMENT 'Verbatim latitude',
  `:location:latitude:deg` int DEFAULT NULL COMMENT 'Degrees of latitude of collecting site',
  `:location:latitude:min` float DEFAULT NULL COMMENT 'Minutes of latitude of collecting site',
  `:location:latitude:sec` float DEFAULT NULL COMMENT 'Seconds of latitude of collecting site',
  `:location:latitude:hem` int DEFAULT NULL COMMENT 'Latitude hemisphere of collecting site',
  `:location:latitude` float DEFAULT NULL COMMENT 'Decimal latitude of collecting site',
  `mcpd:LONGITUDE` tinytext DEFAULT NULL COMMENT 'Verbatim longitude',
  `:location:longitude:deg` int DEFAULT NULL COMMENT 'Degrees of longitude of collecting site',
  `:location:longitude:min` float DEFAULT NULL COMMENT 'Minutes of longitude of collecting site',
  `:location:longitude:sec` float DEFAULT NULL COMMENT 'Seconds of longitude of collecting site',
  `:location:longitude:hem` int DEFAULT NULL COMMENT 'Longitude hemisphere of collecting site',
  `:location:longitude` float DEFAULT NULL COMMENT 'Decimal longitude of collecting site',
  `:location:elevation` int DEFAULT NULL COMMENT 'Elevation of collecting site',
  `mcpd:DONORCODE` tinytext DEFAULT NULL COMMENT 'Donor institute code',
  `mcpd:DONORDESCR` text DEFAULT NULL COMMENT 'Donor name',
  `mcpd:DONORNUMB` tinytext DEFAULT NULL COMMENT 'Donor accession number',
  `mcpd:BREDCODE` tinytext DEFAULT NULL COMMENT 'Breeding institute code',
  `mcpd:BREDDESCR` text DEFAULT NULL COMMENT 'Breeder name',
  `mcpd:ANCEST` text DEFAULT NULL COMMENT 'Ancestral information',
  `mcpd:OTHERNUMB` text DEFAULT NULL COMMENT 'Other identification associated with the accession',
  `mcpd:DUPLSITE` tinytext DEFAULT NULL COMMENT 'Safety duplicates institute code',
  `mcpd:DUPLDESCR` text DEFAULT NULL COMMENT 'Safety duplicates holder name',
  `mcpd:ACCENAME` text DEFAULT NULL COMMENT 'Accession crop',
  `mcpd:ACCEURL` text DEFAULT NULL COMMENT 'Accession URL',
  `mcpd:REMARKS` text DEFAULT NULL COMMENT 'Remarks',
  `:unit:version` tinytext DEFAULT NULL COMMENT 'Creation date'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Multi Crop Passport Descriptors';

--
-- PGR SECURE TARGET SPECIES QUERY
--
SELECT
	LEFT( `INVENTORY`, LOCATE( '-', `INVENTORY` ) - 1 ) AS `:inventory:dataset`,
	IF( LEFT( `INVENTORY`, LOCATE( '-', `INVENTORY` ) - 1 ) = 'EURISCO',
		RIGHT( `INVENTORY`, 3 ),
		IF( LEFT( `INVENTORY`, LOCATE( '-', `INVENTORY` ) - 1 ) = 'GRIN',
			'USA',
			NULL ) ) AS `:inventory:NICODE`,
	`INSTCODE` AS `:inventory:INSTCODE`,
	`COLLECTION` AS `:unit:collection`,
	`ACCENUMB` AS `mcpd:ACCENUMB`,
	IF( `ACQDATE` IS NOT NULL,
		IF( SUBSTRING( `ACQDATE`, 5 ) = '0000' OR
			SUBSTRING( `ACQDATE`, 5 ) = '----',
			LEFT( `ACQDATE`, 4 ),
			IF( SUBSTRING( `ACQDATE`, 7 ) = '00' OR
				SUBSTRING( `ACQDATE`, 7 ) = '--',
				LEFT( `ACQDATE`, 6 ),
				`ACQDATE` ) ),
		NULL ) AS `mcpd:ACQDATE`,
	IF( LENGTH( `STORAGE` ) > 0,
		`STORAGE`,
		NULL ) AS `mcpd:STORAGE`,
	`CROP` AS `:taxon:crop`,
	`GENUS` AS `:taxon:genus`,
	`SPECIES` AS `:taxon:species`,
	`SPAUTHOR` AS `:taxon:species:author`,
	`SUBTAXA` AS `:taxon:infraspecies`,
	`SUBTAUTHOR` AS `:taxon:infraspecies:author`,
	CONCAT_WS( ' ',
		`Genus`,
		`Species`,
		`SUBTAXA` ) AS `:taxon:epithet`,
	`CROPNAME` AS `:taxon:names`,
	`ANNEX1` AS `:taxon:annex-1`,
	IF( `MLSSTAT` = '-',
		NULL,
		`MLSSTAT` ) AS `mcpd:MLSSTAT`,
	IF( `AEGISSTAT` = '-',
		NULL,
		`AEGISSTAT` )AS `mcpd:AEGISSTAT`,
	IF( `AVAILABLE` = '-',
		NULL,
		`AVAILABLE` ) AS `mcpd:AVAILABLE`,
	`SAMPSTAT` AS `mcpd:SAMPSTAT`,
	`COLLSRC` AS `mcpd:COLLSRC`,
	`COLLCODE` AS `mcpd:COLLCODE`,
	`COLLDESCR` AS `mcpd:COLLDESCR`,
	`COLLNUMB` AS `mcpd:COLLNUMB`,
	IF( `COLLDATE` IS NOT NULL,
		IF( SUBSTRING( `COLLDATE`, 5 ) = '0000' OR
			SUBSTRING( `COLLDATE`, 5 ) = '----',
			LEFT( `COLLDATE`, 4 ),
			IF( SUBSTRING( `COLLDATE`, 7 ) = '00' OR
				SUBSTRING( `COLLDATE`, 7 ) = '--',
				LEFT( `COLLDATE`, 6 ),
				`COLLDATE` ) ),
		NULL ) AS `mcpd:COLLDATE`,
	`ORIGCTY` AS `:location:country`,
	`COLLSITE` AS `:location:locality`,
	`LATITUDE` AS `mcpd:LATITUDE`,
	IF( `LATITUDE` IS NOT NULL AND
		`LATITUDED` IS NOT NULL,
		IF( `LATITUDE` LIKE '%°%',
			CONVERT(
				LEFT( `LATITUDE`,
					  LOCATE( '°', `LATITUDE` ) - 1 ),
				UNSIGNED ),
			IF( `LATITUDE` LIKE '%N%' OR
				`LATITUDE` LIKE '%S%',
				CONVERT(
					LEFT( `LATITUDE`, 2 ),
					UNSIGNED ),
				NULL ) ),
		NULL ) AS `:location:latitude:deg`,
	IF( `LATITUDE` IS NOT NULL AND
		`LATITUDED` IS NOT NULL,
		IF( `LATITUDE` LIKE '%°%',
			IF( `LATITUDE` LIKE "%'%",
				CONVERT(
					SUBSTRING( `LATITUDE`,
							   LOCATE( '°', `LATITUDE` ) + 1,
							   LOCATE( "'", `LATITUDE` ) -
							   LOCATE( '°', `LATITUDE` ) - 1 ),
					DECIMAL( 8, 6 ) ),
				NULL ),
			IF( `LATITUDE` LIKE '%N%' OR
				`LATITUDE` LIKE '%S%',
				IF( SUBSTRING( `LATITUDE`, 3, 2 ) != '--',
					CONVERT(
						SUBSTRING( `LATITUDE`, 3, 2 ),
						DECIMAL( 8, 6 ) ),
					NULL ),
				NULL ) ),
		NULL ) AS `:location:latitude:min`,
	IF( `LATITUDE` IS NOT NULL AND
		`LATITUDED` IS NOT NULL,
		IF( `LATITUDE` LIKE '%°%',
			IF( `LATITUDE` LIKE '%"%',
				CONVERT(
					SUBSTRING( `LATITUDE`,
							   LOCATE( "'", `LATITUDE` ) + 1,
							   LOCATE( '"', `LATITUDE` ) -
							   LOCATE( "'", `LATITUDE` ) - 1 ),
					DECIMAL( 8, 6 ) ),
				NULL ),
			IF( `LATITUDE` LIKE '%N%' OR
				`LATITUDE` LIKE '%S%',
				IF( SUBSTRING( `LATITUDE`, 5, 2 ) != '--',
					CONVERT(
						SUBSTRING( `LATITUDE`, 5, 2 ),
						DECIMAL( 8, 6 ) ),
					NULL ),
				NULL ) ),
		NULL ) AS `:location:latitude:sec`,
	IF( `LATITUDE` IS NOT NULL AND
		`LATITUDED` IS NOT NULL,
		IF( `LATITUDE` LIKE '%°%',
			RIGHT( `LATITUDE`, 1 ),
			NULL ),
		IF( `LATITUDE` LIKE '%N%' OR
			`LATITUDE` LIKE '%S%',
			RIGHT( `LATITUDE`, 1 ),
			NULL ) ) AS `:location:latitude:hem`,
	`LATITUDED` AS `:location:latitude`,
	`LONGITUDE` AS `mcpd:LONGITUDE`,
	IF( `LONGITUDE` IS NOT NULL AND
		`LONGITUDED` IS NOT NULL,
		IF( `LONGITUDE` LIKE '%°%',
			CONVERT(
				LEFT( `LONGITUDE`,
					  LOCATE( '°', `LONGITUDE` ) - 1 ),
				UNSIGNED ),
			IF( `LONGITUDE` LIKE '%N%' OR
				`LONGITUDE` LIKE '%S%',
				CONVERT(
					LEFT( `LONGITUDE`, 3 ),
					UNSIGNED ),
				NULL ) ),
		NULL ) AS `:location:longitude:deg`,
	IF( `LONGITUDE` IS NOT NULL AND
		`LONGITUDED` IS NOT NULL,
		IF( `LONGITUDE` LIKE '%°%',
			IF( `LONGITUDE` LIKE "%'%",
				CONVERT(
					SUBSTRING( `LONGITUDE`,
							   LOCATE( '°', `LONGITUDE` ) + 1,
							   LOCATE( "'", `LONGITUDE` ) -
							   LOCATE( '°', `LONGITUDE` ) - 1 ),
					DECIMAL( 8, 6 ) ),
				NULL ),
			IF( `LONGITUDE` LIKE '%N%' OR
				`LONGITUDE` LIKE '%S%',
				IF( SUBSTRING( `LONGITUDE`, 4, 2 ) != '--',
					CONVERT(
						SUBSTRING( `LONGITUDE`, 4, 2 ),
						DECIMAL( 8, 6 ) ),
					NULL ),
				NULL ) ),
		NULL ) AS `:location:longitude:min`,
	IF( `LONGITUDE` IS NOT NULL AND
		`LONGITUDED` IS NOT NULL,
		IF( `LONGITUDE` LIKE '%°%',
			IF( `LONGITUDE` LIKE '%"%',
				CONVERT(
					SUBSTRING( `LONGITUDE`,
							   LOCATE( "'", `LONGITUDE` ) + 1,
							   LOCATE( '"', `LONGITUDE` ) -
							   LOCATE( "'", `LONGITUDE` ) - 1 ),
					DECIMAL( 8, 6 ) ),
				NULL ),
			IF( `LONGITUDE` LIKE '%N%' OR
				`LONGITUDE` LIKE '%S%',
				IF( SUBSTRING( `LONGITUDE`, 6, 2 ) != '--',
					CONVERT(
						SUBSTRING( `LONGITUDE`, 6, 2 ),
						DECIMAL( 8, 6 ) ),
					NULL ),
				NULL ) ),
		NULL ) AS `:location:longitude:sec`,
	IF( `LONGITUDE` IS NOT NULL AND
		`LONGITUDED` IS NOT NULL,
		IF( `LONGITUDE` LIKE '%°%',
			RIGHT( `LONGITUDE`, 1 ),
			NULL ),
		IF( `LONGITUDE` LIKE '%E%' OR
			`LONGITUDE` LIKE '%W%',
			RIGHT( `LONGITUDE`, 1 ),
			NULL ) ) AS `:location:longitude:hem`,
	`LONGITUDED` AS `:location:longitude`,
	CONVERT( `ELEVATION`, SIGNED ) AS `:location:elevation`,
	`DONORCODE` AS `mcpd:DONORCODE`,
	`DONORDESCR` AS `mcpd:DONORDESCR`,
	`DONORNUMB` AS `mcpd:DONORNUMB`,
	`BREDCODE` AS `mcpd:BREDCODE`,
	`BREDDESCR` AS `mcpd:BREDDESCR`,
	`ANCEST` AS `mcpd:ANCEST`,
	`OTHERNUMB` AS `mcpd:OTHERNUMB`,
	`DUPLSITE` AS `mcpd:DUPLSITE`,
	`DUPLDESCR` AS `mcpd:DUPLDESCR`,
	`ACCENAME` AS `mcpd:ACCENAME`,
	`ACCEURL` AS `mcpd:ACCEURL`,
	`REMARKS` AS `mcpd:REMARKS`,
	`Stamp` AS `:unit:version`
FROM
	`ACCESSIONS`
WHERE
(
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'bicornis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'biuncialis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'columnaris' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'comosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'crassa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cylindrica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'elongata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'geniculata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'kotschyi' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'markgrafii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'neglecta' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'peregrina' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'speltoides' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'squarrosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tauschii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'triaristata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'triuncialis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'umbellulata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'uniaristata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'variabilis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'vavilovii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Aegilops' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ventricosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Agropyron' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cimmericum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Agropyron' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cristatum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Agropyron' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'dasyanthum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Agropyron' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'desertorum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Agropyron' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tanaiticum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'albiflorum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'altaicum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ampeloprasum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'atrosanguineum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'atroviolaceum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'bourgeaui' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'commutatum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'convallarioides' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'corsicum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'exaltatum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'fedschenkoanum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'galanthum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'karelinii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ledebourianum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lojaconoi' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'longicuspis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'melananthum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'monadelphum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'oliganthum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pardoi' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pervestitum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pskemense' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pyrenaicum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ramosum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'scabriscapum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'schmitzii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'schoenoprasum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'semenowii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Allium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'weschniakowii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Amblyopyrum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'muticum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Armoracia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'rusticana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'acutifolius' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'albus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'aphyllus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'arborescens' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'fallax' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'inderiensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'maritimus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'nesiotes' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'officinalis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pastorianus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'plocamoides' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'stipularis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Asparagus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tenuifolius' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Avena' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'atherantha' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Avena' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'fatua' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Avena' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'hybrida' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Avena' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'insularis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Avena' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'murphyi' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Avena' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'occidentalis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Avena' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pilosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Avena' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'prostrata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Avena' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sterilis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Avena' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'trichophylla' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Barbarea' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lepuznica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Barbarea' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'verna' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'adanensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'corolliflora' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lomatogona' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'macrocarpa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'macrorhiza' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'nana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'patellaris' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'patula' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'procumbens' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'trigyna' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'vulgaris' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'webbiana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Beta' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'x intermedia' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'barrelieri' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'bourgeaui' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cretica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'drepanensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'elongata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'fruticulosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'glabrescens' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'hilarionis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'incana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'insularis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'macrocarpa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'montana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'nigra' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'oleracea' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'rupestris' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tournefortii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Brassica' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'villosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Carthamus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'boissieri' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Carthamus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'creticus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Carthamus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'dentatus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Carthamus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'glaucus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Carthamus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lanatus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Carthamus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'leucocaulos' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Carthamus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'persicus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Carthamus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'turkestanicus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Chenopodium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ficifolium' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Chenopodium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'murale' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Chenopodium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'opulifolium' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Chenopodium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'polyspermum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Chenopodium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'strictum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Chenopodium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'vulvaria' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cicer' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'bijugum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cicer' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'canariense' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cicer' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'echinospermum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cicer' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'graecum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cicer' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pinnatifidum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cicer' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'reticulatum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Coincya' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'monensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Corylus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'avellana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Corylus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'colurna' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Corylus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'maxima' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'arborea' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'aspera' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'feuillei' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'filiformis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'fruticosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'gomerae' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'hispanica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'laevigata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'microcarpa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pritzelii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'scaberrima' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'scoparia' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sventenii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tamadabensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Crambe' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'wildpretii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cynara' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'algarbiensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cynara' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'auranitica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cynara' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'baetica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cynara' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cardunculus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cynara' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'humilis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Cynara' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tournefortii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Daucus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'carota' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Diplotaxis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'erucoides' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Diplotaxis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'muralis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Diplotaxis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'siettiana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Diplotaxis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'siifolia' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Diplotaxis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tenuifolia' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Diplotaxis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'vicentina' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Elymus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'elongatus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Elymus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'farctus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Elymus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'hispidus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Eruca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'vesicaria' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Erucastrum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'canariense' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Erucastrum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'gallicum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Ficus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'carica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Hordeum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'brevisubulatum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Hordeum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'bulbosum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Hordeum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'vulgare' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Ilex' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'canariensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Isatis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'buschiana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Isatis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lusitanica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Isatis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'platyloba' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Isatis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tinctoria' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Juglans' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'regia' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'aculeata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'alpestris' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'altaica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cyprica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'georgica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'saligna' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'scarioloides' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'serriola' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'singularis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tetrantha' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'triquetra' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'virosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lactuca' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'watsoniana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'amphicarpos' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'annuus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'belinensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'blepharicarpus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cassius' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'chloranthus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'chrysanthus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cicera' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cilicicus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cirrhosus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'clymenum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'gorgoni' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'grandiflorus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'heterophyllus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'hierosolymitanus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'hirsutus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'latifolius' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lycicus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'marmoratus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ochrus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'odoratus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'phaselitanus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pseudocicera' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'rotundifolius' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'stenophyllus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sylvestris' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tingitanus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'trachycarpus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tuberosus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lathyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'undulatus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lens' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'culinaris' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lens' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ervoides' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lens' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'nigricans' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lepidium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sativum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lepidium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'turczaninowii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lupinus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'albus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lupinus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'angustifolius' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lupinus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cosentinii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lupinus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'hispanicus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lupinus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'luteus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lupinus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'micranthus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Lupinus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pilosus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Malus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'crescimannoi' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Malus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'orientalis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Malus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pumila' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Malus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sieversii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Malus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sylvestris' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'arborea' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cancellata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'citrina' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'constricta' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cretacea' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'doliata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'dzhawakhetica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'fischeriana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'glandulosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'heyniana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'hypogaea' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'italica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'kotovii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'littoralis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pironae' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'prostrata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'rigidula' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'rugosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'rupestris' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sativa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'scutellata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'strasseri' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'truncatula' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Medicago' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'turbinata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Moricandia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'arvensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Olea' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'europaea' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Panicum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'miliaceum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Patellifolia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'webbiana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pennisetum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'orientale' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Phoenix' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'canariensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Phoenix' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'humilis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Phoenix' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'theophrasti' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pistacia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE '├ù saportae' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pistacia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'atlantica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pistacia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'eurycarpa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pistacia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'khinjuk' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pistacia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lentiscus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pistacia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'terebinthus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pisum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'fulvum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pisum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sativum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Potentilla' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'palustris' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE '├ù eminens' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'arabica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'argentea' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'avium' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'brigantina' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cerasifera' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cocomilia' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'dulcis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'fenzliana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'fruticosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'incana' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lusitanica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lycioides' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'mahaleb' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'padus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'prostrata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ramburii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'spinosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'spinosissima' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tenella' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'trichamygdalus' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ursina' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Prunus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'webbii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'communis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cordata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'elaeagrifolia' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'magyarica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'nivalis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'salicifolia' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'spinosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Pyrus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'syriaca' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Raphanus' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'raphanistrum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Ribes' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'aciculare' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Ribes' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'multiflorum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Ribes' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'nigrum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Ribes' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'petraeum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Ribes' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'rubrum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Ribes' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'spicatum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Ribes' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'turbinatum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Ribes' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'uva-crispa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Rorippa' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'prolifera' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Rorippa' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'valdes-bermejoi' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Saccharum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'spontaneum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Secale' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'cereale' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Secale' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'strictum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Secale' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sylvestre' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Secale' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'vavilovii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Setaria' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'italica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Sinapidendron' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'angustifolium' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Sinapidendron' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'frutescens' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Sinapidendron' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'gymnocalyx' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Sinapidendron' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'rupestre' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Sinapidendron' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sempervivifolium' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Sinapis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'alba' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Sinapis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'arvensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Sinapis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'flexuosa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Solanum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'incanum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Solanum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lidii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Sorghum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'halepense' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Spinacia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'tetranda' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Spinacia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'turkestanica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Trifolium' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'argutum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Triticum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'boeoticum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Triticum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'dicoccoides' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Triticum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'monococcum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Triticum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'timopheevii' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Triticum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'turgidum' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Triticum' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'urartu' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'articulata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'assyriaca' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'barbazitae' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'capreolata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ciliatula' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'costae' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ervilia' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'esdraelonensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'ferreirensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'galeata' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'galilaea' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'grandiflora' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'hybrida' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'hyrcanica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'johannis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'lutea' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'melanops' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'mollis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'narbonensis' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pannonica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'pyrenaica' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sativa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'sericocarpa' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vicia' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'serratifolia' COLLATE utf8_general_ci)
	) OR
	(
		(`GENUS` LIKE 'Vitis' COLLATE utf8_general_ci) AND
		(`SPECIES` LIKE 'vinifera' COLLATE utf8_general_ci) )
)
