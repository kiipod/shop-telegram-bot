<div class="flex items-center justify-center min-h-full bg-gray-100">
    <form action="create_order.php" method="POST" class="max-w-lg mx-auto p-4 bg-white rounded shadow" enctype="multipart/form-data">
        <h2 class="text-lg font-bold mb-4">Добавить заказ</h2>

        <!-- Скрытое поле с product_id -->
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']); ?>">

        <!-- Отображение информации о продукте -->
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Название продукта:</label>
            <span><?= htmlspecialchars($product['name']); ?></span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Цена продукта:</label>
            <span id="product-price"><?= number_format($product['price'], 2); ?></span> ₽
        </div>

        <!-- Поле для ввода количества -->
        <div class="mb-4">
            <label for="product_count" class="block text-gray-700 text-sm font-bold mb-2">Количество:</label>
            <input type="number" name="product_count" id="product_count" min="1" value="1" required class="block w-full border border-gray-300 rounded p-2">
        </div>

        <!-- Поле для ввода номера телефона -->
        <div class="mb-4">
            <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Номер телефона:</label>
            <input type="tel" name="phone" id="phone" required pattern="\+?[0-9]{10,15}" placeholder="+1234567890" class="block w-full border border-gray-300 rounded p-2">
        </div>

        <!-- Отображение суммарной стоимости -->
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Суммарная стоимость:</label>
            <span id="total-price"><?= number_format($product['price'], 2); ?></span> ₽
        </div>

        <!-- Кнопка отправки -->
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Создать заказ
        </button>
    </form>
</div>

<script>
    // Получаем элементы для расчета
    const productPrice = <?= $product['price']; ?>;
    const productCountInput = document.getElementById('product_count');
    const totalPriceElement = document.getElementById('total-price');

    // Функция для обновления суммы
    function updateTotalPrice() {
        const count = parseInt(productCountInput.value) || 1;
        const totalPrice = productPrice * count;
        totalPriceElement.textContent = totalPrice.toFixed(2);
    }

    // Обновляем сумму при изменении количества
    productCountInput.addEventListener('input', updateTotalPrice);
</script>
