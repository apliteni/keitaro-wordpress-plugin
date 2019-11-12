#!/usr/bin/env bash
set -e -x -o pipefail

rsync -av \
		--exclude=.idea \
		--exclude=.git \
		--exclude=svn \
		--exclude=assets \
		--exclude=Makefile \
		--exclude=keitaro.zip \
		--exclude=.gitignore \
		--exclude=ci \
		./ ./svn/trunk && \

rsync -av \
 		./assets ./svn/

