#
# MyProject Makefile
#
# Targets:
#  - clean: remove the staged files.
#

# Path
ROOT = .
PROJECT_LIB = $(ROOT)/lib
PROJECT_BIN = $(ROOT)/bin

# Binary
ZIP = zip
TAR = tar
PHP = php
DOXYGEN = php

YUI_VERSION = 2.3.5
YUI = java -jar $(PROJECT_BIN)/yuicompressor-$(YUI_VERSION).jar --charset UTF-8

# Project ID
NAME = my_project_name
VERSION = alpha

# Static
CSS_SRC_PATH = $(ROOT)/libraries/php/src
CSS_PACK_PATH = $(ROOT)/libraries/php/src
JS_SRC_PATH = $(ROOT)/libraries/php/src
JS_PACK_PATH = $(ROOT)/libraries/php/src

# Others
ZIP_NAME = $(NAME)-$(VERSION).zip
TAR_NAME = $(NAME)-$(VERSION).tar.gz

all: clean syntax locales static-pack
	@echo "----------------"
	@echo "Project build complete."
	@echo ""

doc:

list:

syntax:
	@echo "----------------"
	@echo "Check PHP syntax on all php files:"

# check syntax of PHP files
	@PHP_SOURCES=`find . -type f -name *.php | tr '\n' ' '`
	@for i in $(PHP_SOURCES); do test=`php -l $$i`; test2=`echo $$test | grep "Parse error"`; if [ "$$test2" != "" ]; then echo $$test; exit 1; fi; done;

	@echo "done"

test:

locales:
	@echo "----------------"
	@echo "Build GetText MO files:"

#todo

	@echo "done"

# Static packing
static-pack: static-pack-css static-pack-js

static-pack-css:
	@echo "----------------"
	@echo "Build CSS static pack files:"

#todo

	@echo "done"

static-pack-js:
	@echo "----------------"
	@echo "Build JavaScript static pack files:"

#todo

	@echo "done"

# Clean Useless file
clean:
	@echo "----------------"
	@echo "Cleaning useless files:"

#todo

	@echo "done"
