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
    /**
     * GETパラメータの受け取り、出力のテスト
     */
    public function testGetParameter()
    {
        $getParameter = 'GETパラメータサンプル';

        $this->url('?example=' . urlencode($getParameter));
        $this->assertEquals($getParameter, $this->byCssSelector('input[type="hidden"][name="GET値取得サンプル"]')->value());
        $this->inputRequiredField();
        $this->submitInputForm();
        $this->assertEquals($this->confirmPageTitle, $this->title());
        $this->assertContains($getParameter, $this->byCssSelector('#content table')->text());
    }
}
