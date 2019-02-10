# WP Content Framework (Custom post module)

[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: >=5.6](https://img.shields.io/badge/PHP-%3E%3D5.6-orange.svg)](http://php.net/)
[![WordPress: >=3.9.3](https://img.shields.io/badge/WordPress-%3E%3D3.9.3-brightgreen.svg)](https://wordpress.org/)

[WP Content Framework](https://github.com/wp-content-framework/core) のモジュールです。

# 要件
- PHP 5.6 以上
- WordPress 3.9.3 以上

# インストール

``` composer require wp-content-framework/custom_post ```  

## 依存モジュール
* [presenter](https://github.com/wp-content-framework/presenter)  
* [db](https://github.com/wp-content-framework/db) 
* [session](https://github.com/wp-content-framework/session) 
* [admin](https://github.com/wp-content-framework/admin)  

## 基本設定
- configs/config.php  

|設定値|説明|
|---|---|
|prior_default|nullableかつdefaultが設定されているカラムの値がない場合にdefaultを優先するかどうか \[default  = false]| 

## カスタム投稿タイプの追加
今後追加予定

# Author

[technote-space](https://github.com/technote-space)
