[nomoEFW](http://nomo.hu/) - nomo Enterprise Framework 
======================================================


Telepítés
--------------------------------------

###Integrálás meglévő projektbe

 - Futtatható állomány letöltése: [https://github.com/pestig/nomo-php-angular-seed/archive/master.zip](https://github.com/pestig/nomo-php-angular-seed/archive/master.zip) 
 - Futtasuk le a **nomo-php-angular-seed-masterdbscripts/init.sql** scriptet az adatbázisunkon, hogy létrehozza a rendszer táblákat
 - Másoljuk a **nomo-php-angular-seed-master/nomoEFW** mappát, a **nomo-php-angular-seed-master/api.php** és a **nomo-php-angular-seed-master/example.html** fájlt a weboldalunk/webalkalmazásunk gyökér könyvtárába.
 - Az **nomo-php-angular-seed-master/api.php** fájlban állítsuk be az adatbázis kapcsolatot
 
Példa a használatra

 - Nyissuk meg böngészőben az example.html fájlt és élvezzük az eredményt :)
 - Bármelyik adatbázis táblánkat megjeelentihetjük ez exmaple.html ben átírva a következő kifejezést: *nomo-table-class="'Peldatabla'"*


###Új projekt indítása esetén

####Fejlesztői környezet létrhozása

 - [Vagrant](https://www.vagrantup.com/downloads.html) letöltése és telepítése
 - [Oracle VirtualBox](https://www.virtualbox.org/wiki/Downloads) letöltése és telepítése
 
####Alkalmazás futtatása

Futtatható állomány letöltése: [https://github.com/pestig/nomo-php-angular-seed/archive/master.zip](https://github.com/pestig/nomo-php-angular-seed/archive/master.zip) 

Kitömörítés után a *nomo-php-angular-seed-master*  mappában (ahol a "Vagrant" fájl van) futtasuk "vagrant up" parancsot.

Ezután az alkalmazás a következő címen elérhető el: 

 - forntend: [http://localhost:8080/](http://localhost:8080/)
 - admin: [http://localhost:8080/admin](http://localhost:8080/admin) (user/pwd: info@nomo.hu/p)
 - PhpMyAdmin felület: [http://localhost:8080/phpmyadmin](http://localhost:8080/phpmyadmin) (user/pwd: root/p)
