#!/bin/sh

public_path=public

csstidy_path=bin/csstidy
csstidy_params="--template=high --silent=false --merge_selectors=2"

yuicompressor_path=bin/yuicompressor.jar
yuicompressor_params="--charset UTF-8 --type js"


echo 'Compilation '$1' from "'$2'" in "'$3'"'
for ls_result  in `ls "$2"`
do
	# begin compilation
	echo ""
	echo  "\tCompiling $2/$ls_result:"
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
		echo "\t\tAdded $file"
		cat "$public_path/$file" >> tmp.compress

		done < $path

	# compress buffer to packed file
	if [ "$error" = "0" ]
		then
		output=`echo $3/$ls_result`

		# compress JS files
		if [ "$1" = "js" ]
		then
			echo "\tProcess: compilation using yuiCompressor"
			java -jar $yuicompressor_path $yuicompressor_params tmp.compress -o $output > /dev/null
		fi

		# compress CSS files
		if [ "$1" = "css" ]
		then
			echo "\tProcess: compilation using TidyCss"
			$csstidy_path tmp.compress $csstidy_params $output > /dev/null
		fi

		echo "\tFinished: \package available here : $output"
		rm -f tmp.compress
	fi

done
echo "done"

