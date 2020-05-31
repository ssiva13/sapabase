<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once 'vendor/autoload.php';

    $loader = new Twig_Loader_Filesystem('templates');
    $twig = new Twig_Environment($loader, array(
        'debug' => true
    ));

    $twig = new Twig_Environment($loader);
    $asset_function = new Twig_SimpleFunction('asset', function ($path) {
       return $path;
    });
    $twig->addFunction($asset_function);

    $ux_function = new Twig_SimpleFunction('ux', function ($path) {
       return $path;
    });
    $twig->addFunction($ux_function);

    $page = 'patterns/index.twig';

    if (isset($_GET['page'])) {
        $page = 'patterns/' . $_GET['page'] . '.twig';
    }

    $template = $twig->load($page);

    echo $template->render(array(
        'page' => isset($_GET['page']) ? $_GET['page'] : 'index',
        'request' => $_GET,
    ));
?>
