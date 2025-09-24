<?php

try {
    $baglanti = new PDO("mysql:host=localhost;dbname=otopark", 'root','');
    $baglantiDurumu = "Bağlantı Başarılı";
    #echo "Bağlantı Başarılı";
} catch (Exception $e) {
    echo $e->getMessage();
}