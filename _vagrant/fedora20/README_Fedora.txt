IMPORTANT!!

When starting a Fedora VM with additional privat or public networks you’ll get the following error and the machine won’t start correctly:



/sbin/ifup p7p1 2> /dev/null

Stdout from the command:

ERROR    : [/etc/sysconfig/network-scripts/ifup-eth] Device p7p1 does not seem to be present, delaying initialization.


This is a Bug! See also:
https://github.com/mitchellh/vagrant/issues/1997


Perhaps a solution (didn’t work for me):
- Startup VM WITHOUT any private or public network settings (e.g. in ~/vagrant.d)
  -> the machine will start and will be provisioned
  -> you can connect just via port forwarding

- halt VM, set you network settings and start up again
  -> you will get the same error message but your environment was setup before 
  -> perhaps you can connect via TCP??? (just DHCP I guess…!)

 