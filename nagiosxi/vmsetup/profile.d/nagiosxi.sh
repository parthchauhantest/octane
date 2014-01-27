# $Id: nagiosxi.sh 170 2010-06-17 20:54:27Z egalstad $
if [ -n "$BASH_VERSION" -o -n "$KSH_VERSION" -o -n "$ZSH_VERSION" ]; then
  [ -x /usr/bin/id ] || return
  tmpid=$(/usr/bin/id -u)
  [ "$tmpid" = "" ] && tmpid=0
  [ $tmpid -ne 0 ] && return
  # for bash and zsh, only if no alias is already set
  grep -q '^root::' /etc/shadow && 
	echo 'Please consider setting a root password.'
fi
