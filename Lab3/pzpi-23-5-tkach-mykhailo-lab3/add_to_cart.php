<?php
session_start();

// Проверка данных формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Инициализация корзины, если она еще не существует
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Загрузка данных о товарах из JSON файла
    $productsData = file_get_contents('products.json');
    $productsJson = json_decode($productsData, true);

    // Создаем плоский массив всех товаров для легкого поиска
    $allProducts = [];
    foreach ($productsJson['products'] as $category) {
        foreach ($category['items'] as $item) {
            $allProducts[$item['id']] = [
                'name' => $item['name'],
                'price' => $item['price'],
                'icon' => $item['icon']
            ];
        }
    }

    // Обработка каждого товара
    foreach ($_POST as $itemId => $count) {
        if ($count > 0 && isset($allProducts[$itemId])) {
            $count = (int)$count;
            $productInfo = $allProducts[$itemId];

            // Проверяем, есть ли уже такой товар в корзине
            $found = false;
            foreach ($_SESSION['cart'] as $key => $cartItem) {
                if ($cartItem['id'] === $itemId) {
                    // Если товар уже есть, увеличиваем количество
                    $_SESSION['cart'][$key]['count'] += $count;
                    $found = true;
                    break;
                }
            }

            // Если товар не найден, добавляем его
            if (!$found) {
                $_SESSION['cart'][] = [
                    'id' => $itemId,
                    'name' => $productInfo['name'],
                    'price' => $productInfo['price'],
                    'icon' => $productInfo['icon'],
                    'count' => $count
                ];
            }
        }
    }

    // Перенаправление на страницу корзины
    header('Location: cart.php');
    exit;
} else {
    // Если форма не была отправлена методом POST
    header('Location: products.php');
    exit;
}
?>