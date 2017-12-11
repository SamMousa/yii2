<?php

namespace app\controllers;
use yii\base\Behavior;
use yii\base\Event;
use yii\console\Controller;
use yii\helpers\Console;

class TestBehavior extends Behavior {
    public function events()
    {
        return [
            'testbehavior' => 'abc'
        ];
    }

    public function abc(Event $event)
    {
        \Yii::$app->controller->stdout("Event fired in behavior: {$event->name}\n", Console::FG_GREEN);
        $senderClass = get_class($event->sender);
        \Yii::$app->controller->stdout("Event fired from: {$senderClass}\n", Console::FG_YELLOW);
    }

}
class TestController extends Controller
{
    public function behaviors()
    {
        return [
            'test' => [
                'class' => TestBehavior::class
            ]
        ];
    }

    public function actionIndex()
    {
        $this->stdout("Starting test.\n", Console::FG_GREEN);
        $this->on('test', function() {
            $this->stdout("CALLBACK!\n", Console::FG_CYAN);
        });
        $this->trigger('test');
        $this->trigger('testbehavior');
        $this->detachBehavior('test');
        $this->trigger('testbehavior');

        // Test application level events.
        Event::on(get_class($this), 'test', function() {
            $this->stdout("GLOBAL CALLBACK!\n", Console::FG_RED);
        });
        $this->trigger('test');

        Event::on(\yii\base\Controller::class, 'test', function() {
            $this->stdout("BASE CONTROLLER GLOBAL CALLBACK!\n", Console::FG_RED);
        });
        $this->trigger('test');

        Event::trigger($this, 'test');
    }

}