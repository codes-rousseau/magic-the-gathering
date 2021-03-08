#!/bin/bash -e

source ./docker/.env

docker stats $(docker container ls -f name=MAGIC --format={{.Names}})

