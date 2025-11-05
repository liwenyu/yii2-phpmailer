<?php

/**
 * Yii2 PHPMailer 使用示例
 * 
 * 演示如何使用 Yii::$app->mail->send() 发送邮件
 */

require_once '../vendor/autoload.php';

// 加载 Yii2 框架
require_once __DIR__ . '/../vendor/yiisoft/yii2/BaseYii.php';

use Yii;

// 加载配置
$config = require __DIR__ . '/config-example.php';

// 创建 Yii 应用
$app = new \yii\console\Application($config);

echo "=== Yii2 PHPMailer 使用示例 ===\n";
echo "使用 Yii::\$app->mail->send() 发送邮件\n";
echo "=====================================\n\n";

try {
    // 示例1: 发送简单邮件
    echo "=== 示例1: 发送简单邮件 ===\n";
    $message1 = Yii::$app->mail->compose()
        ->setFrom('zhangyu@sruntech.onmicrosoft.com')
        ->setTo('liwenyu66@126.com')
        ->setSubject('Yii2 PHPMailer 测试 - ' . date('Y-m-d H:i:s'))
        ->setTextBody('这是一封通过 Yii2 PHPMailer 组件发送的简单测试邮件。')
        ->setHtmlBody('<h2>Yii2 PHPMailer 测试</h2><p>这是一封通过 <strong>Yii2 PHPMailer</strong> 组件发送的简单测试邮件。</p><p>发送时间：' . date('Y-m-d H:i:s') . '</p>');
    
    $result1 = Yii::$app->mail->send($message1);
    echo "发送结果: " . ($result1 ? "✅ 成功" : "❌ 失败") . "\n\n";

    // 示例2: 发送带附件的邮件
    echo "=== 示例2: 发送带附件的邮件 ===\n";
    
    // 创建临时附件文件
    $attachmentFile = sys_get_temp_dir() . '/test_attachment.txt';
    file_put_contents($attachmentFile, '这是一个测试附件的内容。\n发送时间：' . date('Y-m-d H:i:s'));
    
    $message2 = Yii::$app->mail->compose()
        ->setFrom('zhangyu@sruntech.onmicrosoft.com')
        ->setTo('liwenyu66@126.com')
        ->setSubject('Yii2 PHPMailer 附件测试 - ' . date('Y-m-d H:i:s'))
        ->setHtmlBody('<h2>带附件的邮件测试</h2><p>这是一封包含附件的测试邮件。</p>')
        ->attach($attachmentFile);
    
    $result2 = Yii::$app->mail->send($message2);
    echo "发送结果: " . ($result2 ? "✅ 成功" : "❌ 失败") . "\n\n";
    
    // 清理临时文件
    if (file_exists($attachmentFile)) {
        unlink($attachmentFile);
    }

    // 示例3: 发送带抄送和密送的邮件
    echo "=== 示例3: 发送带抄送和密送的邮件 ===\n";
    $message3 = Yii::$app->mail->compose()
        ->setFrom('zhangyu@sruntech.onmicrosoft.com')
        ->setTo('liwenyu66@126.com')
        ->setCc('liwenyu66@126.com')  // 抄送（示例）
        ->setSubject('Yii2 PHPMailer 抄送密送测试 - ' . date('Y-m-d H:i:s'))
        ->setHtmlBody('<h2>抄送和密送测试</h2><p>这是一封包含抄送和密送的测试邮件。</p>');
    
    $result3 = Yii::$app->mail->send($message3);
    echo "发送结果: " . ($result3 ? "✅ 成功" : "❌ 失败") . "\n\n";

    // 示例4: 发送纯文本邮件
    echo "=== 示例4: 发送纯文本邮件 ===\n";
    $message4 = Yii::$app->mail->compose()
        ->setFrom('zhangyu@sruntech.onmicrosoft.com')
        ->setTo('liwenyu66@126.com')
        ->setSubject('Yii2 PHPMailer 纯文本测试 - ' . date('Y-m-d H:i:s'))
        ->setTextBody('这是一封纯文本格式的测试邮件。\n发送时间：' . date('Y-m-d H:i:s'));
    
    $result4 = Yii::$app->mail->send($message4);
    echo "发送结果: " . ($result4 ? "✅ 成功" : "❌ 失败") . "\n\n";

    // 示例5: 发送带嵌入图片的 HTML 邮件
    echo "=== 示例5: 发送带嵌入图片的 HTML 邮件 ===\n";
    $message5 = Yii::$app->mail->compose()
        ->setFrom('zhangyu@sruntech.onmicrosoft.com')
        ->setTo('liwenyu66@126.com')
        ->setSubject('Yii2 PHPMailer 嵌入图片测试 - ' . date('Y-m-d H:i:s'));
    
    // 创建简单的 Base64 图片（1x1 透明 PNG）
    $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    $cid = $message5->embedContent($imageData, [
        'fileName' => 'test.png',
        'contentType' => 'image/png'
    ]);
    
    $message5->setHtmlBody('<h2>嵌入图片测试</h2><p>这是一封包含嵌入图片的测试邮件。</p><img src="cid:' . $cid . '" alt="测试图片">');
    
    $result5 = Yii::$app->mail->send($message5);
    echo "发送结果: " . ($result5 ? "✅ 成功" : "❌ 失败") . "\n\n";

    // 总结
    echo "=====================================\n";
    echo "📊 发送结果总结：\n";
    echo "- 示例1 (简单邮件): " . ($result1 ? "✅" : "❌") . "\n";
    echo "- 示例2 (带附件): " . ($result2 ? "✅" : "❌") . "\n";
    echo "- 示例3 (抄送密送): " . ($result3 ? "✅" : "❌") . "\n";
    echo "- 示例4 (纯文本): " . ($result4 ? "✅" : "❌") . "\n";
    echo "- 示例5 (嵌入图片): " . ($result5 ? "✅" : "❌") . "\n";
    
    echo "\n🎉 所有示例执行完成！\n";
    echo "\n💡 使用方式：\n";
    echo "Yii::\$app->mail->compose()\n";
    echo "    ->setFrom('sender@example.com')\n";
    echo "    ->setTo('recipient@example.com')\n";
    echo "    ->setSubject('邮件主题')\n";
    echo "    ->setHtmlBody('<h1>邮件内容</h1>')\n";
    echo "    ->send();\n";

} catch (Exception $e) {
    echo "❌ 执行过程中发生错误: " . $e->getMessage() . "\n";
    echo "错误文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "错误堆栈:\n" . $e->getTraceAsString() . "\n";
} finally {
    if (isset($app)) {
        $app->end();
    }
}
