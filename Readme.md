deletePictures
===

*Löscht Bilder in den Unterverzeichnissen von "out/pictures/master/product/",  die in der Tabelle oxarticles nicht mehr
hinterlegt sind*


Installation:
-------------
Datei in das Hauptverzeichnis des Shops kopieren

Bedienung
--------
1) Datenbanktabelle d3lostpictures per Button erstellen
2) Über Button Verzeichnisse einlesen. Dieser Vorgang kann länger dauern, und mehrmal ausgeführt werden
3) Löschen der Bilder pro Slot. Die Anzahl der zu löschenden Bilder ist pro Durchgang beschränkt, daher muss der Vorgang mehrmals ausgeführt werden. 


Deinstallation:
-------------
Entfernen der Datenbanktabelle d3lostpictures direkt in der Datenbank oder über den Button 'Tabelle d3lostpictures löschen'.
Das Script aus dem Shoproot per FTP löschen oder über den Button 'Löschen'. 


English
-------

delete old pictures from Subfolders in "out/pictures/master/product/"

Installation:
-------------
Copy Script into the Shoproot

Bedienung
--------
1) Create Databasetable via Button 'Tabelle d3lostpictures erstellen'.
2) Run check for pictures with Button 'Bilder suchen / Verzeichnisse einlesen'. This action can take some time, and can performe some times till last picture slot.
3) Delete pictures separately for each slot. There is a limit to delete files. So it's necessary to perform this action some times.  

Deinstallation:
-------------
Remove Script from Shoproot or delete the script via itself (Button 'Löschen') 


Version:
------------
Oxid eShop 4.9 - 4.10

Version 0.1 
 