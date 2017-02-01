Socrata module - http://drupal.org/project/socrata
==================================================
Provides an integration point for Socrata within Drupal via their SODA2 interface, including a Views query solution and an input filter.

There are four modules in this project:

Socrata
-------
Manages the dataset endpoints and allows for import/export of these definitions. When enabled, you will find a menu item under Structure > Socrata that will allow you to manage those. They can then be used in conjunction with other modules.

Socrata Views
-------------
This is a query plugin for Views 3.x that allows you to build a View against a dataset endpoint defined above. Once enabled, when you go to add a new View it will present all the datasets configured by the socrata module. After selecting a dataset, it will allow you to map fields and format them just like you would in a normal View. Any Views 3.x-compatible display plugin that operates on fields should be able to be used to render the data. Tested recently:

* Charts
* GMap

Socrata Catalog Search
----------------------
This module enables site builders to build Views against the [Socrata Catalog Search API](http://labs.socrata.com/docs/search.html) to query for metadata about dataset endpoints.
Note that because of a current limitation in the API, results can only be sorted within the paged output instead of the entire result set.

Socrata Filter
--------------
This provides an input filter that can be enabled for text formats. Embedding a Socrata widget in content is as simple as:

`[socrata source=my_dataset width=600 height=400]`

The module also provides a default template file that can be customized as needed for your theme.


REQUIREMENTS
------------
* Views (https://drupal.org/project/views) for Socrata Views.

INSTALLATION
------------
Enable the socrata_views modules for Views integration and the socrata_filter module for an input
filter.  Both modules will enable the base Socrata module as a dependency.


CONFIGURATION
-------------
Go to /admin/structure/socrata/add to create a new endpoint.  Endpoints can then be exported or saved to Features.


RECOMMENDED MODULES
-------------------
* Features (https://www.drupal.org/project/features) allows the saving of endpoints to features modules.

* Charting (https://www.drupal.org/node/2363985) and mapping (https://www.drupal.org/node/1704948)
  can be done with several modules with output from a View.


API
---
See socrata.api.php.


MAINTAINERS
-----------
Current maintainers:
* Andy Hieb (arh1) - https://www.drupal.org/u/arh1
* Will Hartmann (PapaGrande) - https://www.drupal.org/u/papagrande

Past maintainer:
* Robert Bates (arpieb) - https://www.drupal.org/u/arpieb

This project is sponsored by:
* Socrata - http://www.socrata.com
