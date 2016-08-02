## Raspberry Pi VMUG Controller (pivmugc)

### Description
pivmugc provides web-based checkin functionality that was designed for a local
chapter of the VMware User Group (VMUG). pivmugc is written in the Fat-Free
Framework and was designed to be used on a Raspberry Pi. Although pivmugc can
be used with any type of web server.  

### Installation Method #1 (Script)
Start with a clean installation of Raspbian and issue the following commands:

> wget https://raw.githubusercontent.com/tkrn/pivmugc/master/pivmugc_installer.sh

> chmod +x pivmugc_installer.sh

> sudo ./pivmugc_installer.sh

This method will run apt-get update and install cups, nginx, php, compile Dymo drivers, configure most necessary services and create RAM_DISK a portion of memory used for writing tmp files to for better performance on the Rasberry Pi.

### Installation Method #2 (Manual)
Installation is simple. Download the latest master branch, extract the files and move the files into the web directory. Here is an example for a default NGINX web server. The paths will vary for lighttpd or Apache installations.

> cd /tmp

> wget https://github.com/tkrn/pivmugc/archive/master.zip

> unzip master.zip

> cd pivmugc-master/

> cp -rf * /usr/local/nginx/html/

> chown www-data:www-data /usr/local/nginx/html/ -R

### config.ini
A file that stores global variables.

###### DB_PATH
Do not change unless needed and you know what you're doing.

###### LABEL_LOGO
Define the logo that is printed on the name tag label. The file must be located
in the ui/images folder in the directory structure. The image will automatically
be resized but it is recommended to keep the logo less than 200px by 200px.

###### RAM_DISK
RAM_DISK is likely one of the most import variables in this configuration. On
most system /tmp is generally a location on the filesystem. Due to the
RaspberryPi nature, /tmp is on an SDcard by default. Because of this, the SDcard
introduces an increase in write response time. To help mitigate temporary files
creation, we'll create a small temporary filesytem out of RAM for a better
response time.

For a manual installation, complete the following to create a RAM disk:

> sudo mkdir /var/tmp

> sudo echo 'tmpfs /var/tmp tmpfs nodev,nosuid,size=96M 0 0' >> /etc/fstab

> sudo mount -a

### Usage
The following pages will allow to perform the following functions by URL.

* /checkin - Default homepage. Allows checkin of preregistered guests.
* /register - Allows for walk-in guests to register.
* /reprint - Reprint name tags for those who are already checked in.
* /admin - Administration functions for pivmugc. Import/export data.

        Default Administration Credentials: admin/pivmugc

#### Included 3rd-party package versions:

* FPDF - v1.81
* PHPExcel - v1.8
* jQuery - v3.1.0
* jQuery Notification Plugin - v2.3.8
* jQuery Validation Plugin - v1.15.0
