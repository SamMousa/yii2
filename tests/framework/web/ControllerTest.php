<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\base\InlineAction;
use yii\web\Response;
use yiiunit\TestCase;

/**
 * @group web
 */
class ControllerTest extends TestCase
{
    /** @var FakeController */
    private $controller;
    public function testBindActionParams()
    {
        $aksi1 = new InlineAction('aksi1', $this->controller, 'actionAksi1');

        $params = ['fromGet' => 'from query params', 'q' => 'd426', 'validator' => 'avaliable'];
        list($fromGet, $other) = $this->controller->bindActionParams($aksi1, $params);
        $this->assertEquals('from query params', $fromGet);
        $this->assertEquals('default', $other);

        $params = ['fromGet' => 'from query params', 'q' => 'd426', 'other' => 'avaliable'];
        list($fromGet, $other) = $this->controller->bindActionParams($aksi1, $params);
        $this->assertEquals('from query params', $fromGet);
        $this->assertEquals('avaliable', $other);
    }

    public function testInjectedActionParams()
    {
        $injectionAction = new InlineAction('injection', $this->controller, 'actionInjection');
        $params = ['between' => 'test', 'after' => 'another', 'before' => 'test'];
        $args = $this->controller->bindActionParams($injectionAction, $params);
        $this->assertEquals($params['before'], $args[0]);
        $this->assertEquals(\Yii::$app->request, $args[1]);
        $this->assertEquals($params['between'], $args[2]);
        $this->assertInstanceOf(Post::className(), $args[3]);
        $this->assertEquals($params['after'], $args[4]);
    }
    /**
     * @see https://github.com/yiisoft/yii2/issues/17701
     */
    public function testBindTypedActionParams()
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped('Can not be tested on PHP < 7.0');
            return;
        }

        // Use the PHP7 controller for this test
        $this->controller = new FakePhp7Controller('fake', new \yii\web\Application([
            'id' => 'app',
            'basePath' => __DIR__,

            'components' => [
                'request' => [
                    'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
            ],
        ]));
        $this->mockWebApplication(['controller' => $this->controller]);

        $aksi1 = new InlineAction('aksi1', $this->controller, 'actionAksi1');

        $params = ['foo' => '100', 'bar' => null, 'true' => 'on', 'false' => 'false'];
        list($foo, $bar, $true, $false) = $this->controller->bindActionParams($aksi1, $params);
        $this->assertSame(100, $foo);
        $this->assertSame(null, $bar);
        $this->assertSame(true, $true);
        $this->assertSame(false, $false);

        $params = ['foo' => 'oops', 'bar' => null];
        $this->expectException('yii\web\BadRequestHttpException');
        $this->expectExceptionMessage('Invalid data received for parameter "foo".');
        $this->controller->bindActionParams($aksi1, $params);
    }

    public function testAsJson()
    {
        $data = [
            'test' => 123,
            'example' => 'data',
        ];
        $result = $this->controller->asJson($data);
        $this->assertInstanceOf('yii\web\Response', $result);
        $this->assertSame(Yii::$app->response, $result, 'response should be the same as Yii::$app->response');
        $this->assertEquals(Response::FORMAT_JSON, $result->format);
        $this->assertEquals($data, $result->data);
    }

    public function testAsXml()
    {
        $data = [
            'test' => 123,
            'example' => 'data',
        ];
        $result = $this->controller->asXml($data);
        $this->assertInstanceOf('yii\web\Response', $result);
        $this->assertSame(Yii::$app->response, $result, 'response should be the same as Yii::$app->response');
        $this->assertEquals(Response::FORMAT_XML, $result->format);
        $this->assertEquals($data, $result->data);
    }

    public function testRedirect()
    {
        $_SERVER['REQUEST_URI'] = 'http://test-domain.com/';
        $this->assertEquals($this->controller->redirect('')->headers->get('location'), '/');
        $this->assertEquals($this->controller->redirect('http://some-external-domain.com')->headers->get('location'), 'http://some-external-domain.com');
        $this->assertEquals($this->controller->redirect('/')->headers->get('location'), '/');
        $this->assertEquals($this->controller->redirect('/something-relative')->headers->get('location'), '/something-relative');
        $this->assertEquals($this->controller->redirect(['/'])->headers->get('location'), '/index.php?r=');
        $this->assertEquals($this->controller->redirect(['view'])->headers->get('location'), '/index.php?r=fake%2Fview');
        $this->assertEquals($this->controller->redirect(['/controller'])->headers->get('location'), '/index.php?r=controller');
        $this->assertEquals($this->controller->redirect(['/controller/index'])->headers->get('location'), '/index.php?r=controller%2Findex');
        $this->assertEquals($this->controller->redirect(['//controller/index'])->headers->get('location'), '/index.php?r=controller%2Findex');
        $this->assertEquals($this->controller->redirect(['//controller/index', 'id' => 3])->headers->get('location'), '/index.php?r=controller%2Findex&id=3');
        $this->assertEquals($this->controller->redirect(['//controller/index', 'id_1' => 3, 'id_2' => 4])->headers->get('location'), '/index.php?r=controller%2Findex&id_1=3&id_2=4');
        $this->assertEquals($this->controller->redirect(['//controller/index', 'slug' => 'äöüß!"§$%&/()'])->headers->get('location'), '/index.php?r=controller%2Findex&slug=%C3%A4%C3%B6%C3%BC%C3%9F%21%22%C2%A7%24%25%26%2F%28%29');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->controller = new FakeController('fake', new \yii\web\Application([
            'id' => 'app',
            'basePath' => __DIR__,

            'components' => [
                'request' => [
                    'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
            ],
        ]));
        $this->mockWebApplication(['controller' => $this->controller]);
    }
}
