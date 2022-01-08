<?php
/**
 * Get parameter test
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Selenium 2
 * @license    MIT License
 * @copyright  TransmitMail development team
 * @link       https://github.com/dounokouno/TransmitMail
 */

class GetParameterTest extends TransmitMailFunctionalTest
{
    private $getParameter = 'GETパラメータサンプル';

    /**
     * GETパラメータの受け取り、出力のテスト
     */
    public function testGetParameter()
    {
        $values = array_merge(array($this->getParameter), $this->templateSyntaxInputPatterns);

        foreach ($values as $value) {
            $this->url('?example=' . urlencode($value));
            $this->assertEquals($value, $this->byCssSelector('input[type="hidden"][name="GET値取得サンプル"]')->value());
            $this->inputRequiredField();
            $this->submitInputForm();
            $this->assertEquals($this->confirmPageTitle, $this->title());
            $this->assertStringContainsString($value, $this->byCssSelector('#content table')->text());
        }
    }

    /**
     * $_GET['file'] の受け取り、出力のテスト
     */
    public function testGetFileParameter()
    {
        $this->url('');
        $this->inputRequiredField();
        $this->submitInputForm();
        $imgUrl = $this->byCssSelector('#content table a')->attribute('href');
        $imgFileName = explode('?file=', $imgUrl)[1];
        $values = array_merge(array($this->getParameter), $this->templateSyntaxInputPatterns);

        // 送信した画像ファイルの場合
        $this->url('?file=' . urlencode($imgFileName));
        $this->assertRegExp('/' . preg_quote($imgFileName, '/') . '$/', $this->byCssSelector('img')->attribute('src'));

        // 送信した画像ファイルではない場合
        foreach ($values as $value) {
            $this->url('?file=' . urlencode($value));
            $this->assertEquals($this->errorPageTitle, $this->title());
            $this->assertStringContainsString($this->byCssSelector('#content ul > li')->text(), $value . $this->tm->config['error_file_not_exist']);
        }
    }
}
