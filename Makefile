clean:
	rm keitaro.zip

build: clean
	zip -r keitaro.zip ./ -x *.git* -x *.idea* -x Makefile -x *branches/* -x *trunc/* -x *tags/*

sync:
	rsync -av \
		--exclude=.idea \
		--exclude=.git \
		--exclude=svn \
		--exclude=assets \
		--exclude=Makefile \
		--exclude=keitaro.zip \
		--exclude=.gitignore \
		./ ./svn/trunk && \
	rsync -av \
    		./assets ./svn/


deploy: sync
	@read -p "Enter changes: " changes;
	cd ./svn/ && \
	svn add ./assets/* ./trunc/* 2>/dev/null; true && \
	svn ci -m '$$changes'