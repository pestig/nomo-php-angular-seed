[nomoEFW](http://nomo.hu/) - nomo Enterprise Framework 
======================================================

About
--------------------------------------

###Linkek

 - Demo
 - Source (GIT): [https://bitbucket.org/nomosolutions/nomoefwsample](https://bitbucket.org/nomosolutions/php-angular-seed)
 - Issue tracking: [http://jira.nomo.hu](http://jira.nomo.hu)

Install
--------------------------------------

###Fejlesztői környezet létrhozása

 - [Vagrant](https://www.vagrantup.com/downloads.html) letöltése és telepítése
 - [Oracle VirtualBox](https://www.virtualbox.org/wiki/Downloads) letöltése és telepítése
 
###Alkalmazás futtatása

Forráskód letöltése és VirtualBox indtása Vagrant-el:
```bash
/path/to/nomoefw/sample$ git clone git@bitbucket.org:nomosolutions/php-angular-seed.git
/path/to/nomoefw/sample$ cd php-angular-seed
/path/to/nomoefw/sample/php-angular-seed$ vagrant up
```
Ezután az alkalmazás a következő címen elérhető: [http://localhost:8080/](http://localhost:8080/)

PhpMyAdmin felület: [http://localhost:8080/phpmyadmin](http://localhost:8080/phpmyadmin)


Fejelsztői dokumentáció

--------------------------------------
###Package manager ([npm](https://www.npmjs.com/), [bower](http://bower.io/)), build tool ([grunt](http://gruntjs.com/)) telepítése

 - [git](http://git-scm.com/git) installáljuk a gitet, és telepítéskor engedélyezzük, hogy a git.exe hozzá adja magát a windows PATH környezeti változóhoz (ha már telepítve van és telepítéskor nem engedélyezteük a git hozzáadását a PATH-hoz, akkor [így](http://blog.countableset.ch/2012/06/07/adding-git-to-windows-7-path/) tudjuk utólag hozzáadni)
 - [nodejs](http://nodejs.org/) installáljuk a nodeot (http://stackoverflow.com/questions/25093276/node-js-windows-error-enoent-stat-c-users-rt-appdata-roaming-npm)
 
Nyissuk meg a "node.js command promot"-ot és futassuk az **npm install -g bower** parancsot, ez telepíti a **bower** package managert.

Eztuán futassuk az **npm install -g grunt-cli** parancsot, ez telepíti a **grunt**-ot.

###További vagrant parancsok

VM suspend:
```bash
vagrant suspend
```

VM shutdown:
```bash
vagrant halt
```

VM törlése (időnként javasolt, hogy tudjuk, minden szükséges szkript benne van a vagrant.bootstrap.sh fileban):
```bash
vagrant destroy
```

### HTTP requestek kiszolgálása

A beérkező kérések egy része statikus fájlokra vonatkozik, a másik részének kiszolgálása keresztülhalad a php értelemezőn. Ezek a ".htaccess" fájlban vannak meghatároza. 

Jellemezően a következő mappák/fájlok kiszolgálása statikus tartalomként történik:

```
/admin/app/.*
/frontend/app/.*
/nomoEFW/app/.*
/media/.*
/robots.txt
/favicon.ico
```

A többi kérés a "/index.php" fájlon kersztül kerül kiszolgálásra.

![HTTP request pipeline](https://docs.google.com/drawings/d/1iD8CvKbQRhyS7hEC6KxmP1ZY8gQ4mHwKvb4aTIQFAwA/pub?w=480&amp;h=360)     


