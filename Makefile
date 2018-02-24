clean:
	rm keitaro.zip

build: clean
	zip -r keitaro.zip ./ -x *.git* -x *.idea* -x Makefile
