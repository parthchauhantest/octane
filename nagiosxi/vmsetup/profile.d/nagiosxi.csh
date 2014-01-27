if ( -x /usr/bin/id ) then
  if ( "`/usr/bin/id -u`" == 0 ) then
  grep -q '^root::' /etc/shadow && echo 'Please consider setting a root password.'
  endif
endif
