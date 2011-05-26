#!/usr/bin/env php
<?php
/**
 * baskit - sandboxed build environment for PHP, PEAR and Phing
 *
 * @package Baskit
 * @version 1.0.0
 * @author Wes Mason <wes.mason@isotoma.com>
 * @copyright 2011 Isotoma Limited
 * @license http://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 */

// Setup all the paths
$home = getenv('HOME') . '/';
$cwd = getcwd() . '/';
$parts = $cwd . 'parts/';
$bin = $cwd . 'bin/';
$cache = $cwd . 'download_cache/';

// Create dynamic paths for environment
if (!file_exists($parts)) {
        mkdir($parts);
        echo "Created ${parts}" . PHP_EOL;
}
if (!file_exists($bin)) {
        mkdir($bin);
        echo "Created ${bin}" . PHP_EOL;
}
if (!file_exists($cache)) {
        mkdir($cache);
        echo "Created ${cache}" . PHP_EOL;
}

// Move any existing ~/.pearrc out of the way, temporarily
$moved_rc = false;
if (file_exists("${home}.pearrc")) {
        rename("${home}.pearrc", "${home}.pearrc_");
        $moved_rc = true;
}

// Use the system isntalled PEAR to generate a local copy
echo 'Creating sandboxed ~/.pearrc' . PHP_EOL;
exec("pear config-create ${parts} ${home}.pearrc 2>&1", $out);
echo implode(PHP_EOL, $out) . PHP_EOL;
exec('pear install -o PEAR  2>&1', $out);
echo implode(PHP_EOL, $out) . PHP_EOL;
rename("${home}.pearrc", "${cwd}.pearrc");

if ($moved_rc) {
        rename("${home}.pearrc_", "${home}.pearrc");
}

if (file_exists("${bin}pear")) {
        unlink("${bin}pear");
}
// Create a wrapper around the local PEAR to reference the right .pearrc
// in the project path rather than looking in $HOME
file_put_contents("${bin}pear", "#!/bin/bash\n${parts}/pear/pear -c ${cwd}.pearrc \$@");
chmod("${bin}pear", 0755);
echo 'Created bin/pear' . PHP_EOL;

// Install Phing locally and wrap it's script
exec("${bin}pear channel-discover pear.phing.info  2>&1", $out);
echo implode(PHP_EOL, $out) . PHP_EOL;
exec("${bin}pear install phing/phing  2>&1", $out);
echo implode(PHP_EOL, $out) . PHP_EOL;
if (file_exists("${bin}phing")) {
        unlink("${bin}phing");
}
copy("${parts}pear/phing", "${bin}phing");
$phing_bin = file_get_contents("${bin}phing");
$phing_bin = str_replace('phing.php', 'phing_wrapper.php', $phing_bin);
file_put_contents("${bin}phing", $phing_bin);
chmod("${bin}phing", 0755);
$paths = implode(PATH_SEPARATOR, array(
    $parts,
    "${parts}pear/php",
));
file_put_contents(
    "${parts}pear/php/phing_wrapper.php",
    "#!/usr/bin/env php\n<?php\nset_include_path('${paths}');\ninclude '${parts}pear/php/phing.php';"
);
echo 'Created bin/phing' . PHP_EOL;

file_put_contents(
    "${bin}php",
    "#!/bin/sh\nphp -d include_path=${paths} \$@"
);
chmod("${bin}php", 0755);
echo 'Created bin/php' . PHP_EOL;

$baskit_dir = realpath(dirname(__FILE__));
if (!file_exists($baskit_dir)) {
    symlink($baskit_dir, "${cwd}baskit");
    echo 'Symlinked ./baskit to ' . $baskit_dir . PHP_EOL;
}

echo 'Done.' . PHP_EOL;
exit(0);
