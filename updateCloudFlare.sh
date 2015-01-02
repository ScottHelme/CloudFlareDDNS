#!/bin/bash

########## Edit the following lines ##########
IPv4=true
IPv6=false
URL="https://www.example.com/updateCloudFlare.php?auth=***Insert Your Key Here***"
##############################################

if $IPv4; then
  wget -4qO- $URL &> /dev/null
fi
if $IPv6; then
  wget -6qO- $URL &> /dev/null
fi
