#!/bin/bash -e

source ./docker/.env

docker logs --tail 100 -f CR_MAGIC_Apache
