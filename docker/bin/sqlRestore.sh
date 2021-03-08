#!/bin/bash -e

source ./docker/.env

cat ${BDD_BASE}.sql | docker exec -i CR_MAGIC_DB /usr/bin/mysql -u${BDD_USER} -p${BDD_PASS}
