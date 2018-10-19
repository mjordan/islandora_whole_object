# Islandora Whole Object

## Introduction

A first attempt at addressing https://github.com/Islandora-CLAW/CLAW/issues/886. Since Islandora "objects" are comprised of data from a variety of sources, repo administrators and other users may find it useful to see this data in one place for diagnostic or other purposes.

## Requirements

* [Islandora](https://github.com/Islandora-CLAW/islandora) a.k.a. CLAW

## Installation

1. Clone this repo into your Islandora's `drupal/web/modules/contrib` directory.
1. Enable the module either under the "Admin > Extend" menu or by running `drush en -y islandora_whole_object`.

## Usage

After you enable this module, a "Whole Islandora Object" tab will appear on Islandora objects, for users with 'administer site configuration' permission. Clicking the link in that tab will provide the RDF properties of the current object, all media linked to the object, Fedora's RDF representation of the object, and the Solr document for the object:

![overview](docs/overview.png)

## Other representations

To get a sense of the different ways we might represent the linked data properties of an Islandora object, you can use a simple way to see different represtations of that data. Appending the following to the end of `/node/1/whole_islandora_object` (e.g., `/node/1/whole_islandora_object/table`) will result in different outputs:

* `overview` (or nothing at the end of the URL): show the combination of 'table', 'media', 'fedora', and 'solr' as described below
* `table` (or nothing at the end of the URL): show the Linked Data properties in a table (currently only show first of multivalued properties) as illustrated above
* `media`: list the media associated with the object
* `jsonld`: show the JSON-LD converted to a raw PHP array as illustrated below 
* `node`: show the basic Drupal node structure as a raw PHP array
* `fedora`: show Fedora's Turtle Linked Data representation of the resource
* `solr`: show the Solr document for the node

Here's the raw (PHP array) JSON-LD output (e.g., `http://localhost:8000/node/1/whole_islandora_object/jsonld`):

![JSON-LD](docs/jsonld.png)

## To do

* Outputing the JSON-LD using https://github.com/scienceai/jsonld-vis would be interesting. See issue #1.
* Add the option of showing the content in a block.
* Make the content types that we can view Whole Objects for configurable (currently it only shows on nodes of type 'islandora_object').
* Make what shows in the overview for the object configurable.

## Current maintainer

* [Mark Jordan](https://github.com/mjordan)

## License

[GPLv2](http://www.gnu.org/licenses/gpl-2.0.txt)
