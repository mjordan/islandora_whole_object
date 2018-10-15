# Islandora Whole Object

## Introduction

An first attempt at addressing https://github.com/Islandora-CLAW/CLAW/issues/886. Mainly me learning Islandora CLAW's data structures and how to render them in a Drupal 8 module.

## Requirements

* [Islandora](https://github.com/Islandora-CLAW/islandora) a.k.a. CLAW

## Installation

1. Clone this repo into your Islandora's `drupal/web/modules/contrib` directory.
1. Enable the module either under the "Admin > Extend" menu or by running `drush en -y islandora_whole_object`.

## Usage

After you enable this module, a "Whole Islandora Object" tab will appear on Islandora objects, for users with 'administer site configuration' permission:

![Whole object menu tab](docs/menu.png)

Clicking the link in that tab will render the JSON-LD of the current object, for example:

![JSON-LD](docs/jsonld.png)

That's currently all it does. More to come! There is one Easter egg: while you are viewing the JSON-LD, if you append `/json` to the end of the URL, you will get the basic node structure instead of the JSON-LD structure.

## To do

* Add the option of showing the content in a block
* Convert the Easter egg into something useful?
* Make the content types that we can view Whole Objects for configurable (currently it only shows on nodes of type 'islandora_object')
* Format the content so it's more useful
* Add more than just the JSON-LD (e.g., Solr document, thumnbails of media, etc.)

## Maintainers

Current maintainer:

* [Mark Jordan](https://github.com/mjordan)

## License

[GPLv2](http://www.gnu.org/licenses/gpl-2.0.txt)
