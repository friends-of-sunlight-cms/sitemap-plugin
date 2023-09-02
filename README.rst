Sitemap plugin
##############

The Sitemap plugin makes it easier for you to create a sitemap for your website.

.. contents::

Requirements
************

- SunLight CMS 8

Installation
************

::

    Copy the folder 'plugins' and its contents to the root directory

or

::

    Installation via administration: 'Administration > Plugins > Upload new plugins'
	
	
Features
********
Files can be generated manually in the content management, but by default sitemap files are generated automatically. 

The following actions trigger the regeneration:
 - creating an article
 - edit article settings (slug, public, visible, confirm, home category)
 - deleting an article
 - creating a page
 - editing page settings (slug, public, visible, level, parent page)
 - deleting a page

Events
------
``sitemap.items`` - event allows to modify page data before generating files and also to add custom addresses (ex: for API)

``sitemap.defaults`` - event allows to modify values for individual page types (``changeFreq`` and ``priority``)
