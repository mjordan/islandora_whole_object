# Islandora Whole Object

## Introduction

Islandora 2 module that provides some Drupal blocks containing various representations of an Islandora object:

* a block that contains the Drupal RDF properties in a table, as illustrated below
* a block listing Fedora's Turtle Linked Data representation of the resource
* a block listing Blazegraph's N-Triples Linked Data representation of the resource
* a block showing an "org chart" containing the current object's parent(s) and children
* a block containing the Solr document for the node
* a block listing the files associated with the object (showing different info than the default "Media" tab)

This is a sample "Drupal RDF Properties" block:

![sample RDF properties block](docs/rdf_properties.png)

This is a sample "Solr Document" block:

![sample RDF properties block](docs/solr.png)

This is a sample "Parents and Children" block:

![sample RDF properties block](docs/hierarchy.png)


## Requirements

* [Islandora 2](https://github.com/Islandora-CLAW/islandora)

## Installation

1. Clone this repo into your Islandora's `drupal/web/modules/contrib` directory.
1. Enable the module either under the "Admin > Extend" menu or by running `drush en -y islandora_whole_object`.

## Usage

After you enable this module, some new blocks will show up in your "Block Layout" admin area with the catagory "Islandora":

![overview](docs/blocks_list.png)

## Configuration

The blocks provided by this module are standard Drupal blocks, so you can configure them as you like. However, since they contain more information than most blocks do, you should:

* place them in wide regions, such as "Content suffix" (if your theme provides that region)
* configure the blocks so they display only for Islandora content types
* configure the blocks so they display only for specific roles (they are not intended for display to anonymous users)

## Current maintainer

* [Mark Jordan](https://github.com/mjordan)

## License

[GPLv2](http://www.gnu.org/licenses/gpl-2.0.txt)
