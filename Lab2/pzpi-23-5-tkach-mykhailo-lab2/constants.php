<?php
/**
 * Константи для програми "Продовольчий магазин 'Весна'"
 */

// Опції головного меню
define('MENU_EXIT', '0');
define('MENU_SELECT_PRODUCTS', '1');
define('MENU_GET_BILL', '2');
define('MENU_SETUP_PROFILE', '3');

// Інші константи
define('MIN_AGE', 7);
define('MAX_AGE', 150);
define('MAX_PRODUCT_QUANTITY', 99);

// Заголовок програми
define('STORE_TITLE', "################################\n# ПРОДОВОЛЬЧИЙ МАГАЗИН \"ВЕСНА\" #\n################################");

// Повідомлення про помилки
define('ERROR_INVALID_COMMAND', 'ПОМИЛКА! Введіть правильну команду');
define('ERROR_INVALID_PRODUCT', 'ПОМИЛКА! ВКАЗАНО НЕПРАВИЛЬНИЙ НОМЕР ТОВАРУ');
define('ERROR_INVALID_QUANTITY', 'ПОМИЛКА! Введіть кількість від 0 до 99');
define('ERROR_EMPTY_NAME', 'ПОМИЛКА! Імʼя повинно містити хоча б одну літеру.');
define('ERROR_INVALID_AGE', 'ПОМИЛКА! Вік повинен бути від 7 до 150 років.');
define('ERROR_EMPTY_CART', 'КОШИК ПОРОЖНІЙ. Спочатку виберіть товари.');

// Інші тексти
define('CART_EMPTY', 'КОШИК ПОРОЖНІЙ');
define('REMOVING_FROM_CART', 'ВИДАЛЯЮ З КОШИКА');
define('GOODBYE_MESSAGE', 'Дякуємо за покупки! До побачення!');
define('PROFILE_UPDATED', 'Профіль успішно оновлено!');
?>