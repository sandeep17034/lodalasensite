IP Checker (index.html + log.php)

Files
-----
index.html  - visitor-facing page
log.php     - receives a POST, detects the connecting IP, and appends a JSON line
             to a text log outside the public web root.

Install
-------
1. Upload index.html and log.php to your Nginx document root, for example:
     /var/www/html/

2. Create the private log directory and grant PHP-FPM access:
     sudo mkdir -p /var/www/private-logs
     sudo chown www-data:www-data /var/www/private-logs
     sudo chmod 750 /var/www/private-logs

   If your PHP-FPM pool runs as a different user, replace www-data accordingly.

3. Open your HTTPS website. Each page visit sends a POST request to log.php.
   The log will be written to:
     /var/www/private-logs/visitors.txt

View log
--------
sudo tail -f /var/www/private-logs/visitors.txt

Notes
-----
- PHP must be enabled in Nginx and log.php must be processed by PHP-FPM.
- The script records REMOTE_ADDR (the connecting peer). If you use a reverse
  proxy/CDN, this may be the proxy address unless you configure trusted-proxy
  handling on your server. Do not blindly trust X-Forwarded-For from visitors.
- The page records IP address, timestamp, browser user-agent and language.
  Inform visitors in an appropriate privacy notice and retain logs only as needed.
