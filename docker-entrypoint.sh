#!/bin/bash

# Make sure the socket is accessible by Apache
chmod 666 /var/run/docker.sock

set -e
exec "$@"
