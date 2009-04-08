#!/bin/sh

php_syntax_check()
{
    retval=0
    for i in $(git-diff-index --name-only --cached HEAD -- | grep -e '\.php$'); do
        if [ -f $i ]; then
                output=$(php -l $i)
                retval=$?
                if [ $retval -gt 0 ]; then
                        echo "=============================================================================="
                        echo "Unstaging $i for the commit due to the follow parse errors"
                        echo "$output"
                        git reset -q HEAD $i
                fi
        fi
    done

    if [ $retval -gt 0 ]; then
        exit $retval
    fi
}
php_syntax_check
