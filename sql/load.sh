#!/bin/sh
# load.sh - A simple bash database loader
#
# Usage: load.sh (struct|import|test|clean|index|help) <db_name> <user> [<host_name>] [<adapter>]
#
# @copyright  Copyright (c) 2008 BaseZF
# @author     Harold Thétiot (hthetiot)
# @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

action="$1"

shift
db_name="$1"

shift
db_username="$1"

shift
db_hostname="$1"

echo $db_hostname
if [ -z "${db_hostname}" ]; then
    db_hostname=localhost
else
    shift
fi
echo $db_adapter
if [ -z "${db_adapter}" ]; then
    db_adapter=mysql
else
    shift
fi


#
# Check if database name provided
#
check_params()
{
    if [ -z "${db_name}" ]; then
        err 1 "No database name specified"
    fi

    if [ -z "${db_username}" ]; then
        err 1 "No database username specified"
    fi
}

#
# Import Structure of Database
#
struct_action()
{
    check_params
}

#
# Import Sample data
#
sample_action()
{
    check_params

    # clean
    echo "TODO: function"
}

#
# Import Index
#
index_action()
{
    check_params

    # clean
    echo "TODO: function"
}

#
# Import Structure, Test and Indexes
#
test_action()
{
    # clean
    echo "TODO: function"
}

#
# Clear Sample Data and rebuild Indexes
#
clean_action()
{
    check_params

    # clean
    echo "TODO: function"
}

#
#
#
usage()
{
    echo "Usage:"
    echo "  load.sh (struct|index|sample|test|clean|help) <db_name> [host_name] [(mysql|pgsql)]"
    echo "where:"
    echo "  struct      - create tables"
    echo "  default     - load default data to database"
    echo "  sample      - load sample data to database"
    echo "  index       - create primary and foreighn keys and indexes"
    echo "  test        - struct, default, sample, index commands together"
    echo "  clean       - struct, default, index commands together"
    echo "  help        - this help message"
}

#
# err exitval message
#       Display message to stderr and exit with exitval.
#
err()
{
        exitval=$1
        shift
        echo 1>&2 "*** ERROR: $*"
        exit $exitval
}

#
# warn message
#       Display message to stderr.
#
warn()
{
        echo 1>&2 "*** WARNING: $*"
}

# bootstrap
case "${action}" in
struct)
    struct_action
    ;;
sample)
    clean_action
    ;;
index)
    index_action
    ;;
test)
    struct_action
    test_action
    index_action
    ;;
clean)
    clean_action
    ;;
*)
    usage
    ;;
esac
