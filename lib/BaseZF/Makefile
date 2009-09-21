#
# Basezf Makefile
#
# Targets:
#  - doc: 		Generate the doc
#  - syntax:	Check syntax of PHP files
#  - test: 		Exec unitTest
#  - clean: 	Remove the useless files
#
# @copyright  Copyright (c) 2008 BaseZF
# @author     Harold Thetiot (hthetiot)
# @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

# Binary
PHP = php
DOXYGEN = doxygen

# Project ID
PROJECT_NAME = Basezf
PROJECT_VERSION = 0.7

all: clean syntax
	@echo "----------------"
	@echo "BaseZF build complete."
	@echo ""

# Generate the doc
doc:
	@echo "----------------"
	@echo "Generate doxygen doc :"
	@$(DOXYGEN) ./doxygen.cnf
	@echo "done"
#
# Alias
#
syntax:	php-syntax
test:	php-phpunit

#
# PHP
#

# Check syntax of PHP files
php-syntax:
	@echo "----------------"
	@echo "Check PHP syntax on all php files:"
	@for i in `find . -type f -name *.ph* -not -name ".*" | tr '\n' ' '`; do test=`php -l $$i`; test2=`echo $$test | grep "Parse error"`; if [ "$$test2" != "" ]; then echo $$test; fi; done;
	@echo "done"

# Check syntax of non commited PHP files
php-syntax-commit:
	@echo "----------------"
	@echo "Check PHP syntax on all php files updated:"
	@for i in `git-diff --name-only | grep '.ph' | tr '\n' ' '`; do test=`php -l $$i`; test2=`echo $$test | grep "Parse error"`; if [ "$$test2" != "" ]; then echo $$test; fi; done;
	@echo "done"

# Exec PHP unitTest
php-phpunit:
	@echo "----------------"
	@echo "Exec PHPUnits test:"
	@cd tests && phpunit --configuration phpunit.xml
	@echo "done"

# Remove the staged files
clean:
	@echo "----------------"
	@echo "Cleaning useless files:"
	@rm -f  `find . \( \
		-iname '*.DS_Store' -o \
		-iname '*~' -o \
		-iname '*.~*' -o \
		-iname 'static-pack-*' -o \
		-iname '*.bak' -o \
		-iname '#*#' -o \
		-iname '*.marks' -o \
		-iname '*.thumb' -o \
		-iname '*Thumbs.db' \) \
		-print`

# Remove doxygen generated doc
	@rm -f ./doc/html/*.html
	@rm -f ./doc/html/*.png
	@rm -f ./doc/html/*.map
	@rm -f ./doc/html/*.md5
	@rm -f ./doc/html/*
	@rm -f ./doc/latex/*.tex
	@rm -f ./doc/latex/*.png
	@rm -f ./doc/latex/*.map
	@rm -f ./doc/latex/*.md5
	@rm -f ./doc/latex/*
	@rm -f ./doc_warn.log
	@echo "done"

.PHONY: doc
