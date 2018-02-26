clean:
	rm keitaro.zip

build: clean
	zip -r keitaro.zip ./ -x *.git* -x *.idea* -x Makefile -x *branches/* -x *trunc/* -x *tags/*

copy:
	rsync -av \
		--exclude=.idea \
		--exclude=.git \
		--exclude=svn \
		--exclude=Makefile \
		./ ./svn/trunc

deploy: clean copy
	cd ./svn/ && svn add ./trunc