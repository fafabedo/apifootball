#!/bin/sh

/Users/e043280/Project/personal/football/bin/console doctrine:schema:update --dump-sql>/Users/e043280/Project/personal/football/scripts/schema1.sql

# bin/console doctrine:schema:update --force
# bin/console doctrine:mapping:import App\\ImportSchema annotation --path=src/ImportSchema

# rsync -azr --verbose scripts/dump-sql/* webdeploy@api.apifootball.tk:/home/webdeploy/.