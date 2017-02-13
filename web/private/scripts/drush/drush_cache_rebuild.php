<?php

chdir(getenv('HOME') . '/code/web');

// Cache rebuild
print "\n====== Running 'drush cr' ======\n\n";
passthru('drush cr 2>&1');