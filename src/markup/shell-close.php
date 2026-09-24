<?php
/**
 * The shell's closing half.
 *
 * @var Iniznet\Mahout\Render\ClassNameResolver $c
 * @var Iniznet\Mahout\Render\Component|null    $footer
 */
?>
</main>
<?php echo $footer?->render(); // the host's chrome, escaped at its own outputs?>
<?php wp_footer(); ?>
</body>
</html>
