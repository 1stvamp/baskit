_Baskit_ Example Project
------------------------

Getting started
===============
To bootstrap the build environment and build the project:

    # Assuming we're inside the baskit base directory
    cd example
    php ../bootstrap.php
    
    # Let's symlink the isotoma directory which contains common baskit tasks and targets
    # you could also cp this in, but I'm avoiding the clutter for the example project.
    ln -s ../isotoma ./isotoma

    # Build the project with Phing
    bin/phing

    ls -lah
    # You should now see bin, parts and var directories, with several scripts
    # and dependencies installed.

    $EDITOR build.xml build.properties # Take a look for your self

This example build doesn't perform any database migrations and it doesn't initialise Wordpress when it installs it to `./parts`
(which is done by calling the Wordpress installation scripts), this is because we obviously can't know what database setup
you have in place when trying out this example project.

Some example migrations are included in `./migrations` but are never run, to run these you would first make
sure the commented out database settings in `build.properties` are uncommented and correct, and then use
the following Phing target call in `build.xml`:

    <!-- Run DB migrations -->
    <phing phingfile="isotoma/targets/migrations.xml" inheritRefs="true" target="migrate"/>

