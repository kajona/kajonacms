#Entwicklung und Debugging mit Vagrant VMs für Kajona
 
Oft steht der Entwickler, sei es bei der Arbeit am Kajona-Kern oder an seinem eigenen Modul usw., vor dem Problem, dass ein ganz bestimmtes Detail auf einer ganz speziellen Umgebung getestet ausprobiert oder ein Fehler nachgestellt werden soll. Das kann z.B. eine bestimmte PHP-Version, ein Betriebssystem oder eine spezielle Datenbankkonfiguration sein.
 
Da spezielle Umgebungen oft nicht für jeden verfügbar sind bietet Kajona nun die Möglichkeit, mittels Vagrant schnell und einfach eine solche Umgebung zu bauen und zu starten. Dies gilt für die komplette Kajona-Entwicklerversion, die z.B. mittels git bezogen werden kann. Die „normalen“ Pakete bringen dies nicht mit.

##Schnelleinstieg – Kajona from Scratch mit Vagrant in aller Kürze

Dieses Kapitel soll einen ganz schnellen Einstieg ermöglichen – ohne lange Erklärung, nur Fakten!

Voraussetzungen: Git und Vagrant sind installiert
Start-Verzeichnis „kajona“ anlegen und nach kajona wechseln:

	mkdir kajona && cd kajona
	
Kajona per Git nach core klonen:

	git clone https://github.com/kajona/kajonacms.git core
	
Ggf. Berechtigungen anpassen (Achtung! Die folgenden Rechte nur auf Entwicklungsumgebungen verwenden!)

	chmod –R 0777 ../*
	
Auf der Kommandozeile wechseln nach core/_vagrant/<VM-NAME>

	cd core/_vagrant/fedora20
	
Vagrant Starten

	vagrant up
	
und auf die Meldung „Now you can 'vagrant ssh' into the new machine!“ warten.

Im Browser öffnen `http://localhost:8008/core/setupproject.php`

Im Browser öffnen `http://localhost:8008/`
Installation des Systems durchführen
Freuen!
 
Hinweise:
Die meisten Vagrant-Boxen unterstützen neben dem Zugriff per http auch den Zugriff per https

	http://localhost:8008/
	https://localhost:8443/
	
Benötigte Module wie php-gd oder xDebug werden automatisch mit installiert

	#Alle Befehle als Liste:
	mkdir kajona && cd kajona
	git clone https://github.com/kajona/kajonacms.git core
	chmod –R 0777 ../*
	cd core/_vagrant/fedora20
	vagrant up
	
##Was ist Vagrant?

Vagrant ist ein Programm, das über Scripte auf einen Virtualisierer wie VM Ware oder Virtualbox zugreifen kann und ein Betriebssystem auf Basis einer ISO-Vorlage herunterlädt und auf Basis einer zuvor festgelegte Konfiguration daraus eine lauffähige VM erstellt. Näheres dazu findet sich auf der Projektseite unter http://docs.vagrantup.com/v2/getting-started/index.html

##Kajona und Vagrant
Kajona bringt die für verschiedene Umgebungen notwendige Konfigurationspakete mit (in der Entwicklerversion, s.o.). Dazu befindet sich im Core-Ordner ein Verzeichnis _vagrant. In diesem Ordner befinden sich Unterordner für verschiedene Betriebssysteme. Diese können direkt verwendet oder als Vorlage für eigene verwendet werden.

###Eigene Umgebung vorbereiten
Zuerst muss das Programm Vagrant installiert werden. Es steht für verschiedene Umgebungen (Linux, Mac OS X, Windows) zur Verfügung.

> Hinweis: Unter Linux sollte immer das Paket direkt von http://www.vagrantup.com/downloads.html  bezogen werden, da die Pakete aus den Distributionen nicht immer aktuell sind und u.U. nicht funktionieren (eigene Erfahrung des Autors).
 
Zusätzlich muss ein Virtualisierer auf dem System vorhanden sein. Vagrant unterstützt u.a. VM Ware und Virtualbox.
 
Das per git bezogene Kajona-Entwicklerpaket sollte (muss nicht) in der Webserverumgebung verfügbar sein, idealerweise ist es zusätzlich in der IDE als Projekt angelegt. Vorteilhaft ist hier, xdebug entsprechend konfiguriert zu haben.
Der Grund dafür ist, dass die virtuelle Maschine mit dem vorliegenden Kajona gestartet wird! Also nicht eine Kopie oder eine veränderte Version sondern genau die aktuelle Entwicklerversion! Man kann also direkt in der Testumgebung debuggen und testen!
 
Liegt ein Virtualisierer lauffähig vor und das Kajona-Entwicklerpaket bereit kann's losgehen!
Vagrant vorbereiten
Wird eine VM per Vagrant gestartet liegt dieser wie oben erwähnt eine „Vorlage“ zugrunde. Um nicht bei jedem Start einer neuen VM das ISO dazu erst herunterzuladen kann man Vagrant „Boxen“ hinzufügen (vgl. Vagrant-Doku!).
Macht man das nicht wird die Vorlage im Verzeichnis der VM (also z.B. core/_vagrant/ubuntu) abgelegt. Da hat es aber nichts verloren. Fügt man eine Box hinzu wird die Vorlage im eigenen Heimatverzeichnis abgelegt und steht dann jeder weitern VM mit diesem Betriebssystem zur Verfügung. Das sieht dann im Filesystem  z.B. so aus:
 
            ~/.vagrant.d
               ├── boxes
               │   └── hashicorp-VAGRANTSLASH-precise32
               │       ├── 1.0.0
               │       │   └── virtualbox
               │       │       ├── box-disk1.vmdk
               │       │       ├── box.ovf
               │       │       ├── metadata.json
               │       │       └── Vagrantfile
               │       └── metadata_url
               ├── data
               ├── gems
               │   └── ruby
               │       └── 2.0.0
               ├── insecure_private_key
               ├── rgloader
               │   └── loader.rb
               ├── setup_version
               └── tmp
 
Folgender Befehl wird auf der Kommandozeile ausgeführt (unabhängig vom Betriebssystem) um eine Ubuntu12-Box für Virtualbox hinzuzufügen:
 
	vagrant box add hashicorp/precise32
	
###Vagrant konfigurieren
Nun ist alles vorbereitet um eine VM mittels Vagrant zu erstellen und zu starten. Optional kann man noch einige Konfigurationseinstellungen vornehmen, bspw. Einstellungen zum Netzwerk.
Es gibt mehrere mögliche Dateien zur Konfiguration. Sie heißen immer „Vagrantfile“ und liegen in unterschiedlichen Verzeichnissen. Die erste Datei liegt im Verzeichnis der VM, im Beispiel der Ubuntu-VM für Kajona sieht das also so aus:
 
             core
             └── _vagrant
                  ├── README.txt
                  └── ubuntu1204
                      ├── setup_vm.sh
                      └── Vagrantfile   <= Einstellungen für diese VM
 
Eigene Einstellungen, die auf dem verwendeten Rechner immer und für alle VM verwendet werden sollen können im eigenen Home-Verzeichnis abgelegt werden. Das sieht dann so aus:
 
                  ~/.vagrant.d
                      ├── Vagrantfile    <=allg. Einstellungen, z.B. zum Netzwerk
                      └── ...
Um der VM eine statische IP-Adresse zu geben würde man z.B. den folgenden Eintrag vornehmen:
 
	# Network settings for all my vagrant machines
	VAGRANTFILE_API_VERSION = "2"
	Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
	### use this for a static address
	config.vm.network "public_network", ip: "192.168.10.44"
	### or use DHCP (switch to the VM with 'vagrant ssh' to get the current ip address)
	# config.vm.network "public_network"
	end
	
###VM mit Vagrant für Kajona starten
Um eine wie zuvor beschriebene VM zu starten und zu verwenden geht man nun wie folgt vor:

* ggf. Netzwerkeinstellungen im eigenen Vagrantfile vornehmen
auf der Kommandozeile in das Verzeichnis der VM wechseln, also <KAJONADIR>/core/_vagrant/<VMNAME> 
* VM starten
vagrant up
* Die VM startet nun und gibt jede Menge Ausgaben aus. Die VMs, die mit Kajona ausgeliefert werden, werden durch verschiedene Scripte und Befehle komplett als LAMP-Server provisioniert, d.h. es werden z.B. Webserver und PHP installiert und verschiede Einstellungen vorgenommen.
* Der erste Start dauert etwas, danach geht es schneller wenn die zus. Software installiert ist.
* In das DocumentRoot des Webservers wird das Kajona-Verzeichnis des Hosts (also das, in dem man auch gerade die VM gestartet hat) eingebunden. Die virtuelle Maschine nutzt also die selben Dateien mit denen man lokal arbeitet und entwickelt!
* Um in die soeben gestartete VM zu gelangen gibt man folgenden Befehl ein:
vagrant ssh

Hat man zuvor eine gültige IP-Adresse für die VM konfiguriert kann man nun mit jedem Webserver im Netz auf das System zugreifen! (Natürlich abhängig von ggf. eingesetzten Firewalls).
Hat man keine Angaben zum Netzwerk gemacht steht das System via Port Forwarding unter der Adresse http://localhost:8008 zur Verfügung.
 
> Hinweis: Ggf. muss die Konfiguration der Datenbank erst angepasst werden. Nutzt man z.B. Mysql und als Host localhost kann die VM damit nichts anfangen. Man richtet also entweder eine Mysql-Anbindung auf einem Rechner ein, der von extern verwendet werden kann oder man verwendet SQLITE, dann wird nur eine lokale Datei verwendet.

###VM beenden und löschen
Soll die VM beendet werden ruft man im VM-Verzeichnis (also dort wo die VM gestartet wurde folgenden Befehl auf:
 
	vagrant halt
 
Ist man zuvor mit vagrant ssh in die VM gewechselt muss man sie natürlich erst wieder verlassen (logout, exit oder CTRL+D).
 
Eine bereits verwendete VM kann jederzeit wieder mit vagrant up gestartet werden, eine erneute Installation und Konfiguration der Software entfällt dann. Wird eine VM nicht mehr benötigt oder soll ganz bewusst neu erstellt werden kann sie entfernt werden. Dazu wird sie „zerstört“, d.h. gelöscht. Die geschieht durch den Befehl
 
	vagrant destroy
	
###Debugging des eigenen Projekts
Die per Vagrant erzeugte VM verwendet wie bereits beschrieben das lokale Kajona-Verzeichnis. Änderungen, die über die gewohnten Entwicklungswerkzeuge vorgenommen werden greifen also auch im virtuellen Testsystem. Das gilt auch für Debug-Tools. Die VMs bringen nicht nur Konfigurationen für den Kajona-Betrieb mit, es ist bspw. auch xdebug bereits konfiguriert.

Da die VM auf einem anderen Host installiert ist greift hier das Remote-Debugging von XDebug. Eine Debug-Session wird wie eine lokal Debug Session gestartet, normalweise per Brower-Plugin (z.B. https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc). In der IDE der Wahl sollte dann das Path-Mapping entsprechend konfiguriert werden.

###Weitere Hinweise
__Speicherplatz__
Jede VM stellt eine komplette Betriebssystem-Umgebung bereit und benötigt entsprechend Platz auf der Festplatte. Die Boxen (also Vorlagen) liegen im Home-Verzeichnis unter .vagrant.d/boxes. Die Daten der VM (also die Festplatten-Dateien für den Virtualisierer, die dann auch die zusätzliche Software enthalten) liegen im Standard-Verzeichnis des Virtualisierers. Bei Virtualbox ist das bspw. ~/.Virtualbox (oder was immer man eingestellt hat!).
 
__VM mit Oberfläche starten__
Standardmäßig werden Vagrant-VMs ohne Anzeige der Oberfläche – also „headless“ gestartet. Will man die Oberfläche in einem eigenen Fenster haben, also so als ob sie über den Virtualisierer gestartet wurde, muss man diese Einstellung im Vagrantfile vornehmen. Die Einträge sind schon vorhanden, man muss nur die Kommentarzeichen entfernen:
 
	config.vm.provider "virtualbox" do |vb|
     # Don't boot with headless mode
     vb.gui = true
 
    #   # Use VBoxManage to customize the VM. For example to change memory:
    #   vb.customize ["modifyvm", :id, "--memory", "1024"]
	end
 
__VM ohne Vagrant verwenden__
Eine einmal verwendete VM kann jederzeit wieder mit Vagrant gestartet werden, sie kann aber auch direkt über den Virtualisierer gestartet werden. In Virtualbox bspw. erscheint ein neuer Eintrag in der Liste der VMs, dieser kann über die Oberfläche gestartet und bearbeitet werden.
Der Benutzer zum Login ist ‚vagrant’, das Passwort ist ebenfalls ‚vagrant’. Der User vagrant ist sudo-berechtigt.