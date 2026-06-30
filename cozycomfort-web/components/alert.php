<?php if ($notification): ?>
<div class="alert alert-<?php echo $notification['type']; ?>" id="alertMessage">
    <span class="alert-icon">
        <?php 
        switch($notification['type']) {
            case 'success': echo '✅'; break;
            case 'error': echo '❌'; break;
            case 'warning': echo '⚠️'; break;
            default: echo 'ℹ️';
        }
        ?>
    </span>
    <span class="alert-message"><?php echo htmlspecialchars($notification['message']); ?></span>
    <button class="alert-close" onclick="closeAlert()">&times;</button>
</div>
<?php endif; ?>
