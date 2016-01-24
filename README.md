# Set-up for the DEVO stack
/*use the root user*/
sudo su
/*re-synchronize the package index files from their sources*/
apt-get update
/*fetch new versions of packages existing on the machine*/
apt-get upgrade -y
/*install the newest versions of all packages currently installed on the system*/
apt-get dist-upgrade -y
apt-get autoremove -y
/*install missing packages*/
apt-get install apache2 php5 php5-cli php5-fpm php5-gd libssh2-php libapache2-mod-php5 php5-mcrypt php5-mysql git unzip zip postfix php5-curl mailutils php5-json -y

a2enmod rewrite headers
php5enmod mcrypt

/*Open the files below with the given command, delete all of the contents of file and replace it with the text found below.*/
nano /etc/apache2/sites-enabled/000-default.conf

<VirtualHost *:80>
        #ServerName example.com
        #ServerAlias www.example.com
        DocumentRoot /var/www/staging

        <Directory /var/www/staging>
                Options -Indexes
                AllowOverride All
                Order allow,deny
                Allow from all
        </Directory>
</VirtualHost>

/*Navigate to /html, remove all files inside and clean the directory. THen up in the hierarchy create a new directory /staging*/
cd /var/www/html
rm -rf *
cd ../
mkdir staging
cd staging
/*clone the code from the reportsitoty*/
git clone https://github.com/andrewpuch/wordpress_4_1_1.git .
/* update folder access */
chmod -R 744 .
/**/
chown -R www-data:www-data .
/*restart apache*/
service apache2 restart


==Console ==
