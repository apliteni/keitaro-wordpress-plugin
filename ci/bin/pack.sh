#!/usr/bin/env bash
set -e -x -o pipefail

zip -r keitaro.zip ./ \
  -x *.git* \
  -x *.idea* \
  -x Makefile \
  -x *branches/* \
  -x *trunc/* \
  -x *tags/* \
  -x ci/*
