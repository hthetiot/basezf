#!/bin/sh
# static-pack.sh - A simple compressor bash script for CSS and JS files
#
# Usage:
# ./static-pack.sh (js|css) <source_path> <dest_path>
#
# @copyright  Copyright (c) 2008 BaseZF
# @author     Harold Thétiot (hthetiot)
# @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

# path to js/pack and css/pack directory
public_path=public

# TidyCss config
csstidy_path=bin/tools/csstidy
csstidy_params="--template=high --silent=false --merge_selectors=4"

# YuiCompressor config
yuicompressor_path=bin/tools/yuicompressor.jar
yuicompressor_params="--charset UTF-8 --type js"

if [ "$#" = "0" ]
then
	echo "usage: ./static-pack.sh (js|css) source_path dest_path"
	exit
fi

echo 'Compilation '$1' from "'$2'" in "'$3'"'
for ls_result  in `ls "$2"`
do
	# @todo :

	# begin compilation
	echo ""
	echo  "    Compiling: \"$2/$ls_result\""
	error=0;
	input=`echo -n`
	path=`echo "$2/$ls_result"`

	# create buffer
	while read file
		do

		extension=`echo $file | grep $1$`
		if [ "$extension" = "" ]
		then
			continue
		fi
		echo "        Added $file"
		cat "$public_path/$file" >> tmp.compress

		done < $path

	# compress buffer to packed file
	if [ "$error" = "0" ]
		then
		output=`echo $3/$ls_result`

		# compress JS files
		if [ "$1" = "js" ]
		then
			echo "    Process: compilation using yuiCompressor"
			java -jar $yuicompressor_path $yuicompressor_params tmp.compress -o $output > /dev/null
		fi

		# compress CSS files
		if [ "$1" = "css" ]
		then
			echo "    Processing: compilation using TidyCss"
			$csstidy_path tmp.compress $csstidy_params $output > /dev/null
		fi

		echo "    Finished: packed file path \"$output\""
		rm -f tmp.compress

	else
		echo "    Fatal Error code: \"$error\""
		rm -f tmp.compress
	fi

done
echo "done"

