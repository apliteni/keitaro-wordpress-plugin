set -e -x -o pipefail

rsync -av \
		--exclude=.idea \
		--exclude=.git \
		--exclude=svn \
		--exclude=assets \
		--exclude=Makefile \
		--exclude=keitaro.zip \
		--exclude=.gitignore \
		--exclude=.gitlab-ci.yml \
		--exclude=ci \
		./ ./svn/trunk && \

rsync -av \
 		./assets ./svn/

