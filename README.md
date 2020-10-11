## Going Paperless for the Rest of Us! 

**Sorting, filing and searching paper is boring work…**

I’ve created FriendlyStack because I couldn’t find a commercial solution that would take care of all the boring work, without adding more boring work. For me, developing FriendlyStack was an opportunity to turn boring work into exciting work. For you FriendlyStack is the opportunity to eliminate boring work and focus on whatever makes you happy!

Essentially FriendlyStack will scan your physical documents and make them available through a user friendly web front end. But there's much more to FriendlyStack:

* Operation through a user friendly web frontend or an optional control unit (affordable microcontroler with touch screen in an attractive 3d printed case)
* Scan documents (FriendlyStack can either control your document scanner or offer a "hot folder" for network scanners)
* Preprocess documents (page rotation, blank page removal, deskewing)
* OCR documents 
* Index documents (full text search)
* Create document previews
* Use smart cover sheets to: separate documents, route documents, scan multiple single page documents
* Use virtual printers to print from any application directly to pdf files in a specific folder
* Encryption of stored files
* Easy backup and restore functionality
* Handles electronic files (office files, photos and videos) as well. Just save or copy them to the file share.

Oh, wait, there is more! Managing my photos and videos used to be almost as annoying as organizing paper. So FriendlyStack takes care of these as well. Just connect your digital camera or smart phone to FriendlyStack and it will process your photos and videos automatically:

* Transfer your photos and videos to your FriendlyStack
* Store photos and videos chronologically by year and month
* Eliminate duplicates
* Convert videos to low resolution mp4 format (original format and resolution is retained and available as well)
* Create previews for photos and videos

Finally you can find your stuff by combining keywords just like in your favorite search engine. There are however a few extras:

* Keywords can be located within a document or be part of the filename or path.
* Preceding a keyword with a "-" will display results *not* containing the specific word.
* FriendlyStack will interpret dates: "November 18 2018 invoice" will retrieve all files dated November 18th 2018 containing the word "invoice" in the document, file- or pathname. Dated here refers either a date within the document or if none is found, the date the file was saved.
* FriendlyStack also understands file types: "photo December 25" will produce the Christmas pictures of any year saved in your system (you might want to limit this by adding a year)

## What is FriendlyStack

FriendlyStack is a Network Appliance for managing physical and electronic documents as well as photos and videos. Like other appliances (think of a photocopier or a toaster) FriendlyStack is designed to fulfill its purpose as simply and efficiently as possible. FriendlyStack was designed with lazy people in mind. Managing your stuff with FriendlyStack is effortless and doesn’t require a PhD in Computer Wizardry.

Technically FriendlyStack is a Linux server working as an appliance and running Ubuntu Server 16.04 LTS (at some point I will migrate to a newer LTS release, but this is currently no priority). It acts as file-, print- and web-server running a bunch of software doing the FriendlyStack magic. FriendlyStack is mostly written in Perl and PHP with some bits and pieces implemented in C and shell scrips.

Installation is simple by using the provided install script. I'm also working on a fully automated "bare metal" installation image.

## What do you need to use FriendlyStack

* A computer that will run the FriendlyStack software as an appliance (think of a NAS)
* Optional: a document scanner with automatic sheet feeder
* Optional: a FriendlyStack Control Unit (FSCU) to control your appliance.

### Minimal Hardware Requirements for the Computer:
* CPU: Intel compatible, 64 Bit, Support for SSE instructions, at least two cores (while all of this sounds very technical, most of today's and yesterday's CPUs can check all boxes).
* RAM: One Gigabyte per CPU core but at least 4 Gigabytes.
* Hard Disk: 300 Gigabyte or larger.
* Ethernet Interface (remember: FriendlyStack is a network appliance)
* At least three USB 2.0 ports (USB 3.0 or never will speed up backup and restore)

### Requirements for the Document Scanner:
* Automatic Document Feeder
* Duplex Scanning
* USB Connection
* Linux Drivers (support for Ubuntu 16.04)
* Optionally, if no Linux drivers are available: network support, capable of saving pdf files to a windows/samba/CIFS share

## License

    The code in this repository is licensed by the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version;
    you may not use this file except in compliance with the License.
    
    FriendlyStack, a system for managing physical and electronic documents as well as photos and videos.
    Copyright (C) 2018  Dimitrios F. Kallivroussis, Friendly River LLC
    
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.
    
    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

**NOTE**: This software depends on other packages that may be licensed under different open source licenses.

FriendlyStack uses [Leptonica library](http://leptonica.com/) which essentially
uses a [BSD 2-clause license](http://leptonica.com/about-the-license.html).

FriendlyStack uses [Tesseract OCR](https://github.com/tesseract-ocr/tesseract) which
uses the [Apache License 2.0](https://github.com/tesseract-ocr/tesseract/blob/master/LICENSE)

FriendlyStack uses [Compact Language Detector 2](https://github.com/CLD2Owners/cld2) which
uses the [Apache License 2.0](https://github.com/CLD2Owners/cld2/blob/master/LICENSE)

FriendlyStack uses [libimobiledevice](https://www.libimobiledevice.org/) which
uses the [GNU Lesser General Public License v2.1](https://github.com/libimobiledevice/libimobiledevice/blob/master/COPYING)

FriendlyStack uses [usbmuxd](https://github.com/libimobiledevice/usbmuxd) which
uses the [GNU General Public License version 3](https://github.com/libimobiledevice/usbmuxd/blob/master/COPYING.GPLv3)

FriendlyStack uses [libusbmuxd](https://github.com/libimobiledevice/libusbmuxd) which
uses the [GNU Lesser General Public License v2.1](https://github.com/libimobiledevice/libusbmuxd/blob/master/COPYING)

FriendlyStack uses [ifuse](https://github.com/libimobiledevice/ifuse) which
uses the [GNU Lesser General Public License v2.1](https://github.com/libimobiledevice/ifuse/blob/master/COPYING)

FriendlyStack uses [gphoto2](http://www.gphoto.org/) which
uses the [GNU General Public License v2.0](https://github.com/gphoto/gphoto2/blob/master/COPYING)

FriendlyStack uses [libgphoto2](https://github.com/gphoto/libgphoto2) which
uses the [GNU Lesser General Public License v2.1](https://github.com/gphoto/libgphoto2/blob/master/COPYING)

FriendlyStack uses [Font Awesome](https://fontawesome.com) which
uses the [CC BY 4.0 License](https://creativecommons.org/licenses/by/4.0/) for Icons
the [SIL OFL 1.1 License](https://scripts.sil.org/OFL) for Fonts
and the [MIT License](https://opensource.org/licenses/MIT) for Code

FriendlyStack uses [QR-code generator](https://prgm.spipu.net/view/27) which
uses the [GNU Lesser General Public License](https://www.gnu.org/licenses/lgpl.txt)

FriendlyStack uses [Apache PDFBox®](https://pdfbox.apache.org/) which
uses the [Apache License, Version 2.0](https://www.apache.org/licenses/LICENSE-2.0)

FriendlyStack uses [wsdd](https://github.com/christgau/wsdd) which
uses the [MIT License](https://github.com/christgau/wsdd/blob/master/LICENCE)

FriendlyStack uses [GeoNames](https://geonames.org) which
uses the [Creative Commons Attribution 4.0 License](https://creativecommons.org/licenses/by/4.0/)
