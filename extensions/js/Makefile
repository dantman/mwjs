
all:
	-rm -r ./tmp
	mkdir ./tmp
	javac -cp ./js.jar -d tmp/ org/mediawiki/extensions/js/*.java
	jar cmf ./manifest ./mwjs.jar -C ./tmp/ org/mediawiki/extensions/js/
	-rm -r ./tmp
