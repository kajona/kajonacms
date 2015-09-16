==== Vagrant for Kajona ====

###  05/2015


************************************************************************************
*** Setup your local environment, get all the software
************************************************************************************

    - install vagrant
    - get at least one 'box', the ubuntu precise (12.04)
      (if you don't do that first vagrant will create this when you run 'vagrant up' the first time in a preconfigured vagrant VM, e.g. under core/_vagrant/<VMNAME>
      # vagrant box add hashicorp/precise32
      -> this will create the folder .vagrant.d your HOME!!
	  
	   ~/.vagrant.d
	      ├── boxes
	      │   └── hashicorp-VAGRANTSLASH-precise32
	      │       ├── 1.0.0
	      │       │   └── virtualbox
	      │       │       ├── box-disk1.vmdk
	      │       │       ├── box.ovf
	      │       │       ├── metadata.json
	      │       │       └── Vagrantfile
	      │       └── metadata_url
	      ├── data
	      ├── gems
	      │   └── ruby
	      │       └── 2.0.0
	      ├── insecure_private_key
	      ├── rgloader
	      │   └── loader.rb
	      ├── setup_version
	      └── tmp
      


************************************************************************************
*** Configuration
************************************************************************************

        Settings for your VM are given in a minimum of 1 file but you can add more. 
        The name is always 'Vagrantfile'!!
        
        The first one is shipped with Kajona:  

	    core
	    └── _vagrant
		├── README.txt
		└── ubuntu1204
		    ├── setup_vm.sh
		    └── Vagrantfile   <= general settings for the VM !!


	The second one could be in your HOME dir. To set up YOUR network settings for every VM on your computer create ~/.vagrant.d/Vagrantfile: 

		~/.vagrant.d
		    ├── Vagrantfile    <= special settings on your environment, e.g. Network addresses
		    └── ...


        Example:

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
        During startup you will be asked which network interface you want to bridge to the given vm network (depending on you OS!)

        You can add further parameter in your personal Vagrantfile.


************************************************************************************
*** Use a VM with Kajona on the host machine
************************************************************************************

   - configure your personal Vagrantfile to setup your network (see above)
   
   - use a shell, cd to <KAJONADIR>/core/_vagrant/<VMNAME>
   
   - call vagrant and bring the machine up
     # vagrant up
     
     When you run it the first time all the needed software (apache, php, ...) will be installed! :-)
   
   - the webserver of the VM is available on the host on
     http://localhost:8008  (this is via port forwarding)
     or
     http://<IP-ADDRESS-FROM-PERSONAL_VAGRANTFILE>  (via TCP/IP, remember your firewall!)

   - make sure to config your database settings in project/system/config.php !!


   Hint:
   Your VM files are located in your virtualizers (e.g. virtualbox) VM dir. When open virtualbox you can see your new running machine.
   If you like to change the location of the VMs do that in the virtualizer's settings!
   
