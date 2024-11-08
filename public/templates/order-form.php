<form action="create-order.php" method="POST" class="order-form">
    <h2 class="order-form__title">Создание заказа</h2>

    <label for="product_id" class="order-form__label">Выберите товар:</label>
    <select name="product_id" id="product_id" class="order-form__select" onchange="updateProductPrice()">
        <?php foreach ($products as $product): ?>
            <option value="<?= $product['id']; ?>" data-price="<?= htmlspecialchars($product['price']); ?>">
                <?= htmlspecialchars($product['name']); ?> (<?= htmlspecialchars($product['price']); ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label for="product_price" class="order-form__label">Стоимость:</label>
    <input type="text" name="product_price" id="product_price" readonly class="order-form__input order-form__input--readonly">

    <label for="product_count" class="order-form__label">Количество:</label>
    <input type="number" name="product_count" id="product_count" min="1" required class="order-form__input">

    <button type="submit" class="order-form__button">Создать заказ</button>
</form>

<script>
    function updateProductPrice() {
        const productSelect = document.getElementById("product_id");
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const productPrice = selectedOption.getAttribute("data-price");

        document.getElementById("product_price").value = productPrice + " ₽";
    }

    // Установим начальную стоимость первого продукта при загрузке страницы
    document.addEventListener("DOMContentLoaded", updateProductPrice);
</script>
