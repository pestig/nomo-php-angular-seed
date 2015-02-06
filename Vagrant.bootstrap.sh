if [ ! -f /var/log/vagrantsetup ];
then
    echo "Europe/Budapest" | sudo tee /etc/timezone & sudo dpkg-reconfigure --frontend noninteractive tzdata
    sudo debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password password p'
    sudo debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password_again password p'
    sudo apt-get update
    sudo apt-get -y install mysql-server-5.5 apache2 php5 phpmyadmin curl git
    curl -sL https://deb.nodesource.com/setup | sudo bash -
    sudo apt-get -y install nodejs

    echo "CREATE USER 'nomoefw_dbuser'@'localhost' IDENTIFIED BY 'p'" | mysql -uroot -pp
		echo "CREATE DATABASE dbdev" | mysql -uroot -pp
		echo "GRANT ALL ON dbdev.* TO 'uvekhu01_vir'@'localhost'" | mysql -uroot -pp
    echo "flush privileges" | mysql -uroot -pp
    
    if [ -f /vagrant/dbscripts/init.sql ];
    then
        mysql -uroot -pp dbdev < /vagrant/dbscripts/init.sql
    fi
    
    sudo touch /var/log/vagrantsetup
fi

if [ ! -h /var/www ];
then 
    sudo rm -rf /var/www
    sudo ln -s /vagrant /var/www

    sudo a2enmod rewrite

    sudo sed -i '/AllowOverride None/c AllowOverride All' /etc/apache2/sites-available/default
    sudo sed -i 's/html_errors = Off/html_errors = On/' /etc/php5/apache2/php.ini 
    sudo sed -i 's/display_errors = Off/display_errors = On/' /etc/php5/apache2/php.ini
    echo 'Include /etc/phpmyadmin/apache.conf' | sudo tee --append /etc/apache2/apache2.conf

    if [ -f /var/www/config.sample.php ];
    then
        cp /var/www/config.sample.php /var/www/config.php
    fi

    npm install -g bower --no-bin-links
    sudo npm install -g grunt-cli

    cd /var/www;
    bower install
    npm install /var/www

    sudo service apache2 restart
fi
