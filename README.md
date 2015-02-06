[nomoEFW](http://nomo.hu/) - nomo Enterprise Framework 
======================================================


Telepítés
--------------------------------------

###Integrálás meglévő projektbe

 1. **Letöltés:** [https://github.com/pestig/nomo-php-angular-seed/archive/master.zip](https://github.com/pestig/nomo-php-angular-seed/archive/master.zip) 
 2. **Adatbázis frissítés:** futtasuk le a *nomo-php-angular-seed-master/dbscripts/init.sql* scriptet az adatbázisunkon, hogy létrehozza a rendszer táblákat (cronjob, file, peldatabla, session, user, _dbpatchlist)
 3. **nomo Fájlok másolása:** Másoljuk a következő mappát és fájlokat a weboldalunk gyökér (www route) mappájába:
	- *nomo-php-angular-seed-master/nomoEFW* (mappa)
	- *nomo-php-angular-seed-master/api.php* (fájl)
	- *nomo-php-angular-seed-master/example.html* (fájl)
 4. **adatbázis kapcsoalat beállítása:** Az *nomo-php-angular-seed-master/api.php* fájlban állítsuk be az adatbázis kapcsolatot
 
Példa a használatra: átírva a *nomo-table-class="'Peldatabla'"* kifejezést bármilyen adatbázis táblát, formot megjeleníthetünk (*nomo-php-angular-seed-master/example.html*)

```html
<!DOCTYPE html>
<html lang="en" ng-app="nomoEFW"><!-- nomo APP scope-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Frontend</title>

    <!-- nomo CSS -->
	<link href="/nomoEFW/app/dist/nomo.min.css?ver=<%VERSION%>" rel="stylesheet" type="text/css">
</head>
<body>
	<h1>Frontend</h1>
	<div>

        <div class="container-fluid">
            <div class="row">
                
                <!-- nomo table -->
                <div nomo-table="sampletable" nomo-table-class="'Peldatabla'" class="col-md-6"></div>
                
                <!-- nomo form -->
                <div ng-if="sampletable.activerow" nomo-form="samplefrom" nomo-form-class="sampletable.class" nomo-form-id="sampletable.activerow.rowid"  class="col-md-6"></div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div  class="col-md-12">
                    Selected rows: <strong>{{sampletable.selection}}</strong><br />
                    Active row: <strong>{{sampletable.activerow}}</strong><br />
                </div>
            </div>
        </div>
	</div>

    <!-- nomo JS -->
	<script src="/nomoEFW/app/dist/nomo.min.js?ver=<%VERSION%>" type="text/javascript"></script>
</body>
</html>
```


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
