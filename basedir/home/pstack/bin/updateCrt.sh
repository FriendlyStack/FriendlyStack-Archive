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


CN=`hostname -I | xargs`
CN1=`hostname | xargs`
CN2=`hostname | xargs`.local
export SAN=IP:$CN,DNS:$CN1,DNS:$CN2

#Generate CSR for Apache Server
openssl req -new -extensions v3_req -key /etc/apache2/ssl/apache.key -out /etc/apache2/ssl/apache.csr -sha512 -passin pass:FriendlyStack -subj "/C=CH/ST=BS/L=BS/O=FriendlyStack/OU=FriendlyStack/CN=$CN" -config /home/FriendlyStack/openssl.cfg

#Generate and sign certificate for Apache Server
openssl x509 -req -extfile /home/FriendlyStack/openssl.cfg -extensions v3_req -in /etc/apache2/ssl/apache.csr -CA /home/pstack/CA/intermediate.crt -CAkey /home/pstack/CA/intermediate-ca-key.pem -CAcreateserial -out /etc/apache2/ssl/apache.crt -days 365 -sha512 -passin pass:FriendlyStackIntermediate

rm /etc/apache2/ssl/apache.csr
cp /home/pstack/CA/ca-root.pem /home/pstack/www/FriendlyStack.crt
chmod 0444 /home/pstack/www/FriendlyStack.crt

cat /etc/apache2/ssl/apache.crt /home/pstack/CA/intermediate.crt > /etc/apache2/ssl/web.crt

service apache2 reload
