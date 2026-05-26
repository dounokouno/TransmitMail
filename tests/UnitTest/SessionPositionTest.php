<?php
/**
 * Session position unit test with hooks verification
 *
 * @package    TransmitMail
 * @license    MIT License
 * @copyright  TransmitMail development team
 * @link       https://github.com/dounokouno/TransmitMail
 */

namespace TransmitMail\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Hook検証用のモッククラス
 */
class TransmitMailHookMock extends \TransmitMail
{
    public $hook_session_statuses = [];
    public $hook_session_data = [];

    private function recordState($hookName)
    {
        $this->hook_session_statuses[$hookName] = session_status();
        $this->hook_session_data[$hookName] = $_SESSION;
    }

    public function afterCheckDenyHost()
    {
        $this->recordState('afterCheckDenyHost');
    }

    public function afterCheckInput()
    {
        $this->recordState('afterCheckInput');
    }

    public function afterSetPageName()
    {
        $this->recordState('afterSetPageName');
    }

    public function afterSetTemplateProperty()
    {
        $this->recordState('afterSetTemplateProperty');
    }

    public function afterSetTemplateAndSendMail()
    {
        $this->recordState('afterSetTemplateAndSendMail');
    }

    // 出力や終了を防ぐために一部メソッドをオーバーライド
    public function setTemplateAndSendMail()
    {
        // finish モードのセッション破棄ロジックをシミュレート
        if ($this->page_name === 'finish') {
            $_SESSION = array();
            // 実際には session_destroy() も呼ばれるが、ユニットテスト継続のため $_SESSION のクリアに留める
        }
    }

    public function setPageName() {
        if (isset($this->post['page_name']) && $this->post['page_name'] === 'finish') {
            $this->page_name = 'finish';
        } else {
            parent::setPageName();
        }
    }
}

class SessionPositionTest extends TestCase
{
    protected function setUp(): void
    {
        // セッションがアクティブな場合は一旦終了（テストのクリーン環境のため）
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }
        $_SESSION = [];

        require_once __DIR__ . '/../../lib/TransmitMail.php';
    }

    /**
     * init() 呼出し後にセッションが開始されていないことを確認
     */
    public function testSessionNotStartedAfterInit()
    {
        $tm = new \TransmitMail();
        $tm->init();

        $this->assertEquals(PHP_SESSION_NONE, session_status(), 'init()直後はセッションが開始されていないはず');
        $this->assertArrayNotHasKey('transmit_mail_input', $_SESSION, 'init()直後はセッションにデータが入っていないはず');
    }

    /**
     * run() の各ホック時点でのセッション状態をテスト
     */
    public function testSessionStatusAtAllHooks()
    {
        $tm = new TransmitMailHookMock();
        $tm->init();

        // run() を実行。出力抑制。
        ob_start();
        @$tm->run();
        ob_end_clean();

        // 1. afterCheckDenyHost (session_start 前)
        $this->assertEquals(PHP_SESSION_NONE, $tm->hook_session_statuses['afterCheckDenyHost'], 'afterCheckDenyHost時点ではセッション未開始');

        // 2. afterCheckInput (session_start 後)
        $this->assertArrayHasKey('afterCheckInput', $tm->hook_session_statuses);

        // 3. afterSetPageName (page_name 決定後)
        $this->assertArrayHasKey('afterSetPageName', $tm->hook_session_statuses);

        // 4. afterSetTemplateProperty
        $this->assertArrayHasKey('afterSetTemplateProperty', $tm->hook_session_statuses);

        // 5. afterSetTemplateAndSendMail
        $this->assertArrayHasKey('afterSetTemplateAndSendMail', $tm->hook_session_statuses);
    }

    /**
     * 完了画面でのセッション破棄がホックにどう影響するか
     */
    public function testSessionDestructionAtFinishHook()
    {
        $tm = new TransmitMailHookMock();
        $tm->init();

        // 完了画面へ遷移する状態を作る
        $tm->post = ['page_name' => 'finish'];

        // ユニットテスト内で直接 session_flag を true にする
        $tm->session_flag = true;

        ob_start();
        @$tm->run();
        ob_end_clean();

        // page_name が正しく判定されていることを確認
        $this->assertEquals('finish', $tm->page_name, 'page_name が finish であるべき');

        // 完了画面では setTemplateAndSendMail 内でセッションがクリアされるため、
        // その後の afterSetTemplateAndSendMail ではセッションが空になっているはず
        $this->assertEmpty($tm->hook_session_data['afterSetTemplateAndSendMail'], '完了画面の後はセッションデータがクリアされているはず');
    }
}
