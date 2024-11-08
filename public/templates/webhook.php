<div>
    <h2>Статус Webhook</h2>
    <p><?= htmlspecialchars($webhookStatus); ?></p>

    <?php if (!empty($updates)): ?>
        <h3>Обновления:</h3>
        <pre><?= htmlspecialchars(print_r($updates, true)); ?></pre>
    <?php else: ?>
        <p>Нет новых обновлений.</p>
    <?php endif; ?>
</div>
