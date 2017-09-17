#!/bin/bash

########## Edit the following lines ##########
IPv4=true
IPv6=false
Logfile="/var/log/ddns.log"
URL="https://www.example.com/updateCloudFlare.php?auth=***Insert Your Key Here***"
##############################################

date >> $Logfile
if $IPv4; then
  wget -4qO- $URL >> $Logfile
fi
if $IPv6; then
  wget -6qO- $URL >> $Logfile
fi
