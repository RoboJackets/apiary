#!/bin/bash

set -e
set -o pipefail

curl -s --cookie cookies.txt https://nova.laravel.com/releases | lynx -dump -listonly -stdin | grep releases > releases.txt

if [[ $(wc --l < releases.txt) -lt 2 ]]
then
	echo "Error fetching Nova release information"
	exit 1
fi

latest_release=$(head -n 1 releases.txt | cut --delimiter=" " -f 5 | cut --delimiter="/" -f 5)

rm releases.txt

echo "Latest release is $latest_release"

if [[ -d nova ]]
then
	if [[ -f nova/release ]]
	then
		echo "Current installed release is $(cat nova/release)"
		if [[ $(cat nova/release) -ge latest_release ]]
		then
			echo "Up to date"
			exit 0
		else
			echo "Update needed"
			rm -rf nova
		fi
	else
		echo "No release file found"
		rm -rf nova
	fi
else
	echo "No Nova repository found"
fi

echo "Downloading latest version"
wget --load-cookies cookies.txt -O nova.zip https://nova.laravel.com/releases/$latest_release

unzip -q nova.zip

rm nova.zip

mv laravel-nova-* nova

echo $latest_release > nova/release
