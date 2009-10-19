#
# MyProject Makefile
#
# Targets:
#  - doc
#  - syntax                     Check syntax of PHP files
#  - test                       Exec unitTest
#  - config                     Deploy config from $(PROJECT_CONFIG_PATH)/dist
#  - php-qa                     Exec PHP Quality reports
#  - php-phpcpd                 Exec PHP Quality Duplicate source report
#  - php-phpcs                  Exec PHP Quality syntax report
#  - php-phploc                 Exec PHP Quality stats report
#  - php-phpunit                Exec PHP unitTest
#  - php-phpunit-report         Exec PHP unitTest with coverage report
#  - php-syntax                 Check syntax of PHP files
#  - php-syntax-commit          Check syntax of non commited PHP file
#  - locale                     Generate gettext files
#  - locale-template            Generate .pot file for current project domain
#  - locale-update              Update .po files of from current .pot for all available local domains
#  - locale-deploy              Generate all .mo files
#  - locale-deploy-fuzzy        Generate all .mo files with fuzzy
#  - locale-translate-google    Transalte all .po files with Google Translate
#  - locale-clean               Remove all .mo and .po files
#  - static-pack                Deploy static packs
#  - static-pack-css            Deploy static packs for CSS
#  - static-pack-js             Deploy static packs for javascript
#  - log-clean                  Remove the log files
#  - log-archive                Archive the log files
#  - clean                      Remove the staged files
#  - update                     Update from current GIT repository
#
# @copyright  Copyright (c) 2008 BaseZF
# @author     Harold Thetiot (hthetiot)
# @license	  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

# Binary
ZIP = zip
TAR = tar
PHP = php
PHPUNIT = phpunit
PHPCS = phpcs
PHPLOC = phploc
PHPCPD = phpcpd
DOXYGEN = doxygen

# Project ID
PROJECT_NAME = MyProject
PROJECT_VERSION = alpha
PROJECT_MAINTAINER =
PROJECT_MAINTAINER_COURRIEL = debug@myproject.com
PROJECT_LOCALE_DOMAIN = message

# Path
ROOT = .
PROJECT_LIB_PATH = $(ROOT)/lib
PROJECT_BIN_PATH = $(ROOT)/bin
PROJECT_LOG_PATH = $(ROOT)/data/log
PROJECT_CONFIG_PATH = $(ROOT)/etc
PROJECT_TEST_PATH = $(ROOT)/tests
PROJECT_LOCALE_PATH = $(ROOT)/locale

# Files Finder
FIND_LOG_FILES = find $(PROJECT_LOG_PATH) -type f -not -name "README" -not -name ".*" -not -name "*.gz"
FIND_LOCALE_SRC = find $(PROJECT_LOCALE_PATH) -type f -iname '*.po' -not -name ".*"
FIND_LOCALE_FILES = find $(PROJECT_LOCALE_PATH) -type f -iname '*.po' -o -iname '*.mo' -not -name ".*"
FIND_PHP_LOCALE_FILES = find $(ROOT)/app $(PROJECT_LIB_PATH)/MyProject $(PROJECT_LIB_PATH)/BaseZF -type f -iname '*.php' -o -iname '*.phtml'
FIND_PHP_SRC_FULL = find $(ROOT) -type f -iname '*.php' -o -iname '*.phtml'
FIND_PHP_SRC = find $(ROOT) -type f -iname '*.php' -o -iname '*.phtml' \
	! -path '$(ROOT)/lib/ZFDebug/*' \
	! -path '$(ROOT)/lib/geshi*' \
	! -path '$(ROOT)/.*' \
	! -path '$(ROOT)/public/debug/*' \
	! -path '$(ROOT)/lib/Spyc.php' \
	! -path '$(ROOT)/lib/SphinxClient.php'

FIND_CLEAN_FILES = find $(ROOT) -type f \
	-iname '*.DS_Store' \
	-o -iname '*~' \
	-o -iname '*.~*' \
	-o -iname 'static-pack-*' \
	-o -iname '*.bak' \
	-o -iname '*.marks' \
	-o -iname '*.thumb' \
	-o -iname '*Thumbs.db'

# Locales
LOCALE_GETTEXT_DIR 	= LC_MESSAGES
LOCALE_DOMAINS 		= $(PROJECT_LOCALE_DOMAIN) time validate

# Static
CSS_PACK_CONFIG = $(PROJECT_CONFIG_PATH)/static/css.yml
JS_PACK_CONFIG = $(PROJECT_CONFIG_PATH)/static/javascript.yml

# Update Env
all: clean syntax locale-deploy static-pack
	f@echo "----------------"
	@echo "Project build complete."
	@echo ""

# Generate a new Env
install: clean config syntax locale static-pack
	@echo "----------------"
	@echo "Project install complete."
	@echo ""

# Generate the doc
doc:
	@echo "----------------"
	@echo "Generate doxygen doc :"
	@$(DOXYGEN) $(PROJECT_CONFIG_PATH)/doxygen.cnf > $(PROJECT_LOG_PATH)/doc.log
	@echo "done"

# Deploy config from $(PROJECT_CONFIG_PATH)/dist
config:
	@echo "----------------"
	@echo "Configure config files:"
	@$(PROJECT_BIN_PATH)/tools/config-generator.php configure $(PROJECT_CONFIG_PATH) $(PROJECT_CONFIG_PATH)/dist
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
	@list=`$(FIND_PHP_SRC_FULL)`; \
	for i in $$list;do \
		$(PHP) -l $$i | grep -v "No syntax errors";\
	done
	@echo "done"

# Check syntax of non commited PHP files
php-syntax-commit:
	@echo "----------------"
	@echo "Check PHP syntax on all php files updated:"
	@list=`git-diff --name-only | grep '.ph' | tr '\n' ' '`; \
	for i in $$list;do \
		$(PHP) -l $$i | grep -v "No syntax errors";\
	done
	@echo "done"

# Exec PHP unitTest
php-phpunit:
	@echo "----------------"
	@echo "Exec PHPUnits test:"
	@cd $(PROJECT_TEST_PATH) && $(PHPUNIT) --configuration phpunit.xml
	@echo "done"

# Exec PHP unitTest with coverage report
php-phpunit-report:
	@echo "----------------"
	@echo "Exec PHPUnits test coverage report:"
	@cd $(PROJECT_TEST_PATH) && $(PHPUNIT) --configuration phpunit-report.xml
	@echo "done"

# Exec PHP Quality reports
php-qa: php-phploc php-phpcs php-phpcpd

# Exec PHP Quality stats report
php-phploc:
	@echo "----------------"
	@echo "Exec PHP Code Stats report:"
	@$(PHPLOC) $(ROOT) > $(PROJECT_LOG_PATH)/php-loc.log
	@echo "done (output: $(PROJECT_LOG_PATH)/php-loc.log)"

# Exec PHP Quality syntax report
php-phpcs:
	@echo "----------------"
	@echo "Exec PHP CodeSniffer report:"
	@$(PHPCS) --extensions=php \
	--ignore=$(ROOT)/lib/ZFDebug/*,$(ROOT)/lib/geshi*,$(ROOT)/public/debug/*,$(ROOT)/lib/Spyc.php,$(ROOT)/lib/SphinxClient.php \
	-n $(ROOT) > $(PROJECT_LOG_PATH)/php-cs.log
	@echo "done (output: $(PROJECT_LOG_PATH)/php-cs.log)"

# Exec PHP Quality Duplicate source report
php-phpcpd:
	@echo "----------------"
	@echo "Exec PHP Code Duplicate report:"
	@$(PHPCPD) $(ROOT) > $(PROJECT_LOG_PATH)/php-cpd.log
	@echo "done (output: $(PROJECT_LOG_PATH)/php-cpd.log)"

#
# Locale
#

locale: clean locale-template locale-update locale-deploy

# Generate .pot file for current project domain
locale-template:
	@echo "----------------"
	@echo "Build GetText POT files for $(PROJECT_NAME):"
	@echo "" > $(PROJECT_LOCALE_PATH)/dist/$(LOCALE_GETTEXT_DIR)/$(PROJECT_LOCALE_DOMAIN).pot
	@$(FIND_PHP_LOCALE_FILES) | xgettext -L PHP --keyword=__ -j -s -o $(PROJECT_LOCALE_PATH)/dist/$(LOCALE_GETTEXT_DIR)/$(PROJECT_LOCALE_DOMAIN).pot --msgid-bugs-address=$(PROJECT_MAINTAINER_COURRIEL) -f -
	@msguniq $(PROJECT_LOCALE_PATH)/dist/$(LOCALE_GETTEXT_DIR)/$(PROJECT_LOCALE_DOMAIN).pot -o $(PROJECT_LOCALE_PATH)/dist/$(LOCALE_GETTEXT_DIR)/$(PROJECT_LOCALE_DOMAIN).pot
	@echo "done"

# Update .po files of from current .pot for all available local domains
locale-update:
	@echo "----------------"
	@echo "Update GetText PO files from POT files:"
	@for o in $(LOCALE_DOMAINS); do \
	for i in `find $(PROJECT_LOCALE_PATH) -maxdepth 1 -mindepth 1 -type d -not -name "dist" -not -name ".*"`; do \
		if [ -e "$$i/$(LOCALE_GETTEXT_DIR)/$$o.po" ] ; then \
			echo "Updated $$i/$(LOCALE_GETTEXT_DIR)/$$o.po"; \
			msgmerge --previous $$i/$(LOCALE_GETTEXT_DIR)/$$o.po $(PROJECT_LOCALE_PATH)/dist/$(LOCALE_GETTEXT_DIR)/$$o.pot -o $$i/$(LOCALE_GETTEXT_DIR)/$$o.po; \
			else mkdir $$i/$(LOCALE_GETTEXT_DIR)/ -p; \
            msginit -l `echo "$(ROOT)/$$i" | sed 's:./$(PROJECT_LOCALE_PATH)\/::g' | sed 's:\/LC_MESSAGES::g'`.utf-8 --no-translator --no-wrap -i $(PROJECT_LOCALE_PATH)/dist/$(LOCALE_GETTEXT_DIR)/$$o.pot -o $$i/$(LOCALE_GETTEXT_DIR)/$$o.po; \
		fi; \
		msguniq $$i/$(LOCALE_GETTEXT_DIR)/$$o.po -o $$i/$(LOCALE_GETTEXT_DIR)/$$o.po; \
	done \
	done

# Generate all .mo files
locale-deploy:
	@echo "----------------"
	@echo "Generate GetText MO files:"
	@list=`$(FIND_LOCALE_SRC)`; \
	for i in $$list;do \
		echo "Compiling  $$i"; \
		msgfmt --statistics $$i -o `echo $$i | sed s/.po/.mo/`; \
	done

# Generate all .mo files with fuzzy
locale-deploy-fuzzy:
	@echo "----------------"
	@echo "Generate GetText MO files with Fuzzy translation:"
	@list=`$(FIND_LOCALE_SRC)`; \
	for i in $$list;do \
		echo "Compiling  $$i"; \
		msgfmt -f --statistics $$i -o `echo $$i | sed s/.po/.mo/`; \
	done

# Transalte all .po files with Google Translate
locale-translate-google:
	@echo "----------------"
	@echo "Translate GetText PO files with Google translate:"
	@list=`$(FIND_LOCALE_SRC)`; \
	for i in $$list;do \
		$(PROJECT_BIN_PATH)/tools/gettext-translator.php en `echo "$$i" | cut -d / -f3 | cut -d _ -f1` $$i $$i; \
	done

# Remove all .mo and .po files
locale-clean:
	@echo "----------------"
	@echo "Clean GetText MO and PO files:"
	@list=`$(FIND_LOCALE_FILES)`; \
	for i in $$list;do \
		echo "Removed $$i"; \
		rm -f $$i; \
	done
	@echo "done"


#
# Static packing


# Deploy static packs
static-pack: clean static-pack-css static-pack-js

# Deploy static packs for CSS
static-pack-css:
	@echo "----------------"
	@$(PROJECT_BIN_PATH)/tools/static-pack.php css $(CSS_PACK_CONFIG) public

# Deploy static packs for javascript
static-pack-js:
	@echo "----------------"
	@$(PROJECT_BIN_PATH)/tools/static-pack.php js $(JS_PACK_CONFIG) public

#
# Log
#

# Remove the log files
log-clean:
	@echo "----------------"
	@echo "Cleaning log files:"
	@list=`$(FIND_LOG_FILES)`; \
	for i in $$list;do \
		echo "Removed $$i"; \
		rm -f $$i; \
	done
	@echo "done"

# Archive the log files
log-archive:
	@echo "----------------"
	@echo "Archive log files:"
	@list=`$(FIND_LOG_FILES)`; \
	for i in $$list;do \
		echo "Archived $$i"; \
		gzip $$i; \
	done
	@echo "done"

# Remove the staged files
clean:
	@echo "----------------"
	@echo "Cleaning useless files:"
	@list=`$(FIND_CLEAN_FILES)`; \
	for i in $$list;do \
		echo "Removed $$i"; \
		rm -f $$i; \
	done
	@echo "done"

# Update from current GIT repository
update:
	@echo "----------------"
	@echo "Update from repository:"
	@git pull

.PHONY: doc clean
