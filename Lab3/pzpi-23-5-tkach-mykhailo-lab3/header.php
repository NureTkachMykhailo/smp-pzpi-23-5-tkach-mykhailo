<?php
// Загрузка данных хедера из JSON файла
$headerData = file_get_contents('header_data.json');
$navigation = json_decode($headerData, true);

// Определяем текущую страницу
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Web Store'; ?></title>
    <link rel="stylesheet" href="styles.css">
    <?php if (isset($additionalStyles)): ?>
        <style>
            <?php echo $additionalStyles; ?>
        </style>
    <?php endif; ?>
</head>
<body>
<!-- Шапка сайта с иконками -->
<div class="header">
    <?php
    $first = true;
    foreach ($navigation['navigation'] as $item):
        if (!$first):
            ?>
            <span class="separator">|</span>
        <?php
        endif;
        $first = false;
        ?>
        <a href="<?php echo $item['link']; ?>" class="nav-item<?php echo ($currentPage == $item['link']) ? ' active' : ''; ?>">
            <span class="nav-icon"><?php echo $item['icon']; ?></span> <?php echo $item['name']; ?>
        </a>
    <?php endforeach; ?>
</div>