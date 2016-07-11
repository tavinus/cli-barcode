##################################################
# Gustavo Arnosti Neves - 2016 Jul 11
# Simple makefile for system install / uninstall
# PHP cli-barcode-generator

# Change INSTDIR if you want to install somewhere else
INSTDIR=/usr/local/bin
BC_BASH=barcode

all: 

install:
	cp ${BC_BASH} ${INSTDIR}/${BC_BASH}
	chmod 755 ${INSTDIR}/${BC_BASH}

permissions:
	find . -type d -exec chmod 0755 {} \;
	find . -type f -exec chmod 0644 {} \;

uninstall:
	rm ${INSTDIR}/${BC_BASH}
