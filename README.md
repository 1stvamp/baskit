baskit - PHP Build Environment
==============================

About
-----
_baskit_ is a zc.buildout/virtualenv/rvm style sandboxed build environment for PHP, based on [PEAR](http://pear.php.net/ "PEAR") and [Phing](http://phing.info/ "Phing").

Using baskit you can create a build environment separate from your system, that uses your system installed PHP, but with it's own dependencies (e.g. specific versions of PEAR installable packages), and run automated build tasks against this environment with Phing.

Installation
------------
To install baskit locally clone the git repository into your project.

**TODO: create PEAR channel and add steps to install here**

The requirements for _baskit_ are:

 * [PHP](http://www.php.net/ "PHP") v5.0+
 * [PEAR](http://pear.php.net/ "PHP Extension and Application Repository") (not PEAR2, but maybe in the future)

If you wish to create a local version of PEAR separate from your system, you can use the following steps (we can't do this for you unfortunately due to limitations in `go-pear`):

    curl http://pear.php.net/go-pear.phar | php

And just define your local project dirs as the base for the install.

Usage
-----
To create a new environment:

    php bootstrap.php

Which will give you a `./bin` directory with scripts for PEAR and Phing:

    bin/pear version
    bin/phing -version
    bin/pear install Net_URL2

And a `./parts` directory containing PEAR itself:

    ls parts/pear/php/

