<?php if (!empty($products)): ?>

<?php foreach ($products as $product): ?>

        <div class="flex font-sans">
            <div class="flex-none w-48 relative">
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy" />
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
                        <button class="h-10 px-6 font-semibold rounded-md bg-black text-white" type="submit">
                            Buy now
                        </button>
                    </div>
                </div>
            </form>
        </div>

    <?php endforeach; ?>

<?php else: ?>

    <p>Продукты не найдены.</p>

<?php endif; ?>
