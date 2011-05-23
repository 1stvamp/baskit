#!/usr/bin/env php
<?php
// Setup all the paths
$home = getenv('HOME') . '/';
$cwd = getcwd() . '/';
$parts = $cwd . 'parts/';
$bin = $cwd . 'bin/';

// Remove any existing dynamic paths
if (!file_exists($parts)) {
        mkdir($parts);
        echo "Created ${parts}" . PHP_EOL;
}
if (!file_exists($bin)) {
        mkdir($bin);
        echo "Created ${bin}" . PHP_EOL;
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
echo implode(PHP_EOL, $out);
exec('pear install -o PEAR  2>&1', $out);
echo implode(PHP_EOL, $out);
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
echo implode(PHP_EOL, $out);
exec("${bin}pear install phing/phing-2.4.5  2>&1", $out);
echo implode(PHP_EOL, $out);
if (file_exists("${bin}phing")) {
        unlink("${bin}phing");
}
copy("${parts}pear/phing", "${bin}phing");
$phing_bin = file_get_contents("${bin}phing");
$phing_bin = str_replace('phing.php', 'phing_wrapper.php', $phing_bin);
file_put_contents("${bin}phing", $phing_bin);
chmod("${bin}phing", 0755);
$paths = implode(PATH_SEPARATOR, array(
    $cwd,
    $parts,
    "${parts}pear/php",
));
file_put_contents(
    "${parts}pear/php/phing_wrapper.php",
    "#!/usr/bin/env php\n<?php\nset_include_path('${paths}'.PATH_SEPARATOR.get_include_path());\ninclude '${parts}pear/php/phing.php';"
);
echo 'Created bin/phing' . PHP_EOL;

echo 'Done.' . PHP_EOL;
exit(0);
