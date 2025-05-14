<?php
/**
 * Продовольчий магазин "Весна"
 *
 * Програма для обробки замовлень у продовольчому магазині.
 * Дозволяє вибирати товари, отримувати підсумковий рахунок та налаштовувати профіль.
 */

// Підключення файлів з константами та даними
require_once 'constants.php';
require_once 'data.php';

// Ініціалізація змінних для зберігання стану
$cart = [];
$profile = [
    "name" => "",
    "age" => 0
];


/**
 * Очищення екрану консолі
 */
function clearScreen() {
    echo chr(27) . "[2J" . chr(27) . "[;H"; // ANSI escape code для очищення екрану
}


/**
 * Показати головне меню
 */
function showMainMenu() {
    global $mainMenuItems;

    echo "\n";
    echo STORE_TITLE . "\n";

    foreach ($mainMenuItems as $key => $menuItem) {
        echo "$key $menuItem\n";
    }

    echo "Введіть команду: ";
}


/**
 * Обробка вибору пункту головного меню
 */
function handleMainMenu() {
    global $cart;

    while (true) {
        showMainMenu();
        $command = trim(fgets(STDIN));

        switch ($command) {
            case MENU_EXIT:
                echo GOODBYE_MESSAGE . "\n";
                exit(0);
            case MENU_SELECT_PRODUCTS:
                selectProducts();
                break;
            case MENU_GET_BILL:
                showFinalBill();
                break;
            case MENU_SETUP_PROFILE:
                setupProfile();
                break;
            default:
                echo ERROR_INVALID_COMMAND . "\n";
                break;
        }
    }
}


/**
 * Показати список доступних товарів
 */
function showProductList() {
    global $products;

    echo "\n";
    echo "№  НАЗВА                 ЦІНА\n";
    foreach ($products as $id => $product) {
        $name = $product["name"];
        $price = $product["price"];
        preg_match_all('/./us', $name, $matches);
        $length = count($matches[0]);
        $padding = str_repeat(" ", 22 - $length);
        printf("%-2d %s%s%d\n", $id, $name, $padding, $price);

    }
    echo "   -----------\n";
    echo "0  ПОВЕРНУТИСЯ\n";
    echo "Виберіть товар: ";
}


/**
 * Показати вміст кошика
 */
function showCart() {
    global $cart;

    if (empty($cart)) {
        echo CART_EMPTY . "\n";
        return;
    }

    echo "У КОШИКУ:\n";
    echo "НАЗВА        КІЛЬКІСТЬ\n";
    foreach ($cart as $id => $quantity) {
        global $products;
        printf("%-22s %-4d\n", $products[$id]["name"], $quantity);
    }
}

/**
 * Вибір товарів для кошика
 */
function selectProducts() {
    global $products, $cart;

    while (true) {
        showProductList();
        $productId = trim(fgets(STDIN));

        if ($productId === MENU_EXIT) {
            return; // Повернутися до головного меню
        }

        if (!isset($products[$productId])) {
            echo ERROR_INVALID_PRODUCT . "\n";
            continue;
        }

        $product = $products[$productId];
        echo "Вибрано: " . $product["name"] . "\n";
        echo "Введіть кількість, штук: ";

        $quantity = (int)trim(fgets(STDIN));

        if ($quantity < 0 || $quantity > MAX_PRODUCT_QUANTITY) {
            echo ERROR_INVALID_QUANTITY . "\n";
            continue;
        }

        if ($quantity === 0) {
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                echo REMOVING_FROM_CART . "\n";
            }
        } else {
            $cart[$productId] = $quantity;
        }

        showCart();
    }
}

/**
 * Показати підсумковий рахунок
 */
function showFinalBill() {
    global $products, $cart;

    if (empty($cart)) {
        echo ERROR_EMPTY_CART . "\n";
        return;
    }

    echo "\n";
    echo "№  НАЗВА                 ЦІНА  КІЛЬКІСТЬ  ВАРТІСТЬ\n";

    $total = 0;
    $index = 1;

    foreach ($cart as $productId => $quantity) {
        $product = $products[$productId];
        $cost = $product["price"] * $quantity;
        $total += $cost;

        // Використовуємо той самий підхід, який працює у showProductList()
        // Calculate length of name in characters (not bytes) using preg_match_all
        $name = $product["name"];
        preg_match_all('/./us', $name, $matches);
        $nameLength = count($matches[0]);

        // Calculate needed padding
        $paddingSize = max(21 - $nameLength, 0);
        $padding = str_repeat(" ", $paddingSize);

        // Create padded name string
        $nameStr = $name . $padding;

        // Форматуємо числові колонки з використанням printf()
        printf("%-2d %-21s %-5d %-10d %-d\n",
            $index++,
            $nameStr,
            $product["price"],
            $quantity,
            $cost
        );
    }

    echo "\nРАЗОМ ДО CПЛАТИ: $total\n";
}


/**
 * Налаштування профілю користувача
 */
function setupProfile() {
    global $profile;

    $validName = false;
    while (!$validName) {
        echo "Ваше імʼя: ";
        $name = trim(fgets(STDIN));

        if (empty($name) || !preg_match('/[a-zA-Zа-яА-ЯіІїЇєЄґҐ]/u', $name)) {
            echo ERROR_EMPTY_NAME . "\n";
            continue;
        }

        $profile["name"] = $name;
        $validName = true;
    }

    $validAge = false;
    while (!$validAge) {
        echo "Ваш вік: ";
        $age = (int)trim(fgets(STDIN));

        if ($age < MIN_AGE || $age > MAX_AGE) {
            echo ERROR_INVALID_AGE . "\n";
            continue;
        }

        $profile["age"] = $age;
        $validAge = true;
    }

    echo PROFILE_UPDATED . "\n";
    echo "Імʼя: " . $profile["name"] . "\n";
    echo "Вік: " . $profile["age"] . "\n";
}
?>