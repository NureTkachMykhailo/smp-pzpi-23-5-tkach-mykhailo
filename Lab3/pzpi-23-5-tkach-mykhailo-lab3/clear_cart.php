<?php
session_start();

// Очищаем корзину
$_SESSION['cart'] = [];

// Перенаправляем обратно в корзину
header('Location: cart.php');
exit;
?>