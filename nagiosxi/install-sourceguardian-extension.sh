#!/bin/sh -e

. ./xi-sys.cfg

# Get PHP version
phpver=$(php -v | head -n 1 | cut -d ' ' -f 2 | cut -d . -f 1,2)

ixedfile="ixed.$phpver.lin"
entry="extension=$ixedfile"

if [ "$arch" = "x86_64" ]; then
	zipfile="sourceguardian/ixed4.lin.x86-64.zip"
else
	zipfile="sourceguardian/ixed4.lin.x86-32.zip"
fi

# Extract SourceGuardian extension to the proper directory
unzip -o "$zipfile" "$ixedfile" -d "$php_extension_dir"

if [ -f "$php_extension_dir/$ixedfile" ]; then
	echo "Sourceguardian extension found for PHP version $phpver"
else
	echo "No valid Sourceguardian extension found for PHP version $phpver"
	exit 1
fi

if grep -q "$entry" "$phpini" "$phpconfd"/*; then
	echo "Sourceguardian extension already in php.ini"
else
	echo "Adding Sourceguardian extension to php.ini"
	echo "$entry" > /etc/php.d/sourceguardian.ini
fi

