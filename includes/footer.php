</main>

<?php if (!($hideFooter ?? false)): ?>
<footer class="app-footer">
    <div class="container-wide footer-inner">
        <div class="footer-brand">
            <span class="brand-mark brand-mark-small" aria-hidden="true"><i class="bi bi-hand-index-thumb"></i></span>
            <div><strong>BIMBoleh</strong><p><?= e(t('footer.mission')) ?></p></div>
        </div>
        <div class="footer-meta">
            <span><i class="bi bi-shield-check" aria-hidden="true"></i> <?= e(t('footer.safe')) ?></span>
            <span><i class="bi bi-webcam" aria-hidden="true"></i> <?= e(t('footer.camera')) ?></span>
            <span>© <span data-current-year><?= date('Y') ?></span> <?= e(t('footer.project')) ?></span>
        </div>
    </div>
</footer>
<?php endif; ?>

<?php
$globalClientI18nKeys = [
    'nav.open', 'nav.close', 'accessibility.open', 'accessibility.close',
    'accessibility.saved', 'accessibility.reset_confirmation', 'accessibility.save_failed', 'common.processing',
];
$clientMessages = clientTranslations(array_merge($globalClientI18nKeys, $clientI18nKeys ?? []));
?>
<script>window.BIM_I18N = <?= json_encode($clientMessages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="<?= e($basePath) ?>assets/js/app.js"></script>
<?= $pageScripts ?? '' ?>
</body>
</html>
