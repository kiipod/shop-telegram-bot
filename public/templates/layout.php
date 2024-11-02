<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>KanaiShop</title>
    <link href="../css/output.css" rel="stylesheet">
</head>

<body class="min-h-screen flex flex-col">

<div class="flex flex-col flex-grow">

    <header class="py-[30px] bg-white shadow-[0_0_30px_0_rgba(0,0,0,0.1)]">

        <div class="flex justify-center">

            <a class="max-w-[160px] bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-700" href="product.php">Добавить товар</a>

        </div>

    </header>

    <?= $content; ?>

</div>

<footer class="bg-white shadow-[0_0_30px_0_rgba(0,0,0,0.1)]">

    <div class="flex justify-between items-center py-[25px]">

        <div class="text-sm leading-1">
            <p>© 2024, KanaiShop</p>
            <p>Интернет-магазин самых нужных товаров</p>
        </div>

    </div>

</footer>

</body>

</html>
