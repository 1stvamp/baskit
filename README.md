baskit - PHP Build Environment
==============================

About
-----
_baskit_ is a zc.buildout/virtualenv/rvm style sandboxed build environment for PHP, based on [PEAR](http://pear.php.net/ "PEAR") and [Phing](http://phing.info/ "Phing").

Using baskit you can create a build environment separate from your system, that uses your system installed PHP, but with it's own dependencies (e.g. specific versions of PEAR installable packages), and run automated build tasks against this environment with Phing.

Installation
------------
To install baskit locally clone the git repository into your project.

The requirements for _baskit_ are:

 * [PHP](http://www.php.net/ "PHP") v5.0+
 * [PEAR](http://pear.php.net/ "PHP Extension and Application Repository") (not PEAR2, but maybe in the future)
 * A Unix shell like bash or zsh (but it should be easy to support others, like CMD or PowerShell on Windows)

Usage
-----
To create a new environment:

    php bootstrap.php

Which will give you a `./bin` directory with scripts for PEAR and Phing:

    bin/pear version
    bin/phing -version
    
    # This will install PEAR's Net_URL2 into the local build environment
    bin/pear install Net_URL2

And a `./parts` directory containing PEAR itself:

    ls parts/pear/php/

To make building your project and installing dependencies easier and more maintainable, _baskit_ includes some Phing tasks and targets:

Phing Tasks
===========

 * **PearInstallTask** - A Phing task to install a depenedency into the local PEAR sandbox, also supports installing custom PEAR channels.
 * **PhpMigrateTask** - A version of the core Phing **DbDeployTask** that allows running migrations written in PHP rather than SQL, you can use this yourself or use the pre-written `migrations.xml` target (see *Phing Targets*).

Phing Targets
=============

 * **install_requirements.xml** - Installs a list of non-PEAR dependencies from web URLs, with version checking, caching and support for zips and gzipped tars.
 * **migrations.xml** - Target for deploying all undeployed SQL and PHP database migrations (currently missing "undo" target for reverting to previous migration)..
 * **wordpress.xml** - Target for installing a Wordpress blog from scratch, and any Wordpress plugins, with version checking and caching.
 * **apache.xml** - Convenience target for generating an Apache2 virtualhost conf file in `./var`.
