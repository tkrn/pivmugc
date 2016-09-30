#!/bin/bash

#
# Raspberry Pi User Group Controller Scirpt Installer
# URL: https://github.com/tkrn/pivmugc/
# Release Date: 2016-05-27
# Verion: 2.1

#
# Start Variables
#

DYMOURL="http://download.dymo.com/Software/Linux/dymo-cups-drivers-1.4.0.tar.gz"
PIVMUGCURL="https://github.com/tkrn/pivmugc/archive/master.zip"

#
# End Variables
#

echo
echo -e "\x1B[01;96mWelcome to the pivmugc (Pi VMware User Group Controller) installer. \x1B[0m"
echo

if [[ $EUID -ne 0 ]]; then
   echo " This script must be run as root! Exiting!"
   echo
   exit 1
fi

CPU=$(cat /proc/cpuinfo | awk '/Revision/ {print $3}')

HWCHECK=false

if [ "$CPU" = 'a02082' ]; then
  HWCHECK=true
fi

if [ "$CPU" = 'a22082' ]; then
  HWCHECK=true
fi

if [ "$HWCHECK" != true ]; then
  echo " The scripted installer only supports the Raspberry Pi 3."
  echo " A manual installation is required. Sorry."
  echo
  exit 0
fi

echo -e "\x1B[01;93mAssumptions: \x1B[0m"
echo
echo " 1. This installer also assumes a FRESH installation of Raspbian. "
echo
echo " 2. Run 'apt-get update' and 'apt-get upgrade' prior to proceeding"
echo "    for a faster installation."
echo
echo " 3. Please ensure you are connected to the internet to download"
echo "    updates and packages for the installer."
echo
echo " 4. The installation configures wlan0 with the 10.0.0.0/24 range. If "
echo "    this is a conflict, a manual installation will be required."
echo

read -r -p "${1:-Are you ready to proceed? [y/n]} " response

if [[ ${response,,} != "y" ]]; then
    echo
    echo "Exiting..."
    exit 0
fi

NGINX_CONF='server {
    root /usr/local/nginx/html;
    location / {
        index index.php index.html index.htm;
        try_files $uri /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}'

HOSTNAME=$(hostname)
INTERFACES='# interfaces(5) file used by ifup(8) and ifdown(8)\n\n# Please note that this file is written to be used with dhcpcd\n# For static IP, consult /etc/dhcpcd.conf and "man dhcpcd.conf"\n\n# Include files from /etc/network/interfaces.d:\nsource-directory /etc/network/interfaces.d\n\nauto lo\niface lo inet loopback\n\niface eth0 inet manual\n\niface wlan0 inet static\naddress 10.0.0.1\nnetwork 10.0.0.0\nnetmask 255.255.255.0\nbroadcast 10.0.0.255'
DHCPCD='\n#WLAN0 Configuration\n\ninterface wlan0\nstatic ip_address=10.0.0.1/24\nstatic routers=10.0.0.1\nstatic domain_name_servers=10.0.0.1'
WLAN0_CONF='interface=wlan0\nexpand-hosts\ndomain=local\ndhcp-range=10.0.0.10,10.0.0.50,24h\ndhcp-option=6,10.0.0.1'
HOSTAPD_CONF='# Basic configuration\ndriver=nl80211\ninterface=wlan0\nssid=pivmugc\nhw_mode=g\nchannel=8\nauth_algs=1\n\n# WPA configuration\nwpa=2\nwpa_passphrase=PIVMUGCPASS\nwpa_key_mgmt=WPA-PSK\nwpa_pairwise=TKIP\nrsn_pairwise=CCMP\nwpa_ptk_rekey=600\nmacaddr_acl=0'
HOSTAPD_DEFAULT='DAEMON_CONF="/etc/hostapd/hostapd.conf"'
DHCLIENT_CONF='timeout 10;\nlease {\ninterface "eth0";\nfixed-address 169.254.0.10;\noption subnet-mask 255.255.0.0;\nrenew 2 2022/1/1 00:00:01;\nrebind 2 2022/1/1 00:00:01;\nexpire 2 2022/1/1 0:00:01;\n}'
SUDOERS='ALL ALL=(root) NOPASSWD: /sbin/shutdown'

echo
echo -e "\x1B[01;93m PLEASE BE PATIENT! All items are ran as a background process. \x1B[0m"
echo

echo " *** Making RAM_DISK..."
if [ ! -d "/var/tmp" ]; then
  mkdir /var/tmp
fi
echo 'tmpfs /var/tmp tmpfs nodev,nosuid,size=96M 0 0' >> /etc/fstab
mount -a

echo " *** Running 'apt-get update'... "
apt-get -qq update -y

echo " *** Installing required binaries... "
apt-get -qq install lpr cups libcups2 libcupsimage2 sendmail -y > /var/tmp/apt-get-install-binaries-1.log

echo " *** Installing required binaries... "
apt-get -qq install nginx php5-fpm php5-sqlite -y > /var/tmp/apt-get-install-binaries-2.log

echo " *** Installing required binaries... "
apt-get -qq install unzip dnsmasq vim unzip hostapd gawk -y > /var/tmp/apt-get-install-binaries-3.log

echo " *** Installing development tools... "
apt-get -qq install build-essential libcups2-dev libcupsimage2-dev -y > /var/tmp/apt-get-install-devel.log

echo " *** Modifying sudoers permissions... "
echo $SUDOERS >> /etc/sudoers

echo " *** Applying NGINX configuration..."
mv /etc/nginx/sites-available/default /etc/nginx/sites-available/default.orginal
echo $NGINX_CONF >> /etc/nginx/sites-available/default
if [ ! -d "/usr/local/nginx/html" ]; then
  mkdir -p /usr/local/nginx/html
fi
service nginx reload
usermod -a -G www-data pi

echo " *** Applying PHP-FPM configuration..."
service php5-fpm start

echo " *** Configuring dhclient..."
cp /etc/dhcp/dhclient.conf /etc/dhcp/dhclient.bak
echo $DHCLIENT_CONF >> /etc/dhcp/dhclient.conf

echo " *** Configuring interfaces..."
mv /etc/dhcpcd.conf /etc/dhcpcd.conf.bak
echo -e $DHCPCD >>/etc/dhcpcd.conf
num=$(wc -l /etc/rc.local |awk '{print $1}')
head -n $(expr $num - 1) /etc/rc.local > /etc/rc.local.new
echo "ifup wlan0" >> /etc/rc.local.new
tail -n 1 /etc/rc.local >> /etc/rc.local.new
mv /etc/rc.local /etc/rc.local.bak
mv /etc/rc.local.new /etc/rc.local
echo "10.0.0.1" $HOSTNAME $HOSTNAME".local" >> /etc/hosts
systemctl reload networking.service
ifdown wlan0 > /dev/null
ifup wlan0 > /dev/null

echo " *** Configuring dnsmasq..."
mv /etc/dnsmasq.conf /etc/dnsmasq.conf.bak
echo -e $WLAN0_CONF > /etc/dnsmasq.conf
systemctl restart dnsmasq.service

echo " *** Configuring hostapd..."
mv /etc/default/hostapd /etc/default/hostapd.bak
echo -e $HOSTAPD_CONF > /etc/hostapd/hostapd.conf
echo $HOSTAPD_DEFAULT >> /etc/default/hostapd
systemctl restart hostapd.service

echo " *** Downloading Dymo drivers... "
cd /var/tmp
wget $DYMOURL -q

echo " *** Extracting Dymo source files... "
tar -xf dymo-cups-drivers-* > /var/tmp/dymo-source-extract.log

echo " *** Running configure on Dymo drivers... "
cd dymo-cups-drivers-*
./configure > /var/tmp/dymo-configure.log 2>&1

echo " *** Running make on Dymo drivers... "
make > /var/tmp/dymo-make.log 2>&1

echo " *** Running make install on Dymo dsrivers... "
make install > /var/tmp/dymo-make-install.log 2>&1

echo " *** Configuring CUPS..."
cp /etc/cups/cupsd.conf /etc/cups/cupsd.conf.bak
gawk -i inplace '/\<Location \/>/{ start=1 } {if(start) ++start; if(start==4) print "  Allow all"} 1' /etc/cups/cupsd.conf
gawk -i inplace '/\<Location \/admin>/{ start=1 } {if(start) ++start; if(start==4) print "  Allow all"} 1' /etc/cups/cupsd.conf
sed -i 's/localhost/*/' /etc/cups/cupsd.conf
usermod -a -G lpadmin pi
service cups restart

echo " *** Installing pivmugc application from github... "
cd /var/tmp
wget $PIVMUGCURL -q
unzip -qq master.zip 
cd pivmugc-master/
cp -rf * /usr/local/nginx/html/
rm -f /usr/local/nginx/html/pivmugc_installer.sh
chown www-data:www-data /usr/local/nginx/html/ -R

echo " *** Cleanup RAM_DISK"
cp /var/tmp/*.log /tmp
rm /var/tmp/* -r

echo
echo -e "\x1B[01;93m Please set a default the default printer in CUPS! \x1B[0m"
echo -e "\x1B[01;93m https://<host>:631/admin \x1B[0m"
echo
echo "Installation complete!"
echo
