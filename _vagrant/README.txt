==== Vagrant for Kajona ====

* Configuration

        Settings for your VM are given in a minimum of 2 files

        +    _vagrant
        |_      ubutu1204
           |_     Vagrantfile    <= general settings for the VM !!



        +   ~/.vagrant.d
        |_     Vagrantfile    <= special settings on your environment, e.g. Network addresses




        To set up YOUR network settings for every VM on your computer create ~/.vagrant.d/Vagrantfile

        -----------

        # Network settings for all my vagrant machines
        VAGRANTFILE_API_VERSION = "2"
        Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
           ### use this for a static address
           config.vm.network "public_network", ip: "192.168.2.4"
           ### or use DHCP (switch to the VM with 'vagrant ssh' to get the current ip address)
           # config.vm.network "public_network"
        end

        -----------


        You can add further parameter in your personal Vagrantfile



* Use a VM with Kajona on the host machine

   - configure your personal Vagrantfile to setup your network (see above)
   - use a shell, cd to <KAJONADIR>/core/_vagrant/<VMNAME>
   - call vagrant and bring the machine up
     # vagrant up
   - the webserver of the VM is available on the host on
     http://localhost:8008  (this is via port forwarding)
     or
     http://<IP-ADDRESS-FROM-PERSONAL_VAGRANTFILE>  (via TCP/IP, remember your firewall!)

   - make sure to config your database settings in project/system/config.php !!


