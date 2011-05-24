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
    ln -s ./isotoma ../isotoma

    # Build the project with Phing
    bin/phing

    ls -lah
    # You should now see bin, parts and var directories, with several scripts
    # and dependencies installed.
