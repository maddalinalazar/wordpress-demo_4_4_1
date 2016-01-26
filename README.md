# General setup steps 
If things get unclear you can refer to the AWS documentation, which is available at: http://docs.aws.amazon.com/AWSEC2/latest/UserGuide/get-set-up-for-amazon-ec2.html 

1. Login into the AWS console (http://console.aws.amazon.com) using the e-mail account you used to sign-up and your password.
2. Create a group called **administrator** and assing the 'AdministratorAccess' policies.
3. Create a user called **your_user_name**, make sure you clear the checkbox close to *Generate an access key for each user*. Assign the user to the group.
4. Select the **your_user_name** user and navigate into the  the *Security Credentials* tab and under *Sign-in credentials* choose *Manage Password* and then *Assign custom password*. Insert a password into the fields as requested, be mindfull that you have to use this password later on so please write it down or use something easy to remember.
5. Log out of this account and let's connect to the console using the user that we created above (use https://your_aws_account_id.signin.aws.amazon.com/console/ and replace your_aws_account_id with your own AWS account - no hyphens).
6. Select the appropriate region for the demo if possible (I chose eu-west - Ireland), if it's not available now try selecting one before starting up your instances. By selecting a region you're instructing the system to pick a computer from the cloud in that particular place of the world.
7. Go to the EC2 console home page and click on *Key Pairs* - under the Network and Security category. Press *Create Key Pair* and let's give it the name of *your-user-name-awsdemo-key-your-selected-region* and let's save it to the disk.(Please replace your_user_name with the name of the user created at step 4 and your-selected-region with the region you selected at step 6 - in my case it was eu-west).
8. If you own a Mac computer please locate the 'Terminal' application and click to open. We want to change the rights to the key-pair that was created at step 7 and in order to do so we have to first locate it in your computer. I used the 'cd' command to move from one directory to another (http://www.computerhope.com/unix/ucd.htm) and 'ls' to display the contents of a directory (http://www.computerhope.com/unix/uls.htm) 
8. Once you are able to locate the key-pair file use the terminal to run the following command : *chmod 400 *your-user-name-awsdemo-key-your-selected-region.pem* (it might not be necessary if you're running on Windows, but if you are have problems ssh-ing to the EC2 staging instance consider doing this step on Windows as well).
9. Let's create the security groups for our application:  
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
12. Log on to the staging box and let's run the install commands below - the Set-up for the DEVO stack category.
13. Let's go into OpsWorks and create our stack, layer and application.
14. Before starting we should go to our cookbooks and update the wpconfig/files/default/wp-config.php file. Cahnge WP_HOME and WP_SITEURL with the DNS name of the Load Balancer and DB_NAME, DB_USER, DB_PASSWORD, DB_HOST with the corresponding details from step 11. Don't forget to commit your changes!
15. For our stack we are going to create a 'Chef 11 stack', use Ubuntu as our OS for all of our instances, custom cookbooks (for me it's https://github.com/maddalinalazar/wordpress-demo_4_4_1_cookbooks).
16. For our layer select the AWS Tutorial EC2 Production security group and use the load balancer created at step 10. Enable public IP addresses for the layer (Network tab) and add your custom cookbooks (wplogin::default, wpconfig::default) to the deployment recipes.
17. Go ahead and create an application. This should point to the repository that containts the wordpress source files (for me it's https://github.com/maddalinalazar/wordpress-demo_4_4_1.git). After the set-up is done add instances - one for each availability zones.
18. Now that everything is ready click on 'Start all instances!'. When all of the machines are online go ahead an click on the link next to 'Using ELB' and on the next page click on the link next to 'DNS Name'. Now your website should be available!


# Set-up for the DEVO stack by running the following commands (use Terminal on MAC and Putty for Windows):
<!--connect to the staging EC2 instance -->
If you own a MAC type this command into the Terminal window: 
ssh -i *your-user-name-awsdemo-key-your-selected-region* ubuntu@ec2_staging_instance_name, where you should replace *your-user-name-awsdemo-key-your-selected-region* with the name of the key-pair create at step 7 and *ec2_staging_instance_name* with the Public DNS value of your instance.  
To get the Public DNS value of your instance, go to the EC2 console page - AWS console, click on 'Services' (top right - on the black banner menu) and click on 'EC2'. Click on 'EC2 dashboard' (right side of the screen) and then on the 'Running Instances' link and a table of all running instances should appear. Select your EC2 staging instance (the instance we created at step 9) and now a series of details should appear in the bottom of the page. Look for the 'Public DNS' field and once you have located it the value next to it is the value that you are interested in (ec2-...-compute.amazonaws.com). Copy the text and paste it into the command above.  
For Windows follow the instructions from: http://docs.aws.amazon.com/AWSEC2/latest/UserGuide/putty.html 
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
