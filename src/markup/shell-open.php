<?php
/**
 * The shell's opening half. One head, one skip link, one header, one main landmark.
 *
 * @var Iniznet\Mahout\Render\ClassNameResolver $c
 * @var Iniznet\Mahout\Render\Component|null    $main
 * @var Iniznet\Mahout\Render\Component|null    $header
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class($c('site')); ?>>
<?php wp_body_open(); ?>
<a class="<?php echo esc_attr($c('skip-link')); ?>" href="#main"><?php esc_html_e('Skip to content', 'mahout-render'); ?></a>
<?php echo $header?->render(); // the host's chrome, escaped at its own outputs?>
<main id="main" class="<?php echo esc_attr($c('main')); ?>">
<?php if ($main instanceof Iniznet\Mahout\Render\Component) {
    echo $main->render();
} // the Surface's own escaped bytes?>
