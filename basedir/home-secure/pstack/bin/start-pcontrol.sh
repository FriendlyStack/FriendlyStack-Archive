#!/bin/bash
export LANG="en_US.UTF-8"
#killall pcontrol.pl
#/home/pstack/bin/pcontrol.pl >/dev/null 2>&1 &
#/home/pstack/bin/pcontrol.pl &
runlevel_variable=$(runlevel | cut -d ' ' -f2)
if (("$runlevel_variable" == 5)) ; then
/etc/init.d/FriendlyStackWatcher restart
/etc/init.d/pstack restart
fi
exit 0
