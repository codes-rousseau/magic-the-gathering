#!/bin/bash -e

source ./docker/.env

docker exec CR_MAGIC_DB /usr/bin/mysqldump --databases -u${BDD_USER} -p${BDD_PASS} ${BDD_BASE} > ${BDD_BASE}.sql
