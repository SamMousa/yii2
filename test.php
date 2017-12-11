<?php
define('YII_DEBUG', true);
include 'vendor/autoload.php';

include 'framework/Yii.php';

$application = new \yii\console\Application([
    'id' => 'poc',
    'basePath' => '.',
    'controllerMap' => [
        'test' => \app\controllers\TestController::class
    ]
]);

die($application->run());