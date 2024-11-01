<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>KanaiShop</title>
    <link href="../css/output.css" rel="stylesheet">
</head>

<body class="min-h-screen flex flex-col">

<div class="flex flex-col flex-grow mb-[-202px]">

    <header class="py-[30px] border-b border-white/50">

        <div class="flex justify-start">
            <h1 class="p-6 max-w-sm mx-auto bg-white rounded-xl shadow-lg flex items-center gap-x-1">KanaiShop</h1>

            <a class="max-w-[160px] mr-[30px] bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-700" href="#">Добавить товар</a>

        </div>

    </header>

    <?= $content; ?>

</div>

<footer class="bg-white shadow-[0_0_30px_0_rgba(0,0,0,0.1)]">

    <div class="flex justify-between items-center py-[25px]">

        <div class="max-w-[212px] text-sm leading-1">
            <p class="m-12">© 2024, KanaiShop</p>
            <p class="m-12">Интернет-магазин самых нужных товаров</p>
        </div>

        <a class="max-w-[160px] bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-700" href="#">Добавить товар</a>

        <div class="flex items-center">
            <span class="font-bold underline">Разработано:</span>
            <a class="block w-[118px] h-[40px]" href="https://github.com/kiipod">
                <span class="font-bold underline">kiipod</span>
            </a>
        </div>

    </div>

</footer>

</body>

</html>
