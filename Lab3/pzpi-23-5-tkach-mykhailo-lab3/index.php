<?php
// Установка заголовка страницы
$pageTitle = 'Home - Web Store';

// Дополнительные стили для этой страницы
$additionalStyles = '
    .welcome {
        text-align: center;
        max-width: 800px;
    }
    
    .welcome h1 {
        margin-bottom: 20px;
    }
    
    .welcome p {
        line-height: 1.6;
        margin-bottom: 20px;
    }
    
    .shop-now-btn {
        display: inline-block;
        margin-top: 20px;
    }
';

// Включение файла хедера
include 'header.php';
?>

    <!-- Тело сайта -->
    <div class="content">
        <div class="welcome">
            <h1>Welcome to Our Web Store</h1>
            <p>This is a simple web store created as a student project. You can browse our products and add them to your shopping cart.</p>
            <a href="products.php" class="btn shop-now-btn">Shop Now</a>
        </div>
    </div>

<?php
// Включение файла футера
include 'footer.php';
?>