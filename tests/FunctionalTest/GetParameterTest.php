<?php
/**
 * Get parameter test
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Symfony panther
 * @license    MIT License
 * @copyright  TransmitMail development team
 * @link       https://github.com/dounokouno/TransmitMail
 */

namespace TransmitMail\Tests;

class GetParameterTest extends TransmitMailPantherTestCase
{
    private $getParameter = 'GETパラメータサンプル';

    /**
     * GETパラメータの受け取り、出力のテスト
     */
    public function testGetParameter()
    {
        $values = array_merge([$this->getParameter], $this->templateSyntaxInputPatterns);

        foreach ($values as $value) {
            $this->crawler = $this->client->request('GET', '/' . $this->tm->config['mailform_program'] . '?example=' . urlencode($value));
            $this->assertEquals($value, $this->findElementAndGetValue('input[type="hidden"][name="GET値取得サンプル"]'));
            $this->inputRequiredField();
            $this->submitInputForm();
            $this->assertEquals($this->confirmPageTitle, $this->client->getTitle());
            $this->assertStringContainsString($value, $this->findElementAndGetText('#content table'));
        }
    }

    /**
     * $_GET['file'] の受け取り、出力のテスト
     */
    public function testGetFileParameter()
    {
        $this->inputRequiredField();
        $this->submitInputForm();
        $imgUrl = $this->findElementAndGetAttr('#content table a', 'href');

        $imgFileName = explode('?file=', $imgUrl)[1];
        $values = array_merge([$this->getParameter], $this->templateSyntaxInputPatterns);

        // 送信した画像ファイルの場合
        $this->crawler = $this->client->request('GET', '/' . $this->tm->config['mailform_program'] . '?file=' . urlencode($imgFileName));
        $regexp = '/' . preg_quote($imgFileName, '/') . '$/';
        $attr = $this->findElementAndGetAttr('img', 'src');
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression($regexp, $attr);
        } else {
            $this->assertRegExp($regexp, $attr);
        }

        // 送信した画像ファイルではない場合
        foreach ($values as $value) {
            $this->crawler = $this->client->request('GET', '/' . $this->tm->config['mailform_program'] . '?file=' . urlencode($value));
            $this->assertEquals($this->errorPageTitle, $this->client->getTitle());
            $this->assertStringContainsString($this->findElementAndGetText('#content ul > li'), $value . $this->tm->config['error_file_not_exist']);
        }
    }
}
