<?php

$pdo = new PDO('mysql:host=localhost;dbname=projet_defoulement', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',));

$username = $_POST['user_id'];
$slug = $_POST['url_slug'];

$enregistrement_produit = $pdo->prepare("INSERT INTO favorite (user_id, url_post) VALUES ('$username', '$slug')");
$enregistrement_produit->bindParam(':user_id', $username, PDO::PARAM_STR);
$enregistrement_produit->bindParam(':url_post', $slug, PDO::PARAM_STR);
$enregistrement_produit->execute();
