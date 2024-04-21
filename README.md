![ReadmeHeader](/public/images/ReadmeHeader.png)

# 概要

[Tasukeai](https://tasukeai.icu)のバックエンドのリポジトリ。

# 環境構築手順

## 1. GitHub からプロジェクトをクローン

GitHub リポジトリからプロジェクトのソースコードをローカルにクローンする。

```
git clone https://github.com/dream-theater-web-team/tasukeai-api.git
```

## 2. プロジェクトディレクトリに移動

クローンしたリポジトリのディレクトリに移動する。

```
cd tasukeai-api
```

## 3. Composer による依存パッケージをインストール

PHP のパッケージ管理ツールである Composer を使用して、プロジェクトで定義された依存関係をインストールする。`vendor`ディレクトリがプロジェクト内に作成される。

```
composer install
```

## 4. 環境変数ファイルを作成

`.env.example`ファイルをコピーして、`.env`ファイルを新規作成する。

```
cp .env.example .env
```

## 5. 環境変数の設定

`.env`ファイルを修正する。特に、データベース関連の設定は必須である。自身の環境に合わせて、適切な値に設定する。

## 6. アプリケーションキーを生成

Laravel アプリケーションキーを生成し、`.env`ファイル内の`APP_KEY`に設定する。

```
php artisan key:generate
```

## 7. マイグレーションを実行

マイグレーションを実行して、データベース内に必要なテーブルを作成する。

```
php artisan migrate
```

## 8. シーダを実行

シーダを実行して、データベースに初期値を設定する。

```
php artisan db:seed
```

## 9. 開発サーバー起動

Laravel の開発サーバーを起動し、アプリケーションをローカル環境で実行する。Laravel は API サーバーとして機能することを想定しているため、コマンド実行後に表示される URL（例: http://127.0.0.1:8000）に直接アクセスする必要はない。

```
php artisan serve
```
