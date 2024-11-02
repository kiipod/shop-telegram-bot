<div class="flex items-center justify-center min-h-full bg-gray-100">
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Добавить новый продукт</h1>

        <form action="product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <!-- Название продукта -->
            <div>
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Название продукта</label>
                <input type="text" id="name" name="name" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       placeholder="Введите название продукта">
            </div>

            <!-- Цена продукта -->
            <div>
                <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Цена продукта</label>
                <input type="number" id="price" name="price" required step="0.01"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       placeholder="Введите цену продукта">
            </div>

            <!-- Загрузка изображения -->
            <div>
                <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Изображение продукта</label>
                <input type="file" id="image" name="image" accept="image/*" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <!-- Кнопка отправки -->
            <div class="flex items-center justify-center">
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Добавить продукт
                </button>
            </div>
        </form>
    </div>
</div>
