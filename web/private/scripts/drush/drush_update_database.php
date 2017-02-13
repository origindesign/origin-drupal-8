<?php

chdir(getenv('HOME') . '/code/web');

// Config Manager Import
print "\n====== Running 'drush updb' ======\n\n";
passthru('drush updb -y  2>&1');
