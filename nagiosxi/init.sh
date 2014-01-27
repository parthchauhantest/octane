#!/bin/sh -e
# $Id: init.sh 1024 2012-02-15 16:34:38Z agriffin $

xivar() {
	./xivar "$1" "$2"
	eval "$1"=\"\$2\"
}

# XI version
xivar xiver $(sed -n '/full/ s/.*=\(.*\)/\L\1/p' ./nagiosxi/basedir/var/xiversion)

# OS-related variables have a detailed long variable, and a more useful short
# one: distro/dist, version/ver, architecture/arch. If in doubt, use the short
. ./get-os-info
xivar distro  "$distro"
xivar version "$version"
xivar ver     "${version%%.*}" # short major version, e.g. "6" instead of "6.2"
xivar architecture "$architecture"

# Set dist variable like before (el5/el6 on both CentOS & Red Hat)
case "$distro" in
	CentOS | RedHatEnterpriseServer )
		xivar dist "el$ver"
		;;
	Debian )
		xivar dist "debian$ver"
		;;
	*)
		xivar dist $(echo "$distro$version" | tr A-Z a-z)
esac

# i386 is a more useful value than i686 for el5, because repo paths and
# package names use i386
if [ "$dist $architecture" = "el5 i686" ]; then
	xivar arch i386
else
	xivar arch "$architecture"
fi

case "$dist" in
	el5 | el6)
		if [ "$arch" = "x86_64" ]; then
			xivar php_extension_dir /usr/lib64/php/modules
		fi
		;;
	*)
		:
esac

