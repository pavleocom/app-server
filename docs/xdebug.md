Xdebug is installed but not enabled by default.

To enable/disable xdebug run (when php container running):

`./app xdebug-on`

`./app xdebug-off`

Xdebug config file is in docker/php/custom-ini/xdebug.ini

Changes are not tracked but git.
To start tracking changes to this file run:

`git update-index --no-assume-unchanged FILE_NAME`

To stop tracking changes to this file run:

`git update-index --assume-unchanged FILE_NAME`

