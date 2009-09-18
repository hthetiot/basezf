#
# Basezf Makefile
#
# Targets:
#  - clean: 	Remove the staged files
#  - doc: 		Generate the doc
#  - syntax:	Check syntax of PHP files
#  - test: 		Exec unitTest
#  - locale: 	Generate gettext files
#
# @copyright  Copyright (c) 2008 BaseZF
# @author     Harold Thetiot (hthetiot)
# @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

# Binary
ZIP = zip
TAR = tar
PHP = php
DOXYGEN = doxygen

# Project ID
PROJECT_NAME = Basezf
PROJECT_VERSION = alpha

# Others
RELEASE_NAME = $(PROJECT_NAME)-$(PROJECT_VERSION)
CHANGELOG_FILE_PATH = $(ROOT)/CHANGELOG

ZIP_NAME = $(NAME)-$(VERSION).zip
TAR_NAME = $(NAME)-$(VERSION).tar.gz

install: clean syntax
	@echo "----------------"
	@echo "Project install complete."
	@echo ""

all: clean syntax locale
	@echo "----------------"
	@echo "Project build complete."
	@echo ""

# Generate the doc
doc:
	@echo "----------------"
	@echo "Generate doxygen doc :"
	@$(DOXYGEN) ./etc/doxygen.cnf > $(PROJECT_LOG)/doc.log
	@echo "done"

# Check syntax of PHP files
syntax:
	@echo "----------------"
	@echo "Check PHP syntax on all php files:"
	@for i in `find . -type f -name *.ph* | tr '\n' ' '`; do test=`php -l $$i`; test2=`echo $$test | grep "Parse error"`; if [ "$$test2" != "" ]; then echo $$test; fi; done;
	@echo "done"

syntax-fast:
	@echo "----------------"
	@echo "Check PHP syntax on all php files updated:"
	@for i in `git-diff --name-only | grep '.ph' | tr '\n' ' '`; do test=`php -l $$i`; test2=`echo $$test | grep "Parse error"`; if [ "$$test2" != "" ]; then echo $$test; fi; done;
	@echo "done"

# Exec unitTest
test:
	@echo "----------------"
	@echo "Exec Units test:"
	@cd tests && phpunit BaseZF_AllTests
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
	@echo "done"

.PHONY: doc
