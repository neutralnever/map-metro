<?php
// functions.php дочерней темы
add_action('wp_enqueue_scripts', 'my_child_theme_styles');
function my_child_theme_styles() {
    // Загружаем стиль родительской темы
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    // Загружаем стиль дочерней темы (должен идти после родительского)
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
}