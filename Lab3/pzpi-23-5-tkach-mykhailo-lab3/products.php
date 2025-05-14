<?php
// Установка заголовка страницы
$pageTitle = 'Products - Web Store';

// Дополнительные стили для этой страницы
$additionalStyles = '
    .product-form {
        width: 100%;
        max-width: 600px;
    }
    
    .category-header {
        margin-top: 30px;
        margin-bottom: 15px;
        border-bottom: 2px solid #ddd;
        padding-bottom: 5px;
        color: #555;
    }
    
    .product-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    
    .product-table td {
        padding: 10px;
        vertical-align: middle;
    }
    
    .product-icon {
        width: 40px;
        text-align: center;
        font-size: 20px;
    }
    
    .product-name {
        width: 200px;
    }
    
    .product-input {
        width: 80px;
        text-align: center;
    }
    
    .product-price {
        width: 80px;
        text-align: right;
    }
    
    .form-footer {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    
    /* Скрытие визуального лейбла, но сохранение его для скринридеров */
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
';

// Включение файла хедера
include 'header.php';

// Загрузка данных о товарах из JSON файла
$productsData = file_get_contents('products.json');
$products = json_decode($productsData, true);
?>

    <!-- Тело сайта -->
    <div class="content">
        <form method="POST" action="add_to_cart.php" class="product-form">
            <?php foreach ($products['products'] as $category): ?>
                <h3 class="category-header"><?php echo $category['category']; ?></h3>

                <table class="product-table">
                    <?php foreach ($category['items'] as $item): ?>
                        <tr>
                            <td class="product-icon"><?php echo $item['icon']; ?></td>
                            <td class="product-name"><?php echo $item['name']; ?></td>
                            <td class="product-input">
                                <label for="<?php echo $item['id']; ?>" class="sr-only">Quantity of <?php echo $item['name']; ?></label>
                                <input type="number" id="<?php echo $item['id']; ?>" name="<?php echo $item['id']; ?>" min="0" value="0" data-name="<?php echo $item['name']; ?>" data-price="<?php echo $item['price']; ?>" data-icon="<?php echo $item['icon']; ?>">
                            </td>
                            <td class="product-price">$<?php echo number_format($item['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>

            <div class="form-footer">
                <button type="submit" class="btn-send">Send</button>
            </div>
        </form>
    </div>

<?php
// Включение файла футера
include 'footer.php';
?>