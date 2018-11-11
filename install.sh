#!/bin/bash

##FriendlyStack, a system for managing physical and electronic documents as well as photos and videos
##Copyright (C) 2018  Dimitrios F. Kallivroussis, Friendly River LLC
##
##This program is free software: you can redistribute it and/or modify
##it under the terms of the GNU Affero General Public License as
##published by the Free Software Foundation, either version 3 of the
##License, or (at your option) any later version.
##
##This program is distributed in the hope that it will be useful,
##but WITHOUT ANY WARRANTY; without even the implied warranty of
##MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
##GNU Affero General Public License for more details.
##
##You should have received a copy of the GNU Affero General Public License
##along with this program.  If not, see <http://www.gnu.org/licenses/>.

SCRIPTPATH=$( cd $(dirname $0) ; pwd -P )
cd "$SCRIPTPATH"

##Required for self installing image

#stty -F /dev/ttyACM0 cs8 19200 ignbrk -brkint -icrnl -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts
#sleep 1
##echo -e "\x029F" > /dev/ttyACM0
#echo "testtest" > /dev/ttyACM0
#sleep 5
##echo -e "\x029F" > /dev/ttyACM0
#echo -e "\x029I" > /dev/ttyACM0
#echo -e "\x029A" > /dev/ttyACM0
#echo -e "\x029W" > /dev/ttyACM0
#echo -e "\x029u" > /dev/ttyACM0
#echo -e "\x020FriendlyStack" > /dev/ttyACM0
#echo -e "\x021Installing... " > /dev/ttyACM0


if [ -e /dev/ttyUSB0 ]; then ln -s /dev/ttyUSB0 /dev/pcontrol; fi;
if [ -e /dev/ttyACM0 ]; then ln -s /dev/ttyACM0 /dev/pcontrol; fi;

##Required for self installing image

#LD_LIBRARY_PATH=/cdrom/mystuff
#export LD_LIBRARY_PATH
#/cdrom/mystuff/avrdude -qq -C/cdrom/mystuff/avrdude.conf -v -patmega328p -carduino -P/dev/pcontrol -b115200 -D -Uflash:w:/cdrom/mystuff/FSCU.hex:i

sleep 5
stty -F /dev/pcontrol cs8 19200 ignbrk -brkint -icrnl -imaxbel -opost -onlcr -isig -icanon -iexten -echo -echoe -echok -echoctl -echoke noflsh -ixon -crtscts -hupcl
if [ -e /dev/ttyUSB0 ]
then
cat < /dev/pcontrol &
sleep 3
echo -e "\x02"9u > /dev/pcontrol
echo -e "\x02"9A > /dev/pcontrol
echo -e "\x02"0FriendlyStack > /dev/pcontrol
echo -e "\x02"1Installing... > /dev/pcontrol
echo -e "\x02"9I > /dev/pcontrol
sleep 1
killall cat
fi
if [ -e /dev/ttyACM0 ]
then
sleep 3
echo -e "\x02"9u > /dev/pcontrol
echo -e "\x02"9W > /dev/pcontrol
echo -e "\x02"9A > /dev/pcontrol
echo -e "\x02"9d > /dev/pcontrol
echo -e "\x02"0FriendlyStack > /dev/pcontrol
echo -e "\x02"1Installing... > /dev/pcontrol
echo -e "\x02"9I > /dev/pcontrol
fi


printf "\nUpdating System"
cp -r /$SCRIPTPATH/packages /tmp/packages
echo "deb file:/tmp/packages ./" > /etc/apt/sources.list.d/friendlystack.list
apt-get -y update

printf "\nInstalling Base Packages"
SQLPASSWORD=$(< /dev/urandom tr -dc _A-Z-a-z-0-9 | head -c${1-32};echo;)
echo 'mysql-server-5.7 mysql-server/root_password password' $SQLPASSWORD | debconf-set-selections
echo 'mysql-server-5.7 mysql-server/root_password_again password' $SQLPASSWORD | debconf-set-selections
tasksel install lamp-server samba-server standard openssh-server

##This is provided for compatibilizy with the image installation and will fail in the installscript
mysqladmin -u root -pdb4dm1n password $SQLPASSWORD


apt-get -y --allow-unauthenticated install cups wpasupplicant sane-utils php-fpdf php-xml libimage-exiftool-perl liblingua-identify-perl libclass-dbi-perl libproc-daemon-perl zbar-tools libtiff-tools imagemagick graphicsmagick libav-tools libreoffice ntfs-3g libgphoto2 gphoto2 libdbd-mysql-perl libpdf-api2-perl wireless-tools tesseract leptonica libopenjp2-7 libimobiledevice ifuse libusbmuxd usbmuxd libplist3 jmtpfs convmv liblinux-inotify2-perl apt-offline ifuse cryptsetup libatkmm-1.6 libboost-program-options1.58 libglibmm-2.4 libgraphicsmagick++-q16 libgtkmm-2.4 libpangomm-1.4 libsigc++ libgraphicsmagick++-q16-12 libexpect-perl libio-pty-perl libio-stty-perl libconfig-general-perl smartmontools cryptsetup-bin libcld2-0 avrdude

rm /etc/apt/sources.list.d/friendlystack.list
rm -rf /tmp/packages

printf "Please enter the Username for the FriendlyStack User: "
USERNAME=friendly
PASSPHRASE=FriendlyStack
stty -echo
printf "Password: "
PASSWORD=stack
stty echo
CN=`hostname -I | xargs`
CN1=`hostname | xargs`
CN2=`hostname | xargs`.local
echo $CN
useradd $USERNAME && (echo -ne "$PASSWORD\n$PASSWORD\n" | passwd $USERNAME)
printf "\nCreating Groups"
groupadd -f FriendlyStack
usermod -a -G FriendlyStack $USERNAME
usermod -a -G FriendlyStack www-data

printf "\nAdding Apache Account"
echo -ne "$PASSWORD\n" | htpasswd -c -i /etc/apache2/pStack.password $USERNAME


digest="$( printf "%s:%s:%s" "$USERNAME" "pStack" "$PASSWORD" | md5sum | awk '{print $1}' )"
printf "%s:%s:%s\n" "$USERNAME" "pStack" "$digest" >> "/etc/apache2/pStack.digest"

printf "\nCreate Database Schema"
stty -echo
printf "\nPlease enter MySQL Administrator Password: "
stty echo
/usr/bin/mysql --batch -u root -p$SQLPASSWORD < /$SCRIPTPATH/FriendlyStack.sql 2>/dev/null

printf "\nCreating Directory Structure"
sudo mkdir /home/pstack
sudo mkdir /home/pstack/bin
sudo mkdir /home/pstack/Documents
sudo mkdir /home/pstack/Multimedia
sudo mkdir /home/pstack/tmp
sudo mkdir /home/pstack/Inbox
sudo mkdir /home/pstack/Inbox/Pictures
sudo mkdir /home/pstack/Inbox/PicturesOCR
sudo mkdir /home/pstack/Inbox/Scan
sudo mkdir /home/pstack/Inbox/tmp
sudo mkdir /home/pstack/ScanInbox
sudo mkdir /home/pstack/Previews
sudo mkdir /home/pstack/CA
chown -R root:FriendlyStack /home/pstack/Documents
chown -R root:FriendlyStack /home/pstack/Previews
chown -R root:FriendlyStack /home/pstack/Multimedia
chown -R root:root /home/pstack/CA
chown -R root:FriendlyStack /home/pstack/Inbox/Pictures
chown -R root:FriendlyStack /home/pstack/Inbox/PicturesOCR
chmod -R 0770 /home/pstack/Documents
chmod -R 0770 /home/pstack/Previews
chmod -R 0770 /home/pstack/Multimedia
chmod -R 0700 /home/pstack/CA
chmod -R 0770 /home/pstack/Inbox/Pictures
chmod -R 0770 /home/pstack/Inbox/PicturesOCR

printf "\nInstalling FriendlyStack Software"

##Stopping MySQL Server in order to move datadir
systemctl stop mysql

cp -r --preserve=mode,timestamps $SCRIPTPATH/basedir/usr $SCRIPTPATH/basedir/etc $SCRIPTPATH/basedir/home /

##Fix permissions for CUPS backend
chmod 0500 /usr/lib/cups/backend/pstack
chmod 0755 /usr/lib/cups/backend

##Set permissioms for www home
chown -R root:www-data /home/pstack/www
chmod -R 0640 /home/pstack/www
chmod -R ug+X /home/pstack/www

##Program the Arduino Uno compatible FSCU
if [[ $(ls -ls /dev/pcontrol) =~ ttyACM ]]
then
/usr/bin/avrdude -qq -C/home/pstack/bin/avrdude.conf -v -patmega328p -carduino -P/dev/pcontrol -b115200 -D -Uflash:w:/home/pstack/bin/FSCU.hex:i
fi

echo $SQLPASSWORD > /home/pstack/bin/mysql.pwd
chown root:FriendlyStack /home/pstack/bin/mysql.pwd
chmod 0440 /home/pstack/bin/mysql.pwd

mv /var/lib/mysql /home/pstack/mysql
mkdir /var/lib/mysql/mysql -p
systemctl restart apparmor
systemctl start mysql
systemctl restart smbd

printf "\nAdding Samba Account"
echo -ne "$PASSWORD\n$PASSWORD\n" | smbpasswd -a -s $USERNAME

printf "\nEnabling FriendlyStack Service"
update-rc.d pstack defaults


printf "\nEnabling FriendlyStackWatcher Service"
update-rc.d FriendlyStackWatcher defaults

printf "\nConfiguring Apache for SSL"
mkdir /etc/apache2/ssl

export SAN=IP:$CN,DNS:$CN1,DNS:$CN2

##Generate Key for root CA
openssl genrsa -aes256 -out /home/pstack/CA/ca-key.pem -passout pass:FriendlyStack 4096

##Generate certificate for root CA
openssl req -x509 -new -nodes -extensions v3_ca -key /home/pstack/CA/ca-key.pem -days 65536 -out /home/pstack/CA/ca-root.pem -sha512 -passin pass:FriendlyStack -subj "/C=CH/ST=BS/L=BS/O=FriendlyStack/OU=FriendlyStack/CN=FriendlyStack CA" -config /$SCRIPTPATH/openssl.cfg

##Generate key for intermediate CA
openssl genrsa -out /home/pstack/CA/intermediate-ca-key.pem -passout pass:FriendlyStackIntermediate 4096

##Generate CSR for intermediate CA
openssl req -new -extensions v3_req -key /home/pstack/CA/intermediate-ca-key.pem -out /home/pstack/CA/intermediate.csr -sha512 -passin pass:FriendlyStackIntermediate -subj "/C=CH/ST=BS/L=BS/O=FriendlyStack/OU=FriendlyStack/CN=FriendlyStack Intermediate CA" -config /$SCRIPTPATH/openssl.cfg
##Generate and sign certificate for Intermediate CA
openssl x509 -req -extfile /$SCRIPTPATH/openssl.cfg -extensions v3_ca -in /home/pstack/CA/intermediate.csr -CA /home/pstack/CA/ca-root.pem -CAkey /home/pstack/CA/ca-key.pem -CAcreateserial -out /home/pstack/CA/intermediate.crt -days 365 -sha512 -passin pass:FriendlyStack

##Generate key for Apache Server
openssl genrsa -out /etc/apache2/ssl/apache.key 4096

##Generate CSR for Apache Server
openssl req -new -extensions v3_req -key /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.csr -sha512 -passin pass:FriendlyStack -subj "/C=CH/ST=BS/L=BS/O=FriendlyStack/OU=FriendlyStack/CN=$CN" -config /$SCRIPTPATH/openssl.cfg

##Generate and sign certificate for Apache Server
openssl x509 -req -extfile /$SCRIPTPATH/openssl.cfg -extensions v3_req -in /etc/apache2/ssl/apache.csr -CA /home/pstack/CA/intermediate.crt -CAkey /home/pstack/CA/intermediate-ca-key.pem -CAcreateserial -out /etc/apache2/ssl/apache.crt -days 365 -sha512 -passin pass:FriendlyStackIntermediate

rm /etc/apache2/ssl/apache.csr
cp /home/pstack/CA/ca-root.pem /home/pstack/www/FriendlyStack.crt
chmod 0444 /home/pstack/www/FriendlyStack.crt

cat /etc/apache2/ssl/apache.crt /home/pstack/CA/intermediate.crt > /etc/apache2/ssl/web.crt
a2enmod ssl
a2enmod rewrite
a2enmod dav
a2enmod dav_fs
systemctl restart apache2
a2ensite default-ssl.conf
systemctl restart apache2
a2enmod auth_digest
systemctl restart apache2



printf "\nPreparing Network Stuff\n"
touch /etc/network/interfaces.d/wireless
chown root:www-data /etc/network/interfaces.d/wireless
chmod 0664 /etc/network/interfaces.d/wireless
chown root:www-data /etc/network/interfaces
chmod 0664 /etc/network/interfaces

##Disable NTP Time Synchronization
#timedatectl set-ntp 0

##The following prewents rtl8821 based WLAN interfaces from disconnecting randomly
##echo "options rtl8821ae swenc=1 ips=0 fwlps=0" | sudo tee /etc/modprobe.d/rtl8821ae.conf
##This is another possible solution for the same problem, but has not been tested
echo "options rtl8821ae  debug=0 disable_watchdog=N fwlps=N swlps=Y swenc=Y ips=N msi=0" | sudo tee /etc/modprobe.d/rtl8821ae.conf

##The following prewents Intel AC 7265 based WLAN interfaces from disconnecting randomly
echo "options iwlwifi 11n_disable=8" | sudo tee -a /etc/modprobe.d/iwlwifi.conf

##The following prewents Intel based WLAN interfaces to go into sleep and disconnecting
#tee /etc/modprobe.d/iwlmvm.conf <<< "options iwlmvm power_scheme=1"


printf "\nExtracting Printer Drivers\n"
/usr/lib/cups/daemon/cups-driverd cat drv:///sample.drv/laserjet.ppd > /usr/share/cups/model/laserjet.ppd

/usr/lib/cups/daemon/cups-driverd cat drv:///sample.drv/generic.ppd > /usr/share/cups/model/generic.ppd

##Required for self installing image
update-rc.d -f FriendlyStackInstaller remove

dpkg -i --force-all $SCRIPTPATH/scanner-drivers/brscan4-0.4.6-1.amd64.deb

dpkg --install $SCRIPTPATH/scanner-drivers/imagescan_3.30.0-1epson4ubuntu16.04_amd64.deb $SCRIPTPATH/scanner-drivers/imagescan-plugin-gt-s650_1.0.0-1epson4ubuntu16.04_amd64.deb

dpkg --install $SCRIPTPATH/scanner-drivers/pfufs-ubuntu14.04_2.1.0_amd64.deb


##Create encrypred file device
#export SIZE=$(((`df --output=avail / | sed -e '1d'` / 10)*8))k
export SIZE=$(((`df --output=avail / | sed -e '1d'`)-5242880))k

##Stop services relevant for FriendlyStack
systemctl stop smbd
systemctl stop mysql
systemctl stop pstack
systemctl stop FriendlyStackWatcher

fallocate -l $SIZE /home/pstack.crypt
echo -n "$PASSPHRASE" | cryptsetup luksFormat /home/pstack.crypt -
echo -n "$PASSPHRASE" | cryptsetup luksOpen /home/pstack.crypt pstack -
mkfs.ext4 -j /dev/mapper/pstack
mount /dev/mapper/pstack /mnt
mv /home/pstack/* /mnt/
umount /mnt
cryptsetup luksClose pstack

mkdir /home/pstack/mysql
chown -R mysql:mysql /home/pstack/mysql
/usr/sbin/mysqld --initialize --user=mysql
#tar -xPf /$SCRIPTPATH/FriendlyStackSecure.tar.gz
cp -r --preserve=mode,timestamps $SCRIPTPATH/basedir/home-secure/* /home/

##Set permissioms for www home
chown -R root:www-data /home/pstack/www
chmod -R 2640 /home/pstack/www
chmod -R ug+X /home/pstack/www


##Disable automatic startup of Samba and CUPS to avoid files being placed (by printing) in dummy area before system unlock
update-rc.d -f smbd remove
update-rc.d -f nmbd remove
update-rc.d -f cups remove


##For added security uncomment these
#update-rc.d ssh disable
#chsh -s /usr/sbin/nologin friendly

##Create user for usbmuxd
useradd -r -G plugdev -d /var/lib/usbmux -s /sbin/nologin usbmux -c "usbmux daemon"

sync
sync
reboot -f

