CloudFlareDDNS
==============

Create your own DDNS service with CloudFlare.

The updateCloudFlare.php will allow you to update a DNS record in your CloudFlare account using the CloudFlare API and your API key. 

Update the file with the appropriate details and host online. 

Call the file with a cron job every 5 minutes to regularly update your DDNS entry. 

*/5 * * * * /home/user/updateCloudFlare.sh
