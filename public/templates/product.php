<div class="mt-8 px-4 md:px-8">

    <?php if (!empty($products)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="flex font-sans">
                        <div class="flex-none w-48 h-48 relative">
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt=""
                                 class="absolute inset-0 w-full h-full object-cover"
                                 loading="lazy"
                                 style="max-width: 100%; max-height: 100%;" />
                        </div>
                        <form class="flex-auto p-6">
                            <div class="flex flex-wrap">
                                <h1 class="flex-auto text-lg font-semibold text-slate-900">
                                    <?= htmlspecialchars($product['name']) ?>
                                </h1>
                                <div class="text-lg font-semibold text-slate-500">
                                    <?= htmlspecialchars($product['price'] . ' ₽') ?>
                                </div>
                                <div class="w-full flex-none text-sm font-medium text-slate-700 mt-2">
                                    In stock
                                </div>
                            </div>
                            <div class="flex space-x-4 mb-6 text-sm font-medium">
                                <div class="flex-auto flex space-x-4">
                                    <a href="order.php?productId=<?php echo $product['id']; ?>"
                                       class="h-10 px-6 font-semibold rounded-md bg-blue-500 hover:bg-blue-700 text-white"
                                       type="submit">
                                        Добавить в корзину
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Продукты не найдены.</p>
    <?php endif; ?>

</div>
