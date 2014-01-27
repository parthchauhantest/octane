#!/bin/sh

echo "Nagios XI Postgres Database Sequence Information"
echo ""
echo "CURRENT VALUES"
echo "--------------"
for seq in xi_commands_command_id_seq xi_events_event_id_seq xi_meta_meta_id_seq xi_options_option_id_seq xi_sysstat_sysstat_id_seq xi_usermeta_usermeta_id_seq xi_users_user_id_seq ; do
   val=`psql -U nagiosxi nagiosxi -q -t -A -c "SELECT last_value FROM $seq"`
   echo "$seq = $val"
done
echo ""
