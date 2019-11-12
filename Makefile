clean:
	ci/bin/clean.sh

build: clean
	ci/bin/pack.sh

sync:
	ci/bin/sync_with_trunk.sh

deploy: sync
	ci/bin/svn_commit.sh