# Yii2 PHPMailer 组件

将 `phpmailer/phpmailer` 封装成 Yii2 邮件发送组件，支持使用 `Yii::$app->mail->send()` 方式发送邮件。

## 📁 文件结构

```
yii2-phpmailer/
├── src/
│   ├── Mailer.php      # Mailer 组件类
│   └── Message.php     # Message 消息类
├── config-example.php   # 配置示例
├── example.php         # 使用示例
└── README.md           # 说明文档
```

## 🚀 快速开始

### 1. 配置组件

在 Yii2 应用配置中添加邮件组件：

```php
'components' => [
    'mail' => [
        'class' => 'yii\phpmailer\Mailer',
        'useGraphAPI' => true,  // 使用 Microsoft Graph API
        'graphApiConfig' => [
            'clientId' => 'your-client-id',
            'clientSecret' => 'your-client-secret',
            'tenantId' => 'your-tenant-id',
            'userEmail' => 'your-email@yourdomain.com',
        ],
    ],
],
```

### 2. 使用方式

```php
use Yii;

// 发送简单邮件
Yii::$app->mail->compose()
    ->setFrom('sender@example.com')
    ->setTo('recipient@example.com')
    ->setSubject('邮件主题')
    ->setHtmlBody('<h1>邮件内容</h1>')
    ->send();

// 发送带附件的邮件
Yii::$app->mail->compose()
    ->setFrom('sender@example.com')
    ->setTo('recipient@example.com')
    ->setSubject('带附件的邮件')
    ->setHtmlBody('<p>这是一封带附件的邮件</p>')
    ->attach('/path/to/file.pdf')
    ->send();
```

## ⚙️ 配置选项

### Microsoft Graph API 配置（推荐）

```php
'mail' => [
    'class' => 'yii\phpmailer\Mailer',
    'useGraphAPI' => true,
    'graphApiConfig' => [
        'clientId' => 'your-client-id',
        'clientSecret' => 'your-client-secret',
        'tenantId' => 'your-tenant-id',
        'userEmail' => 'your-email@yourdomain.com',
    ],
],
```

### 传统 SMTP 配置

```php
'mail' => [
    'class' => 'yii\phpmailer\Mailer',
    'useGraphAPI' => false,
    'phpmailerConfig' => [
        'host' => 'smtp.office365.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'your-email@yourdomain.com',
        'password' => 'your-password',
        'SMTPAuth' => true,
    ],
],
```

## 📧 功能特性

- ✅ 完全兼容 Yii2 Mailer 接口
- ✅ 支持 Microsoft Graph API（推荐）
- ✅ 支持传统 SMTP 发送
- ✅ 支持 HTML 和纯文本邮件
- ✅ 支持附件和嵌入图片
- ✅ 支持抄送、密送、回复地址
- ✅ OAuth 2.0 自动令牌管理

## 🎯 API 使用

### 基本方法

```php
// 创建消息
$message = Yii::$app->mail->compose();

// 设置发件人
$message->setFrom('sender@example.com');
$message->setFrom('sender@example.com', '发件人名称');

// 设置收件人
$message->setTo('recipient@example.com');
$message->setTo(['user1@example.com', 'user2@example.com']);

// 设置抄送和密送
$message->setCc('cc@example.com');
$message->setBcc('bcc@example.com');

// 设置回复地址
$message->setReplyTo('reply@example.com');

// 设置主题和内容
$message->setSubject('邮件主题');
$message->setTextBody('纯文本内容');
$message->setHtmlBody('<h1>HTML 内容</h1>');

// 添加附件
$message->attach('/path/to/file.pdf');
$message->attachContent('附件内容', ['fileName' => 'file.txt']);

// 嵌入图片
$cid = $message->embed('/path/to/image.png');
$message->setHtmlBody('<img src="cid:' . $cid . '">');

// 发送邮件
Yii::$app->mail->send($message);
```

## 🔧 技术实现

### Mailer 类

- 继承自 `yii\mail\BaseMailer`
- 支持两种发送方式：
  - Microsoft Graph API（使用客户端凭据流）
  - 传统 SMTP

### Message 类

- 实现 `yii\mail\MessageInterface`
- 封装 PHPMailer 消息对象
- 提供链式调用接口

## 📝 注意事项

1. **Microsoft Graph API 方式**：

   - 使用客户端凭据流（Client Credentials Flow）
   - 不需要用户交互
   - 自动管理访问令牌

2. **SMTP 方式**：

   - 需要配置 SMTP 服务器信息
   - 支持 XOAUTH2（需要用户授权）

3. **配置要求**：
   - Azure 应用需要 `Mail.Send` 权限
   - 管理员需要同意权限

## 🎉 使用示例

查看 `example.php` 文件获取完整的使用示例。

## 📚 相关文档

- [PHPMailer 文档](https://github.com/PHPMailer/PHPMailer)
- [Yii2 Mailer 文档](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-mailing)
- [Microsoft Graph API 文档](https://docs.microsoft.com/en-us/graph/api/resources/mail-api-overview)
