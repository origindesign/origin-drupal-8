<?php

chdir(getenv('HOME') . '/code/web');

// Config Manager Import
print "\n====== Running 'drush config-import' ======\n\n";
passthru('drush config-import -y  2>&1');
