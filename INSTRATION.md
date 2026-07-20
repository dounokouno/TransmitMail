# TransmitMail 使い方ガイド

このドキュメントは、TransmitMail の導入からカスタマイズ、バリデーション設定までの基本的な使い方をまとめたガイドです。

## 1. 導入編

### ファイルのダウンロード

公式サイト（GitHub）から最新の ZIP ファイルをダウンロードし、解凍します。

[https://github.com/dounokouno/TransmitMail](https://github.com/dounokouno/TransmitMail)

### 主要なファイル・フォルダの概要

ファイル・フォルダ構成図および各ファイルの概要は以下の通りです。

```text
# テンプレート関連（必要に応じて編集してください）
+ input.html               → 入力画面テンプレート
+ confirm.html             → 確認画面テンプレート（ループ出力用）
+ finish.html              → 完了画面テンプレート
+ error.html               → エラー画面テンプレート

# 設定ファイル関連（必要に応じて編集してください。config/[705] は推奨パーミッションです）
+ config/ [705]
    + .htaccess            → アクセス制御ファイル
    + config.yml.sample    → YAML 形式の設定ファイルのサンプル
    + config.json.sample   → JSON 形式の設定ファイルのサンプル
    + config.php.sample    → PHP 形式の設定ファイルのサンプル
    + mail_body.txt        → 管理者宛送信メール本文テンプレート
    + mail_auto_reply_body.txt → 自動返信メール本文テンプレート

# プログラム関連（lib/[705], log/[707], tmp/[707] は推奨パーミッションです）
+ index.php                → TransmitMail実行ファイル
+ lib/ [705]
    + qdmail.php           → メール送信ライブラリ
    + qdsmtp.php           → メール送信ライブラリ
    + Spyc.php             → YAMLパースライブラリ
    + tinyTemplate.php     → テンプレートエンジン
    + TransmitMail.php     → TransmitMailクラス
+ log/ [707]               → ログ出力ディレクトリ
    + .htaccess            → アクセス制御ファイル
+ tmp/ [707]               → 一時保存用ディレクトリ
    + .htaccess            → アクセス制御ファイル
```

### 設置方法

- 「テンプレート関連（HTMLファイルなど）」と「設定ファイル（`config/` 内）」を適宜修正後、「プログラム関連（`index.php`、`lib/` など）」と合わせてレンタルサーバーなどにアップロードします。
- パーミッションは、上記のフォルダ構成にある3桁の数字（`[705]`、`[707]` など）を参考に設定をしてください。

## 2. 実践編：基本設定

ここでは設定ファイルは YAML ファイルを利用します。それ以外のファイル形式（JSON や PHP）を利用する場合は適宜読み替えてください。

### config フォルダの設定

1. 設定ファイルには YAML, JSON, PHP の3種類がありますが、いずれか**どれか一つを選択して**利用します（一般的には YAML が推奨されます）。
2. `config.yml.sample` を `config.yml` にリネームします。（JSON を利用する場合は `config.json`、PHP を利用する場合は `config.php` にリネームします）
3. リネームした設定ファイルを編集し、受信メールアドレスや件名を案件に合わせて変更します。
4. `mail_body.txt`（管理者宛）と `mail_auto_reply_body.txt`（自動返信）の本文を適宜修正します。

### ファイル添付の有効化（必要な場合）

デフォルトではファイル添付が無効になっています。利用する場合は `config.yml` に `file: true` を追加してください。

#### 記述例

```yaml
config:
    # その他の設定
    file: true
```

### その他の設定

プログラム上のほぼ全ての設定は設定ファイルで変更可能です。変更可能な設定は `lib/TransmitMail.php` の該当行、または後述のチェックモードで確認できます。

[https://github.com/dounokouno/TransmitMail/blob/master/lib/TransmitMail.php#L88](https://github.com/dounokouno/TransmitMail/blob/master/lib/TransmitMail.php#L88)

## 3. テンプレートの編集

### 入力画面 (input.html)

フォームの項目を案件に合わせて追加、修正します。送信後の画面でフォーム部分を表示したい場合は `form` タグに `action="./#id名"` を追加してください。

### 確認画面 (confirm.html, confirm_kobetsu.html)

- **ループ出力 (confirm.html)**: 項目を自動で順番に出力します。
- **個別出力 (confirm_kobetsu.html)**: 特定の項目を好きな順番で表示するようカスタマイズ可能です。利用する場合は `confirm_kobetsu.html` を `confirm.html` に置き換えて使用してください。

### 完了画面 (finish.html)

フォームのページに戻るリンクなどは `./` や `index.php` を指定します。

## 4. バリデーションの設定

TransmitMail では `input.html` 内に `hidden` フィールドを追加することでバリデーションを設定できます。 TransmitMail に実装されているすべてのバリデーションの設定は `input.html` に書かれているので、そちらを参考にしてください。

### 基本的な記述例

```html
<!-- 必須項目 -->
<input type="text" name="お名前" value="{$お名前}">
<input type="hidden" name="required[]" value="お名前">
{if:$required.お名前}
<div class="error">{$required.お名前}</div>
{/if:$required.お名前}
```

### 主なバリデーション種類

- **required[]**: 必須項目
- **email[]**: メールアドレス形式
- **match[]**: 一致チェック（例：`value="メールアドレス mail2"`）
- **hiragana[]**: ひらがな
- **zenkaku_katakana[]**: 全角カタカナ
- **num[]**: 数字
- **hankaku_eisu[]**: 半角英数字
- **url[]**: URL形式

## 5. その他・カスタマイズ

### ヘッダー・フッターの共通化

`{include:header.html}` のように記述することで、別ファイルをインクルードできます。

## 6. チェックモード

設定ファイルで `checkmode` を `1` （通常モード）または `2` （詳細モード）とし、 `http://{TransmitMail設置ディレクトリ}/index.php?checkmode` にアクセスするとチェックモードが表示されます。

チェックモードでは、各種設定内容やファイル、パーミッションなどに不備がないか確認できます。詳細モードでは各種設定値が確認できます。

※本番環境ではチェックモードは必ず無効にしてください（ PHP のバージョン情報や各種メールアドレスが表示されるため）。一時的に有効にする場合は、利用が終わったら必ず無効にしてください。

### 設定ファイルの記述例

```yaml
config:
    # その他の設定
    checkmode: 1 # または 2
```

## 7. エラーログ出力

何らかの理由でメールが送信できなかった場合や各種エラー発生時には、「log/」内に送信内容やエラー内容のログが出力されます。

---

## 参考

- [TransmitMail Wiki](https://github.com/dounokouno/TransmitMail/wiki)
- [TransmitMail 2 のカスタマイズ例のリンク集 #TransmitMail - Qiita](https://qiita.com/dounokouno/items/c76d6b7053200c476d6d)
- [TransmitMail でよく使いそうなパーツ #TransmitMail - Qiita](https://qiita.com/dounokouno/items/ad8a128a0bd9fd20c3d8)
