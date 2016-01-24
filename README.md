# General setup steps
1. Select the appropriate region for the demo (eu-west - Ireland)
2. Create a group called **administrator** and assing the 'AdministratorAccess' policies.
3. Create a user called **your_user_name**, make sure you clear the checkbox close to *Generate an access key for each user*. Assign the user to the group.
4. Select the **your_user_name** user and navigate into the  the *Security Credentials* tab and under *Sign-in credentials* choose *Manage Password* and then *Assign custom password*.
5. Log out of this account and let's connect to the console using the user that we created above (use https://your_aws_account_id.signin.aws.amazon.com/console/ and replace your_aws_account_id with your own AWS account - no hyphens).
6. Go to the Ec2 console home page and click on *Key Pairs* - under the Network and Security category. Press *Create Key Pair* and let's give it the name of *your_user_name-awsdemo-key-eu-west* and let's save it to the disk.
7. Use the terminal to run the following command for the key-pair that you created: *chmod 400 your_user_name-awsdemo-key-eu-west.pem*
8. Let's create the security groups for our application:  
Security group name          | Type of connections  
---------------------------- | -------------  
AWS Tutorial EC2 Staging     | Inbound: HTTP, SSH  (My IP)  
AWS Tutorial EC2 Production  | Inbound: HTTP (source is ELB security group)  
AWS Tutorial ELB             | Inbound: HTTP (My IP), Outbound All Traffic (destination EC2 prod security group)  
AWS Tutorial RDS             | Type: mySQL, Port range: 3306, Sources: EC2 Staging and Prod security groups  

*Outbound for the rest is _All traffic_ from _Anywhere_*

9. Let's start our EC2 Staging instance. 
10. Let's create our Load Balancer (use the */* as a ping path and your ELB security group).
11. Let's create our RDS instance (don't forget to create a 'Subnet group' first).
12. Log on to the staging box and let's run the install commands below.
13. Let's go into OpsWorks and create our stack, layer and application.
14. Before starting we should go to our cookbooks and update the wpconfig/files/default/wp-config.php file. Cahnge WP_HOME and WP_SITEURL with the DNS name of the Load Balancer and DB_NAME, DB_USER, DB_PASSWORD, DB_HOST with the corresponding details from step 11. Don't forget to commit your changes!
15. For our stack we are going to create a 'Chef 11 stack', use Ubuntu as our OS for all of our instances, custom cookbooks (for me it's https://github.com/maddalinalazar/wordpress-demo_4_4_1_cookbooks).
16. For our layer select the AWS Tutorial EC2 Production security group and use the load balancer created at step 10. Enable public IP addresses for the layer (Network tab) and add your custom cookbooks (wplogin::default, wpconfig::default) to the deployment recipes.
17. Go ahead and create an application. This should point to the repository that containts the wordpress source files (for me it's https://github.com/maddalinalazar/wordpress-demo_4_4_1.git). After the set-up is done add instances - one for each availability zones.
18. Now that everything is ready click on 'Start all instances!'. When all of the machines are online go ahead an click on the link next to 'Using ELB' and on the next page click on the link next to 'DNS Name'. Now your website should be available!


# Set-up for the DEVO stack
<!-- use the root user --> 
sudo su  
<!--re-synchronize the package index files from their sources-->
apt-get update  
<!--fetch new versions of packages existing on the machine-->
apt-get upgrade -y  
<!--install the newest versions of all packages currently installed on the system-->
apt-get dist-upgrade -y  
apt-get autoremove -y  
<!--install missing packages-->
apt-get install apache2 php5 php5-cli php5-fpm php5-gd libssh2-php libapache2-mod-php5 php5-mcrypt php5-mysql git unzip zip postfix php5-curl mailutils php5-json -y  

a2enmod rewrite headers  
php5enmod mcrypt  

<!--Open the files below with the given command, delete all of the contents of file-->
nano /etc/apache2/sites-enabled/000-default.conf  
<!-- Replace the text in the file with the text below. -->
```xml
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
```
<!--Navigate to /html, remove all files inside and clean the directory.-->
cd /var/www/html  
rm -rf *  
cd ../  
mkdir staging  
cd staging  
<!--clone the code from the repository -->
git clone https://github.com/maddalinalazar/wordpress-demo_4_4_1.git .  
<!--update folder access-->
chmod -R 744 .   
chown -R www-data:www-data .  
<!--restart apache-->
service apache2 restart  
