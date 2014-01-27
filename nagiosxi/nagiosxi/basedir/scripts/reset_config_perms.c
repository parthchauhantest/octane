#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <unistd.h>

int main()
{
   if(setuid( 0 )!=0)
	printf("ERROR TRYING TO SETUID ROOT!\n");
	else
		printf("SETUID ROOT OK\n");
   system( "/usr/local/nagiosxi/scripts/reset_config_perms.sh" );

   return 0;
}

