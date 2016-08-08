## Raspberry Pi VMUG Controller (pivmugc)

### Description
pivmugc provides web-based checkin functionality that was designed for a local
chapter of the VMware User Group (VMUG). pivmugc is written in the Fat-Free
Framework and was designed to be used on a Raspberry Pi. Although pivmugc can
be used with any type of web server.  

[![pivmugc Front End Demo](https://s10.postimg.org/9pghci2c9/pi_frontend_demo_screenshot.jpg)](https://vimeo.com/177565807)

### Installation Method #1 (Script)
Start with a clean installation of Raspbian and issue the following commands:

> wget https://raw.githubusercontent.com/tkrn/pivmugc/master/pivmugc_installer.sh

> chmod +x pivmugc_installer.sh

> sudo ./pivmugc_installer.sh

This method will run apt-get update and install cups, nginx, php, compile Dymo
drivers, configure most necessary services and create RAM_DISK a portion of
memory used for writing tmp files to for better performance on the Raspberry Pi.

[![pivmugc Installer Demo](https://s10.postimg.org/vd5fmy2qh/pi_installer_screenshot.jpg)](https://vimeo.com/177562196)

### Installation Method #2 (Manual)
Installation is simple. Download the latest master branch, extract the files
and move the files into the web directory. Here is an example for a default
NGINX web server. The paths will vary for lighttpd or Apache installations.

> cd /tmp

> wget https://github.com/tkrn/pivmugc/archive/master.zip

> unzip master.zip

> cd pivmugc-master/

> sudo cp -rf * /usr/local/nginx/html/

> sudo chown www-data:www-data /usr/local/nginx/html/ -R

For a manual installation of the RAM_DISK:

> sudo mkdir /var/tmp

> sudo echo 'tmpfs /var/tmp tmpfs nodev,nosuid,size=96M 0 0' >> /etc/fstab

> sudo mount -a

### Post Installation Printer Configuration

Upon the Dymo driver installation, the printer must be added to the CUPS daemon
and set as the default printer for the system for use by the pivmugc
application.

Browse to the CUPS web interface for configuration:

> https://raspberrypi:631/admin

> Click on "Add Printer"

> Click on the printer under "Local Printers"

> Press "Continue"

> Press "Add Printer"

> Change the "Media Size" to "99014 Name Badge Label"

> Change the "Output Resolution" to the highest possible.

> Change "Print Density" to "Dark"

> Change "Print Quality" to "Barcodes and Graphics"

> Press "Set Default Options"

> Click on the Printer name

> Under the "Administration" drop down, click on "Set As Server Default"

[![pivmugc Post Installation Printer Configuration](https://s10.postimg.org/k22rykvvd/pi_post_install_screenshot.jpg)](https://vimeo.com/178011036)

### Usage
The following pages will allow to perform the following functions by URL.

* / - Default landing page.
* /checkin - Allows checkin of preregistered guests.
* /register - Allows for walk-in guests to register.
* /reprint - Reprint name tags for those who are already checked in.
* /admin - Administration functions for pivmugc. Import/export data.

        Default Administration Credentials: admin/pivmugc

[![pivmugc Basic Administration Demo](https://s10.postimg.org/wpn4ou061/pi_basic_administration_scr.jpg)](https://vimeo.com/177564587)

#### config.ini
A file that stores global variables.

###### DB_PATH
Do not change unless needed and you know what you're doing.

###### LABEL_LOGO
Define the logo that is printed on the name tag label. The file must be located
in the ui/images folder in the directory structure. The image will automatically
be resized but it is recommended to keep the logo less than 200px by 200px.

###### RAM_DISK
RAM_DISK is likely one of the most import components to increase the speed of
the Raspberry Pi. On unix-based systems, /tmp is a location on the filesystem
that resides on hard drive. Due to the Raspberry Pi nature,/tmp is on an SDcard
by default. Because of this, the SDcard introduces increased response times. To
help performance during use temporary files creation, the system will write to
temporary filesytem that is carved out of RAM for better response times. This is
automatically created in the installation script or see the manual installation
for RAM_DISK creation above.

#### Included 3rd-party package versions:

* FPDF - v1.81
* PHPExcel - v1.8
* jQuery - v3.1.0
* jQuery Notification Plugin - v2.3.8
* jQuery Validation Plugin - v1.15.0
