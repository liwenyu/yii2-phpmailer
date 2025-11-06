<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../vendor/autoload.php';

// 加载 Yii2 框架
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

echo "开始测试...\n";

try {
    $config = require __DIR__ . '/config-example.php';
    echo "配置加载成功\n";
    
    $app = new \yii\console\Application($config);
    echo "Yii 应用创建成功\n";
    

    // 检查是否使用 SMTP
    $useSmtp = !$app->mail->useMicrosoft365;
    echo "邮件发送方式: " . ($useSmtp ? "SMTP" : "Microsoft 365") . "\n";
    
    if ($useSmtp) {
        echo "SMTP 服务器: " . ($app->mail->phpmailerConfig['host'] ?? 'N/A') . "\n";
        echo "SMTP 端口: " . ($app->mail->phpmailerConfig['port'] ?? 'N/A') . "\n";
        echo "SMTP 加密: " . ($app->mail->phpmailerConfig['encryption'] ?? 'N/A') . "\n";
        echo "SMTP 用户名: " . ($app->mail->phpmailerConfig['username'] ?? 'N/A') . "\n";
        
        if (($app->mail->phpmailerConfig['password'] ?? '') === 'YOUR_APP_PASSWORD') {
            echo "⚠️  警告: 请先在配置文件中设置应用密码！\n";
            echo "   应用密码不是普通密码，需要在 Microsoft 账户中生成\n";
        }
    } else {
        // 显示 Microsoft 365 配置信息
        $m365Config = $app->mail->microsoft365Config;
        echo "Microsoft 365 配置:\n";
        echo "  - 租户ID: " . ($m365Config['tenantId'] ?? 'N/A') . "\n";
        echo "  - 客户端ID: " . (isset($m365Config['clientId']) ? substr($m365Config['clientId'], 0, 8) . '...' : 'N/A') . "\n";
        echo "  - 发件人邮箱: " . ($m365Config['username'] ?? 'N/A') . "\n";
        echo "  - API 端点: https://graph.microsoft.com/v1.0\n";
    }
    
    // 根据配置选择发件人
    $fromEmail = $useSmtp ? 'liwenyu66@126.com' : ($app->mail->microsoft365Config['username'] ?? 'zhangyu@sruntech.onmicrosoft.com');
    $toEmail = 'lwy@srun.com';
    
    echo "\n正在发送邮件...\n";
    echo "发件人: $fromEmail\n";
    echo "收件人: $toEmail\n";
    
    $result = $app->mail->compose()
        ->setFrom($fromEmail)
        ->setTo($toEmail)
        ->setSubject('Yii2 PHPMailer ' . ($useSmtp ? 'SMTP' : 'Microsoft 365') . ' 测试 - ' . date('Y-m-d H:i:s'))
        ->setHtmlBody('<h2>Yii2 PHPMailer ' . ($useSmtp ? 'SMTP' : 'Microsoft 365') . ' 测试</h2><p>这是一封通过 <strong>Yii2 PHPMailer</strong> 使用 <strong>' . ($useSmtp ? 'SMTP' : 'Microsoft 365') . '</strong> 方式发送的测试邮件。</p><p>发送方式: ' . ($useSmtp ? 'SMTP (126 邮箱)' : 'Microsoft 365') . '</p><p>发送时间：' . date('Y-m-d H:i:s') . '</p>')
        ->send();
    
    if ($result) {
        echo "✅ 邮件发送成功！\n";
    } else {
        echo "❌ 邮件发送失败\n";
        if ($useSmtp) {
            echo "\n💡 提示:\n";
            echo "   - 如果失败，请检查应用密码是否正确\n";
            echo "   - 应用密码需要在 Microsoft 账户安全设置中生成\n";
            echo "   - 或者可以切换到 Microsoft 365 方式（更稳定）\n";
        } else {
            echo "\n💡 提示:\n";
            echo "   - 如果失败，请检查 Azure 应用权限配置\n";
            echo "   - 确认管理员已同意 Mail.Send 权限\n";
            echo "   - 检查网络连接和访问令牌获取\n";
        }
    }

    echo "\n测试完成\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "堆栈:\n" . $e->getTraceAsString() . "\n";
}