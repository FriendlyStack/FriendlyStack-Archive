#!/usr/bin/perl


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


if ((-e "/home/pstack/Inbox/Pictures/$ENV{ARGUMENT}") && !($ENV{ARGUMENT}  =~ /\.(jpg|jpeg|r[a]?w2|avi|mp4|mts|mov)$/i))
	{
		unlink("/home/pstack/Inbox/Pictures/$ENV{ARGUMENT}");
	}
