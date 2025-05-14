<?php
session_start();

if (isset($_GET['index']) && isset($_SESSION['cart'][$_GET['index']])) {
    // Удаляем элемент из корзины
    unset($_SESSION['cart'][$_GET['index']]);

    // Переиндексируем массив
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// Перенаправляем обратно в корзину
header('Location: cart.php');
exit;
?>