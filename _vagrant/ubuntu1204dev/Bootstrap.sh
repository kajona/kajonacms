#!/bin/sh
#proxyConf="http://yourproxy.com:8080"

#export {http_proxy,https_proxy,ftp_proxy}="http://yourproxy.com:8080"

# Install gnome-session-fallback
apt-get -qy install gnome-session-fallback
# Install Launcher.
apt-get -qy install gnome-do

echo "Installing PhpStorm..."

# We need root to install
[ $(id -u) != "0" ] && exec sudo "$0" "$@"

# Attempt to install a JDK
# apt-get install openjdk-7-jdk

# Set options for unattented Java installation
echo debconf shared/accepted-oracle-license-v1-1 select true | sudo debconf-set-selections
echo debconf shared/accepted-oracle-license-v1-1 seen true | sudo debconf-set-selections

add-apt-repository ppa:webupd8team/java && apt-get -q update && apt-get -qy install oracle-java7-installer

# Prompt for edition
#while true; do
#    read -p "Enter 'U' for Ultimate or 'C' for Community: " ed 
#    case $ed in
#        [Uu]* ) ed=U; break;;
#        [Cc]* ) ed=C; break;;
#    esac
#done
# Set Edition for unattended installation
ed=U;

# Fetch the most recent community edition URL
URL=$(wget "http://www.jetbrains.com/phpstorm/download/download_thanks.jsp?os=linux" -qO- | grep -o -m 1 "http://download.jetbrains.com/webide/.*gz")
echo ${URL};

# Truncate filename
FILE=$(basename ${URL})
echo ${FILE};
# Download binary
wget -qcO /home/vagrant/Downloads/${FILE} ${URL} --read-timeout=5 --tries=0

# Set directory name
DIR="${FILE%\.tar\.gz}"

# Untar file
if mkdir /opt/${DIR}; then
    tar -xzf /home/vagrant/Downloads/${FILE} -C /opt/${DIR} --strip-components=1
fi

# Grab executable folder
BIN="/opt/$DIR/bin"

# Add permissions to install directory
chmod 755 ${BIN}/phpstorm.sh

# Set desktop shortcut path
DESK="/usr/share/applications/PhpStorm.desktop"

# Add desktop shortcut
echo -e "[Desktop Entry]\nEncoding=UTF-8\nName=PhpStorm\nComment=PhpStorm IDEA\nExec=${BIN}/phpstorm.sh\nIcon=${BIN}/webide.png\nTerminal=false\nStartupNotify=true\nType=Application" > ${DESK}

cp ${DESK} /home/vagrant/Desktop/
chown vagrant /home/vagrant/Desktop/PhpStorm.desktop
chmod a+x /home/vagrant/Desktop/PhpStorm.desktop

echo "PhpStorm installed"

echo "Install git"

apt-get -y install git

# create proxy shortcuts
VAGRANTDESK="/home/vagrant/Desktop/"
VAGRANTDOCUMENTS="/home/vagrant/Documents/skripts/"

mkdir ${VAGRANTDOCUMENTS}
echo -e "git config --global --unset-all http.proxy http://yourproxy.com:8080\ngit config --global --unset-all https.proxy http://yourproxy.com:8080\ngit config --global --add http.proxy http://yourproxy.com:8080\ngit config --global --add https.proxy http://yourproxy.com:8080\ngit config --global url.\"https://\".insteadOf git://" > ${VAGRANTDOCUMENTS}set-git-proxy.sh

chown vagrant ${VAGRANTDOCUMENTS}set-git-proxy.sh
chmod a+x ${VAGRANTDOCUMENTS}set-git-proxy.sh

echo -e "[Desktop Entry]\nEncoding=UTF-8\nName=Set git Proxy\nComment=Set git Proxy\nExec=${VAGRANTDOCUMENTS}set-git-proxy.sh\nTerminal=true\nType=Application" > ${VAGRANTDESK}SetProxy.desktop
chown vagrant ${VAGRANTDESK}SetProxy.desktop
chmod a+x ${VAGRANTDESK}SetProxy.desktop

echo -e "git config --global --unset-all http.proxy http://yourproxy.com:8080\ngit config --global --unset-all https.proxy http://yourproxy.com:8080\ngit config --global --unset-all git.proxy http://yourproxy.com:8080\n" > ${VAGRANTDOCUMENTS}unset-git-proxy.sh
chown vagrant ${VAGRANTDOCUMENTS}unset-git-proxy.sh
chmod a+x ${VAGRANTDOCUMENTS}unset-git-proxy.sh

echo -e "[Desktop Entry]\nEncoding=UTF-8\nName=Unset git Proxy\nComment=Unset git Proxy\nExec=${VAGRANTDOCUMENTS}set-git-proxy.sh\nTerminal=true\nType=Application" > ${VAGRANTDESK}UnsetProxy.desktop
chown vagrant ${VAGRANTDESK}UnsetProxy.desktop
chmod a+x ${VAGRANTDESK}UnsetProxy.desktop

echo "Git installed"


echo "Set Proxy for gradle"

GRADLEPROP="/home/vagrant/.gradle/gradle.properties"

mkdir /home/vagrant/.gradle/
chown vagrant /home/vagrant/.gradle/

# echo -e "systemProp.http.proxyHost=yourproxy.com\nsystemProp.http.proxyPort=8080\nsystemProp.http.nonProxyHosts=*.nonproxyrepos.com|localhost\nsystemProp.https.proxyHost=yourproxy.com\nsystemProp.https.proxyPort=8080\nsystemProp.https.nonProxyHosts=*.nonproxyrepos.com|localhost" > ${GRADLEPROP}

chown vagrant ${GRADLEPROP}
echo "Gradle proxy set."
 
# bootstrap ansible for convenience on the control box
apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys 36A1D7869245C8950F966E92D8576A8BA88D21E9
sh -c "echo deb https://get.docker.io/ubuntu docker main > /etc/apt/sources.list.d/docker.list"
apt-get update
apt-get -y install lxc-docker
 
# need to add proxy specifically to docker config since it doesn't pick them up from the environment
#sed -i '$a export http_proxy='$proxyConf /etc/default/docker
#sed -i '$a export https_proxy='$proxyConf /etc/default/docker
 
# enable non-root use by vagrant user
groupadd docker
gpasswd -a vagrant docker
 
# restart to enable proxy
service docker restart

echo "Install fig"
apt-get install curl
curl -L https://github.com/docker/fig/releases/download/1.0.1/fig-`uname -s`-`uname -m` > /usr/local/bin/fig; chmod +x /usr/local/bin/fig

# Install shipyard
echo "Install shipyard."
docker run -it -d --name shipyard-rethinkdb-data --entrypoint /bin/bash shipyard/rethinkdb -l
docker run -it -P -d --name shipyard-rethinkdb --volumes-from shipyard-rethinkdb-data shipyard/rethinkdb
docker run -it -p 8080:8080 -d --name shipyard --link shipyard-rethinkdb:rethinkdb shipyard/shipyard
echo "Shipyard installed."