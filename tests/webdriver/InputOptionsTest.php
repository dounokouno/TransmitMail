<?php
/**
 * Basi test.
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Selenium 2
 * @author     TAGAWA Takao
 * @license    MIT License
 * @copyright  TransmitMail Development Team
 * @link       https://github.com/dounokouno/TransmitMail
 */

class InputOptionsTest extends TransmitMailFunctionalTest
{

    /**
     * 入力必須のテスト
     */
    public function testRequiredField()
    {
        $this->url('');
 
        $selectors = array(
            'target' => 'input[type="text"][name="入力必須"]',
            'option' => 'input[type="hidden"][name="required[]"][value="入力必須"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_REQUIRED;
        $validValue = '入力必須項目の入力テスト';

        // 入力必須とするフィールドを確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // エラーの場合
        $this->submitInputForm();
        $this->assertContains($this->globalErrorMessage, $this->byCssSelector('#content')->text());
        $this->assertEquals($errorMessage, $this->byCssSelector('#content ul li')->text());
        $this->assertEquals($errorMessage, $this->byCssSelector('#content table tr td div.error')->text());

        // 成功の場合
        $this->byCssSelector($selectors['target'])->value($validValue);
        $this->submitInputForm();
        $this->assertEquals($this->confirmPageTitle, $this->title());
        $this->assertContains($validValue, $this->byCssSelector('#content table')->text());
    }

    /**
     * メールアドレスの書式チェックのテスト
     */
    public function testMailAddressField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="メールアドレス"]',
            'option' => 'input[type="hidden"][name="email[]"][value="メールアドレス"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_EMAIL;
        $invalidValues = array(
            'user@foo,com',
            'user_at_foo.org',
            'foo@bar_baz_com',
            'foo@bar+baz.com'
        );
        $validValues = array(
            'info@example',
            'info@example.com',
            'info..@example.com',
            'info@example.co.jp',
            'info@example.museum',
            'lastname.firstname@example.com',
            'lastname+firstname@example.com',
            'lastname+firstname@example.unknowndomain'
        );

        // 入力フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputErrorTest($invalidValues, $selectors['target'], $errorMessage);
        $this->inputSuccessTest($validValues, $selectors['target']);
    }

    /**
     * 半角文字の入力チェックのテスト
     */
    public function testHankakuField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="半角文字"]',
            'option' => 'input[type="hidden"][name="hankaku[]"][value="半角文字"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_HANKAKU;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->inputPatterns['num'],
            $this->inputPatterns['zenkakuNum'],
            $this->inputPatterns['eiji'],
            $this->inputPatterns['zenkakuEiji'],
            $this->inputPatterns['numHyphen'],
            $this->inputPatterns['zenkakuNumHyphen'],
            $this->inputPatterns['eijiHyphen'],
            $this->inputPatterns['zenkakuEijiHyphen'],
            $this->inputPatterns['eisu'],
            $this->inputPatterns['zenkakuEisu'],
            $this->inputPatterns['eisuHyphen'],
            $this->inputPatterns['zenkakuEisuHyphen'],
            $this->inputPatterns['eisuKigo'],
            $this->inputPatterns['zenkakuEisuKigo']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->inputPatterns, $validValues, $selectors['target'], $errorMessage, 'hankaku');
    }

    /**
     * 半角英数字の入力チェックのテスト
     */
    public function testHankakuEisuField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="半角英数字"]',
            'option' => 'input[type="hidden"][name="hankaku_eisu[]"][value="半角英数字"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_HANKAKU_EISU;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->inputPatterns['num'],
            $this->inputPatterns['zenkakuNum'],
            $this->inputPatterns['eiji'],
            $this->inputPatterns['zenkakuEiji'],
            $this->inputPatterns['eisu'],
            $this->inputPatterns['zenkakuEisu']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->inputPatterns, $validValues, $selectors['target'], $errorMessage, 'hankaku_eisu');
    }

    /**
     * 半角英字の入力チェックのテスト
     */
    public function testHankakuEijiField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="半角英字"]',
            'option' => 'input[type="hidden"][name="hankaku_eiji[]"][value="半角英字"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_HANKAKU_EIJI;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->inputPatterns['eiji'],
            $this->inputPatterns['zenkakuEiji']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->inputPatterns, $validValues, $selectors['target'], $errorMessage, 'hankaku_eiji');
    }

    /**
     * 数字の入力チェックのテスト
     */
    public function testNumField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="数字"]',
            'option' => 'input[type="hidden"][name="num[]"][value="数字"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_NUM;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->inputPatterns['num'],
            $this->inputPatterns['zenkakuNum']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->inputPatterns, $validValues, $selectors['target'], $errorMessage, 'num');
    }

    /**
     * 数字＋ハイフンの入力チェックのテスト
     */
    public function testNumHyphenField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="数字＋ハイフン"]',
            'option' => 'input[type="hidden"][name="num_hyphen[]"][value="数字＋ハイフン"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_NUM_HYPHEN;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->inputPatterns['num'],
            $this->inputPatterns['zenkakuNum'],
            $this->inputPatterns['numHyphen'],
            $this->inputPatterns['zenkakuNumHyphen']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->inputPatterns, $validValues, $selectors['target'], $errorMessage, 'num_hyphen');
    }

    /**
     * ひらがなの入力チェックのテスト
     */
    public function testHiraganaField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="ひらがな"]',
            'option' => 'input[type="hidden"][name="hiragana[]"][value="ひらがな"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_HIRAGANA;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->inputPatterns['hiragana'],
            $this->inputPatterns['katakana'],
            $this->inputPatterns['hankakuKatakana']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->inputPatterns, $validValues, $selectors['target'], $errorMessage, 'hiragana');
    }

    /**
     * 全角カタカナの入力チェックのテスト
     */
    public function testZenkakuKatakanaField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="全角カタカナ"]',
            'option' => 'input[type="hidden"][name="zenkaku_katakana[]"][value="全角カタカナ"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_ZENKAKU_KATAKANA;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->inputPatterns['hiragana'],
            $this->inputPatterns['katakana'],
            $this->inputPatterns['hankakuKatakana']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->inputPatterns, $validValues, $selectors['target'], $errorMessage, 'zenkaku_katakana');
    }

    /**
     * 半角カタカナの入力チェックのテスト
     */
    public function testHankakuKatakanaField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="半角カタカナ"]',
            'option' => 'input[type="hidden"][name="hankaku_katakana[]"][value="半角カタカナ"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_HANKAKU_KATAKANA;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->inputPatterns['hiragana'],
            $this->inputPatterns['katakana'],
            $this->inputPatterns['hankakuKatakana']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->inputPatterns, $validValues, $selectors['target'], $errorMessage, 'hankaku_katakana');
    }

    /**
     * 全角文字を含むかの入力チェックのテスト
     */
    public function testZenkakuField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="全角文字を含むか"]',
            'option' => 'input[type="hidden"][name="zenkaku[]"][value="全角文字を含むか"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_ZENKAKU;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->inputPatterns['kanji'],
            $this->inputPatterns['hiragana'],
            $this->inputPatterns['katakana'],
            $this->inputPatterns['kanjiNum'],
            $this->inputPatterns['kanjiNumHyphen'],
            $this->inputPatterns['kanjiEiji'],
            $this->inputPatterns['kanjiEijiHyphen'],
            $this->inputPatterns['zenkakuNum'],
            $this->inputPatterns['zenkakuEiji'],
            $this->inputPatterns['zenkakuNumHyphen'],
            $this->inputPatterns['zenkakuEijiHyphen'],
            $this->inputPatterns['zenkakuEisu'],
            $this->inputPatterns['zenkakuEisuHyphen'],
            $this->inputPatterns['zenkakuEisuKigo']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->inputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * 全て全角文字の入力チェックのテスト
     *
     * @todo zenkakuEijiHyphen と zenkakuEisuKigo が入力エラーになるが、仕様的には入力エラーでは無いほうがいいのでは。 TransmitMail 本体の実装を検討する
     */
    public function testZenkakuAllField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="全て全角文字"]',
            'option' => 'input[type="hidden"][name="zenkaku_all[]"][value="全て全角文字"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_ZENKAKU_ALL;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->inputPatterns['kanji'],
            $this->inputPatterns['hiragana'],
            $this->inputPatterns['katakana'],
            $this->inputPatterns['zenkakuNum'],
            $this->inputPatterns['zenkakuEiji'],
            $this->inputPatterns['zenkakuNumHyphen'],
            $this->inputPatterns['zenkakuEisu'],
            $this->inputPatterns['zenkakuEisuHyphen']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->inputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * 3文字以上の文字数チェックのテスト
     */
    public function testLenFieldThreeOrMoreCharacters()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="3文字以上"]',
            'option' => 'input[type="hidden"][name="len[]"][value="3文字以上 3-"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_LEN;
        $errorMessage = str_replace('{文字数}', '3文字以上', $errorMessage);

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->lenFieldInputPatterns['eiji3'],
            $this->lenFieldInputPatterns['eiji4'],
            $this->lenFieldInputPatterns['eiji5'],
            $this->lenFieldInputPatterns['eiji6'],
            $this->lenFieldInputPatterns['eiji7'],
            $this->lenFieldInputPatterns['eiji8'],
            $this->lenFieldInputPatterns['eiji9'],
            $this->lenFieldInputPatterns['hiragana3'],
            $this->lenFieldInputPatterns['hiragana4'],
            $this->lenFieldInputPatterns['hiragana5'],
            $this->lenFieldInputPatterns['hiragana6'],
            $this->lenFieldInputPatterns['hiragana7'],
            $this->lenFieldInputPatterns['hiragana8'],
            $this->lenFieldInputPatterns['hiragana9']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->lenFieldInputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * 3文字以下の文字数チェックのテスト
     *
     * @todo 入力フィールドは「3文字以下」だけどエラーメッセージは「3文字以内」と出力される。どちらかに合わせたい。
     */
    public function testLenFieldThreeOrLessCharacters()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="3文字以下"]',
            'option' => 'input[type="hidden"][name="len[]"][value="3文字以下 -3"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_LEN;
        $errorMessage = str_replace('{文字数}', '3文字以内', $errorMessage);

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->lenFieldInputPatterns['eiji1'],
            $this->lenFieldInputPatterns['eiji2'],
            $this->lenFieldInputPatterns['eiji3'],
            $this->lenFieldInputPatterns['hiragana1'],
            $this->lenFieldInputPatterns['hiragana2'],
            $this->lenFieldInputPatterns['hiragana3']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->lenFieldInputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * 3文字固定の文字数チェックのテスト
     */
    public function testLenFieldThreeCharacterFixed()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="3文字固定"]',
            'option' => 'input[type="hidden"][name="len[]"][value="3文字固定 3-3"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_LEN;
        $errorMessage = str_replace('{文字数}', '3文字', $errorMessage);

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->lenFieldInputPatterns['eiji3'],
            $this->lenFieldInputPatterns['hiragana3']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->lenFieldInputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * 6文字以上8文字以下の文字数チェックのテスト
     *
     * @todo フィールド名が「6文字以上8以下」になっているが、正しくは「6文字以上8文字以下」では。修正したい。
     */
    public function testLenFieldSixOrMoreAndEightOrLessCharacters()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="6文字以上8以下"]',
            'option' => 'input[type="hidden"][name="len[]"][value="6文字以上8以下 6-8"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_LEN;
        $errorMessage = str_replace('{文字数}', '6〜8文字', $errorMessage);

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->lenFieldInputPatterns['eiji6'],
            $this->lenFieldInputPatterns['eiji7'],
            $this->lenFieldInputPatterns['eiji8'],
            $this->lenFieldInputPatterns['hiragana6'],
            $this->lenFieldInputPatterns['hiragana7'],
            $this->lenFieldInputPatterns['hiragana8']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->lenFieldInputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * 一致する文字列の入力チェックのテスト
     */
    public function testMatchField()
    {
        $this->url('');

        $selectors = array(
            'target1' => 'input[type="text"][name="一致1"]',
            'target2' => 'input[type="text"][name="一致2"]',
            'option' => 'input[type="hidden"][name="match[]"]'
        );
        $target1NameValue = $this->byCssSelector($selectors['target1'])->attribute('name');
        $target2NameValue = $this->byCssSelector($selectors['target2'])->attribute('name');
        $errorMessage = $target1NameValue . ERROR_MATCH;

        // 入力パターン
        $inputPatterns = array(
            'eisuKigoAndEisuKigo' => array($this->inputPatterns['eisuKigo'], $this->inputPatterns['eisuKigo']),
            'kanjiAndKanji' => array($this->inputPatterns['kanji'], $this->inputPatterns['kanji']),
            'eisuKigoAndKanji' => array($this->inputPatterns['eisuKigo'], $this->inputPatterns['kanji']),
            'kanjiAndEisuKigo' => array($this->inputPatterns['kanji'], $this->inputPatterns['eisuKigo'])
        );

        // 入力エラーにならない入力パターン
        $validValues = array(
            $inputPatterns['eisuKigoAndEisuKigo'],
            $inputPatterns['kanjiAndKanji']
        );

        // 入力エラーにならない入力パターン
        $invalidValues = array(
            $inputPatterns['eisuKigoAndKanji'],
            $inputPatterns['kanjiAndEisuKigo']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target1'])->value());
        $this->assertEquals('', $this->byCssSelector($selectors['target2'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // 入力エラーの場合のテスト
        foreach ($invalidValues as $values) {
            $elements[0] = $this->byCssSelector($selectors['target1']);
            $elements[1] = $this->byCssSelector($selectors['target2']);
            $elements[0]->clear();
            $elements[1]->clear();
            $elements[0]->value($values[0]);
            $elements[1]->value($values[1]);
            $this->inputRequiredField();
            $this->submitInputForm();
            $this->assertContains($this->globalErrorMessage, $this->byCssSelector('#content')->text());
            $this->assertEquals($errorMessage, $this->byCssSelector('#content ul li')->text());
            $this->assertEquals($errorMessage, $this->byCssSelector('#content table tr td div.error')->text());
            $this->assertEquals($values[0], $this->byCssSelector($selectors['target1'])->value());
            $this->assertEquals($values[1], $this->byCssSelector($selectors['target2'])->value());
        }

        // 入力エラーにならない場合のテスト
        foreach ($validValues as $values) {
            $this->url('');
            $elements[0] = $this->byCssSelector($selectors['target1']);
            $elements[1] = $this->byCssSelector($selectors['target2']);
            $elements[0]->value($values[0]);
            $elements[1]->value($values[1]);
            $this->inputRequiredField();
            $this->submitInputForm();
            $this->assertEquals($this->confirmPageTitle, $this->title());
            $this->assertContains($values[0], $this->byCssSelector('#content table')->text());
            $this->assertContains($values[1], $this->byCssSelector('#content table')->text());

            // 入力画面に戻る
            $this->returnInputPage();
            $this->assertEquals($values[0], $this->byCssSelector($selectors['target1'])->value());
            $this->assertEquals($values[1], $this->byCssSelector($selectors['target2'])->value());
        }
    }

    /**
     * URLの入力チェックのテスト
     *
     * @todo 今は日本語ドメインはエラーになるが、日本語ドメインの場合もエラーにならないように判別ロジックを修正したい
     * @todo サブドメインにアンダースコアが入る場合にエラーにならない。判別ロジックの修正が必要
     */
    public function testUrlField()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="URL"]',
            'option' => 'input[type="hidden"][name="url[]"][value="URL"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_URL;

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->urlInputPatterns['exampleCom'],
            $this->urlInputPatterns['exampleComLastCharacterSlash'],
            $this->urlInputPatterns['exampleComSsl'],
            $this->urlInputPatterns['exampleComWwwSubdomain'],
            $this->urlInputPatterns['exampleMuseum'],
            $this->urlInputPatterns['punycodeJapaneseDomain'],
            $this->urlInputPatterns['exampleComHyphenSubdomain'],
            $this->urlInputPatterns['exampleComUnserscoreSubdomain'],
            $this->urlInputPatterns['exampleA']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->urlInputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * 3以下の数字の入力チェックのテスト
     */
    public function testNumRangeFieldThreeOrLessNumbers()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="3以下の数字"]',
            'option' => 'input[type="hidden"][name="num_range[]"][value="3以下の数字 -3"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_NUM_RANGE;
        $errorMessage = str_replace('{範囲}', '0以上、3以下', $errorMessage);

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->numRangeInputPatterns['0'],
            $this->numRangeInputPatterns['1'],
            $this->numRangeInputPatterns['2'],
            $this->numRangeInputPatterns['3']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->numRangeInputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * 3以下の数字の入力チェックのテスト（数字以外を入力した場合）
     *
     * @todo エラーメッセージの「数値」「数字」の表記揺れをどちらかに合わせたい
     */
    public function testNumRangeFieldThreeOrLessNumbersNotNumber()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="3以下の数字"]',
            'option' => 'input[type="hidden"][name="num_range[]"][value="3以下の数字 -3"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_NUM_RANGE;
        $errorMessage = str_replace('{範囲}の数値', '数字', $errorMessage);

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->numRangeNotNumberInputPatterns, array(), $selectors['target'], $errorMessage);
    }

    /**
     * 3以上の数字の入力チェックのテスト
     */
    public function testNumRangeFieldThreeOrMoreNumbers()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="3以上の数字"]',
            'option' => 'input[type="hidden"][name="num_range[]"][value="3以上の数字 3-"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_NUM_RANGE;
        $errorMessage = str_replace('{範囲}', '3以上', $errorMessage);

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->numRangeInputPatterns['3'],
            $this->numRangeInputPatterns['4'],
            $this->numRangeInputPatterns['5'],
            $this->numRangeInputPatterns['6'],
            $this->numRangeInputPatterns['7'],
            $this->numRangeInputPatterns['8'],
            $this->numRangeInputPatterns['9'],
            $this->numRangeInputPatterns['10'],
            $this->numRangeInputPatterns['11'],
            $this->numRangeInputPatterns['12'],
            $this->numRangeInputPatterns['13'],
            $this->numRangeInputPatterns['32768'],
            $this->numRangeInputPatterns['65536'],
            $this->numRangeInputPatterns['2147483648'],
            $this->numRangeInputPatterns['4294967296']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->numRangeInputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * 3以上の数字の入力チェックのテスト（数字以外を入力した場合）
     *
     * @todo エラーメッセージの「数値」「数字」の表記揺れをどちらかに合わせたい
     */
    public function testNumRangeFieldThreeOrMoreNumbersNotNumber()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="3以上の数字"]',
            'option' => 'input[type="hidden"][name="num_range[]"][value="3以上の数字 3-"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_NUM_RANGE;
        $errorMessage = str_replace('{範囲}の数値', '数字', $errorMessage);

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->numRangeNotNumberInputPatterns, array(), $selectors['target'], $errorMessage);
    }

    /**
     * ちょうど3の数字の入力チェックのテスト
     */
    public function testNumRangeFieldThreeNumberFixed()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="ちょうど3の数字"]',
            'option' => 'input[type="hidden"][name="num_range[]"][value="ちょうど3の数字 3-3"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_NUM_RANGE;
        $errorMessage = str_replace('{範囲}', 'ちょうど3', $errorMessage);

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->numRangeInputPatterns['3']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->numRangeInputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * ちょうど3の数字の入力チェックのテスト（数字以外を入力した場合）
     *
     * @todo エラーメッセージの「数値」「数字」の表記揺れをどちらかに合わせたい
     */
    public function testNumRangeFieldThreeNumberFixedNotNumber()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="ちょうど3の数字"]',
            'option' => 'input[type="hidden"][name="num_range[]"][value="ちょうど3の数字 3-3"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_NUM_RANGE;
        $errorMessage = str_replace('{範囲}の数値', '数字', $errorMessage);

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->numRangeNotNumberInputPatterns, array(), $selectors['target'], $errorMessage);
    }

    /**
     * 1〜12の数字の入力チェックのテスト
     */
    public function testNumRangeFieldOneOrMoreAndTwelveOrLessNumbers()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="1〜12の数字"]',
            'option' => 'input[type="hidden"][name="num_range[]"][value="1〜12の数字 1-12"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_NUM_RANGE;
        $errorMessage = str_replace('{範囲}', '1以上、12以下', $errorMessage);

        // 入力エラーにならない入力パターン
        $validValues = array(
            $this->numRangeInputPatterns['1'],
            $this->numRangeInputPatterns['2'],
            $this->numRangeInputPatterns['3'],
            $this->numRangeInputPatterns['4'],
            $this->numRangeInputPatterns['5'],
            $this->numRangeInputPatterns['6'],
            $this->numRangeInputPatterns['7'],
            $this->numRangeInputPatterns['8'],
            $this->numRangeInputPatterns['9'],
            $this->numRangeInputPatterns['10'],
            $this->numRangeInputPatterns['11'],
            $this->numRangeInputPatterns['12']
        );

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->numRangeInputPatterns, $validValues, $selectors['target'], $errorMessage);
    }

    /**
     * 1〜12の数字の入力チェックのテスト（数字以外を入力した場合）
     *
     * @todo エラーメッセージの「数値」「数字」の表記揺れをどちらかに合わせたい
     */
    public function testNumRangeFieldOneOrMoreAndTwelveOrLessNumbersNotNumber()
    {
        $this->url('');

        $selectors = array(
            'target' => 'input[type="text"][name="1〜12の数字"]',
            'option' => 'input[type="hidden"][name="num_range[]"][value="1〜12の数字 1-12"]'
        );
        $targetNameValue = $this->byCssSelector($selectors['target'])->attribute('name');
        $errorMessage = $targetNameValue . ERROR_NUM_RANGE;
        $errorMessage = str_replace('{範囲}の数値', '数字', $errorMessage);

        // フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selectors['target'])->value());
        $this->assertInternalType('object', $this->byCssSelector($selectors['option']));

        // テストの実行
        $this->inputTest($this->numRangeNotNumberInputPatterns, array(), $selectors['target'], $errorMessage);
    }

    /**
     * 郵便番号（数値とハイフンを含む8文字）の入力チェックのテスト
     *
     * @todo このテストはいらない気がするので、一旦無しにする
     */
}
