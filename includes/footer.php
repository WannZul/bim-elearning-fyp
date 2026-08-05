</main>

<?php if (!($hideFooter ?? false)): ?>
<footer class="app-footer">
    <div class="container-wide footer-inner">
        <div class="footer-brand">
            <span class="brand-mark brand-mark-small" aria-hidden="true"><i class="bi bi-hand-index-thumb"></i></span>
            <div><strong>BIMBoleh</strong><p>Membina komunikasi yang lebih inklusif, satu isyarat pada satu masa.</p></div>
        </div>
        <div class="footer-meta">
            <span><i class="bi bi-shield-check"></i> Pembelajaran selamat</span>
            <span><i class="bi bi-webcam"></i> Kamera diproses dalam pelayar</span>
            <span>© <span data-current-year><?= date('Y') ?></span> Projek FYP</span>
        </div>
    </div>
</footer>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e($basePath) ?>assets/js/app.js"></script>
<?= $pageScripts ?? '' ?>
</body>
</html>
