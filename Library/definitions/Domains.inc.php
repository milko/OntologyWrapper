<?php

/*=======================================================================================
 *																						*
 *									Domains.inc.php										*
 *																						*
 *======================================================================================*/
 
/**
 * Domain definitions.
 *
 * This file contains the default domain definitions, these constants correspond to the
 * native identifiers of the terms defining domains.
 *
 *	@author		Milko A. Škofič <m.skofic@cgiar.org>
 *	@version	1.00 18/03/2013
 */

/*=======================================================================================
 *	DEFAULT DOMAINS																		*
 *======================================================================================*/

/**
 * Attribute.
 *
 * An attribute.
 *
 * This represents the domain of data property attributes.
 */
define( "kDOMAIN_ATTRIBUTE",					':domain:attribute' );

/**
 * Property.
 *
 * A property.
 *
 * This represents the domain of data properties.
 */
define( "kDOMAIN_PROPERTY",						':domain:property' );

/**
 * Unit.
 *
 * A generic unit.
 *
 * This represents the domain of generic units.
 */
define( "kDOMAIN_UNIT",							':domain:unit' );

/**
 * Event.
 *
 * An event.
 *
 * This represents the domain of generic events.
 */
define( "kDOMAIN_EVENT",						':domain:event' );

/**
 * Entity.
 *
 * An entity.
 *
 * This represents the domain of persons, institutions and organisations.
 */
define( "kDOMAIN_ENTITY",						':domain:entity' );

/**
 * Occurrence.
 *
 * An occurrence or observation.
 *
 * This represents the domain of occurrences.
 */
define( "kDOMAIN_OCCURRENCE",					':domain:occurrence' );

/**
 * Population.
 *
 * A population occurrence.
 *
 * This represents the domain of populations.
 */
define( "kDOMAIN_POPULATION",					':domain:population' );

/**
 * Specimen.
 *
 * A sample or specimen.
 *
 * This represents the domain of samples or specimens.
 */
define( "kDOMAIN_SPECIMEN",						':domain:specimen' );

/**
 * Taxon.
 *
 * A taxonomic nomenclature and concepts.
 *
 * This represents the domain of taxa.
 */
define( "kDOMAIN_TAXON",						':domain:taxon' );

/**
 * Inventory.
 *
 * An inventory.
 *
 * This represents the domain of taxa inventories.
 */
define( "kDOMAIN_INVENTORY",					':domain:inventory' );

/**
 * CWR inventory.
 *
 * A crop wild relatives inventory.
 *
 * This represents the domain of crop wild relatives inventories.
 */
define( "kDOMAIN_INVENTORY_CWR",				':domain:inventory:cwr' );

/**
 * Checklist.
 *
 * A checklist.
 *
 * This represents the domain of taxa checklists.
 */
define( "kDOMAIN_CHECKLIST",					':domain:inventory:checklist' );

/**
 * Crop wild relative checklist.
 *
 * A crop wild relative checklist.
 *
 * This represents a crop wild relative checklist.
 */
define( "kDOMAIN_CHECKLIST_CWR",				':domain:inventory:checklist:cwr' );

/**
 * Person.
 *
 * A person.
 *
 * This represents the domain of entities describing an individual.
 */
define( "kDOMAIN_INDIVIDUAL",					':domain:individual' );

/**
 * Organisation.
 *
 * An organisation.
 *
 * This represents the domain of entities describing an organisation.
 */
define( "kDOMAIN_ORGANISATION",					':domain:organisation' );

/**
 * Accession.
 *
 * An ex-situ sample.
 *
 * This represents the domain of units describing an ex-situ sample.
 */
define( "kDOMAIN_ACCESSION",					':domain:accession' );

/**
 * Sample.
 *
 * A collected or breeding sample.
 *
 * This represents the domain of units describing a germplasm sample.
 */
define( "kDOMAIN_SAMPLE",						':domain:sample' );

/**
 * Collected sample.
 *
 * A collected sample.
 *
 * This represents the domain of units describing a collected germplasm sample.
 */
define( "kDOMAIN_SAMPLE_COLLECTED",				':domain:sample:collected' );

/**
 * Bred sample.
 *
 * A breeding sample.
 *
 * This represents the domain of units describing a breeding germplasm sample.
 */
define( "kDOMAIN_SAMPLE_BREEDING",				':domain:sample:breeding' );

/**
 * Trial.
 *
 * An experiment or trial.
 *
 * This represents a controlled experiment on a set of samples.
 */
define( "kDOMAIN_TRIAL",						':domain:trial' );

/**
 * Collecting.
 *
 * A collecting mission or event.
 *
 * This represents the collecting of samples in the field.
 */
define( "kDOMAIN_COLLECTING",					':domain:collecting' );

/**
 * Breeding.
 *
 * A breeding trial or event.
 *
 * This represents the breeding of samples.
 */
define( "kDOMAIN_BREEDING",						':domain:breeding' );

/**
 * Forest unit.
 *
 * A forest gene conservation unit.
 *
 * This represents forest gene conservation unit including the target species populations.
 */
define( "kDOMAIN_FOREST",						':domain:forest' );

/**
 * Landrace unit.
 *
 * A landrace unit.
 *
 * This represents a landrace unit.
 */
define( "kDOMAIN_LANDRACE",						':domain:landrace' );

/**
 * Household assessment unit.
 *
 * A household assessment unit.
 *
 * This represents a household agro bio-diversity assessment unit.
 */
define( "kDOMAIN_HH_ASSESSMENT",				':domain:hh-assessment' );


?>
