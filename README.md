# AlgoWIFI - where Ads meets blockchain. 
![logo](/img/algowifiLogo.png)

**AlgoWIFI for Hot-Spot**

allows the owners of public and / or private WIFI Hot-Spots offering internet connections, the enhancement of accesses through an advertising circuit based on the **Algorand blockchain**. All views are certified with blockchain writing.



**AlgoWIFI for Web 3.0**

* allows owner of web-site to make revenues from visit.

**AlgoWIFI Counter**
* Certification of views.

**AlgoWIFI for Qr-Code**
* Dynamic qr-code with certification on blockchain.





**Requirement:**

* S.O. Linux All version, tested on Ubuntu 18.04+.
* Server LAMP, PHP Ver. 7.2+
* PHP mbstring required.
* Enable **CORS on  Apache**  https://enable-cors.org/server_apache.html (if use RestJQuery - deprecated)
* Algorand Node, algod, kmd. 
* Optional Algo Indexer or using API AlgoExplorer.
* PHP-sdk Algorand - FFSolutions, https://github.com/ffsolutions/php-algorand-sdk
* PHPMailer for recovery password.


# Hardware for Access Point:

* Mikrotik Router ALL type. ( wireless and wired )
* UBNT Unifi in testing.
* AP based on OpenWRT in testing.
* Application for Hot-Spot Management in testing.


# Changelog

- Ver. 1.0 stable
  - change name of logintest.php in algowifi.php
  - add **$mainReserveAddress** in algoconfig.php
  - add **$reserveFee** in algoconfig.php
  - add **$reserveFee** in routine on algowifi.php
  - add Atomic Transfer for **Reserve address** in algowifi.php
  - add **algowifiRest.php**
  - add **algowifiCounter.html**
  - add **GraphStat of tx** on dashboard.
  
  




