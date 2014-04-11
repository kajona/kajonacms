IMPORTANT!!

When starting a Fedora VM with additional privat or public networks you’ll get the following error and the machine won’t start correctly:



/sbin/ifup p7p1 2> /dev/null

Stdout from the command:

ERROR    : [/etc/sysconfig/network-scripts/ifup-eth] Device p7p1 does not seem to be present, delaying initialization.


This is a Bug! See also:
https://github.com/mitchellh/vagrant/issues/1997

The mentioned files you’ll find under misc.

