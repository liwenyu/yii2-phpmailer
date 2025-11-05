<?php

/**
 * Yii2 PHPMailer Mailer Component
 * 
 * 将 PHPMailer 封装成 Yii2 邮件发送组件
 * 支持使用 Yii::$app->mail->send() 方式发送邮件
 * 
 * @author liwenyu
 * @since 1.0.0
 */

namespace liwenyu\phpmailer;

use Yii;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use GuzzleHttp\Client;

/**
 * Mailer 类
 * 
 * 封装 PHPMailer 为 Yii2 邮件发送组件
 */
class Mailer extends BaseMailer
{
    /**
     * @var string 消息类名
     */
    public $messageClass = 'liwenyu\phpmailer\Message';

    /**
     * @var array PHPMailer 配置
     * 支持以下配置：
     * - 'useSmtp': bool, 是否使用 SMTP
     * - 'host': string, SMTP 服务器
     * - 'port': int, SMTP 端口
     * - 'encryption': string, 加密方式 (tls, ssl)
     * - 'username': string, SMTP 用户名
     * - 'password': string, SMTP 密码
     * - 'authType': string, 认证类型 (XOAUTH2, LOGIN, PLAIN)
     * - 'useGraphAPI': bool, 是否使用 Microsoft Graph API（推荐）
     * - 'graphApiConfig': array, Graph API 配置（clientId, clientSecret, tenantId, userEmail）
     */
    public $phpmailerConfig = [];

    /**
     * @var bool 是否使用 Microsoft Graph API
     */
    public $useGraphAPI = false;

    /**
     * @var array Microsoft Graph API 配置
     */
    public $graphApiConfig = [];

    /**
     * @var PHPMailer PHPMailer 实例
     */
    private $_phpmailer;

    /**
     * @var string 访问令牌缓存
     */
    private $_accessToken;

    /**
     * @var int 令牌过期时间
     */
    private $_tokenExpiresAt;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // 如果使用 Graph API，检查配置
        if ($this->useGraphAPI || !empty($this->graphApiConfig)) {
            $this->useGraphAPI = true;
            if (empty($this->graphApiConfig['clientId']) || 
                empty($this->graphApiConfig['clientSecret']) || 
                empty($this->graphApiConfig['tenantId'])) {
                throw new InvalidConfigException('使用 Microsoft Graph API 时，必须配置 clientId, clientSecret 和 tenantId');
            }
        }
    }

    /**
     * 获取 PHPMailer 实例
     * 
     * @return PHPMailer
     */
    public function getPHPMailer()
    {
        if ($this->_phpmailer === null) {
            $this->_phpmailer = $this->createPHPMailer();
        }
        return $this->_phpmailer;
    }

    /**
     * 创建 PHPMailer 实例
     * 
     * @return PHPMailer
     */
    protected function createPHPMailer()
    {
        $mail = new PHPMailer(true);

        // 如果使用 Graph API，不需要配置 SMTP
        if (!$this->useGraphAPI) {
            // 配置 SMTP
            if (!empty($this->phpmailerConfig)) {
                $mail->isSMTP();
                
                if (isset($this->phpmailerConfig['host'])) {
                    $mail->Host = $this->phpmailerConfig['host'];
                }
                
                if (isset($this->phpmailerConfig['port'])) {
                    $mail->Port = $this->phpmailerConfig['port'];
                }
                
                if (isset($this->phpmailerConfig['encryption'])) {
                    $encryption = strtoupper($this->phpmailerConfig['encryption']);
                    if ($encryption === 'TLS') {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    } elseif ($encryption === 'SSL') {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    }
                }
                
                if (isset($this->phpmailerConfig['username'])) {
                    $mail->Username = $this->phpmailerConfig['username'];
                }
                
                if (isset($this->phpmailerConfig['password'])) {
                    $mail->Password = $this->phpmailerConfig['password'];
                }
                
                if (isset($this->phpmailerConfig['authType'])) {
                    $mail->AuthType = $this->phpmailerConfig['authType'];
                }
                
                if (isset($this->phpmailerConfig['SMTPAuth'])) {
                    $mail->SMTPAuth = $this->phpmailerConfig['SMTPAuth'];
                }
            }
        }

        return $mail;
    }

    /**
     * @inheritdoc
     */
    protected function sendMessage($message)
    {
        if ($this->useGraphAPI) {
            return $this->sendViaGraphAPI($message);
        } else {
            return $this->sendViaSMTP($message);
        }
    }

    /**
     * 通过 SMTP 发送邮件
     * 
     * @param Message $message
     * @return bool
     */
    protected function sendViaSMTP($message)
    {
        $mail = $this->getPHPMailer();
        
        try {
            // 设置邮件内容
            $message->applyToPHPMailer($mail);
            
            // 发送邮件
            return $mail->send();
            
        } catch (PHPMailerException $e) {
            throw new \yii\base\Exception('邮件发送失败: ' . $mail->ErrorInfo);
        }
    }

    /**
     * 通过 Microsoft Graph API 发送邮件
     * 
     * @param Message $message
     * @return bool
     */
    protected function sendViaGraphAPI($message)
    {
        // 获取访问令牌
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            throw new \yii\base\Exception('无法获取 Microsoft OAuth 访问令牌');
        }

        // 获取发件人邮箱
        $fromEmail = $this->graphApiConfig['userEmail'] ?? null;
        if (!$fromEmail) {
            $from = $message->getFrom();
            if (is_array($from)) {
                $fromEmail = array_keys($from)[0];
            } else {
                $fromEmail = $from;
            }
        }

        // 构建邮件数据
        $mail = $this->getPHPMailer();
        $message->applyToPHPMailer($mail);

        $emailData = [
            'message' => [
                'subject' => $mail->Subject,
                'body' => [
                    'contentType' => $mail->isHTML() ? 'HTML' : 'Text',
                    'content' => $mail->Body
                ],
                'toRecipients' => [],
            ],
            'saveToSentItems' => true
        ];

        // 添加收件人
        foreach ($mail->getToAddresses() as $address) {
            $emailData['message']['toRecipients'][] = [
                'emailAddress' => [
                    'address' => $address[0]
                ]
            ];
        }

        // 添加抄送
        if (!empty($mail->getCcAddresses())) {
            $emailData['message']['ccRecipients'] = [];
            foreach ($mail->getCcAddresses() as $address) {
                $emailData['message']['ccRecipients'][] = [
                    'emailAddress' => [
                        'address' => $address[0]
                    ]
                ];
            }
        }

        // 添加密送
        if (!empty($mail->getBccAddresses())) {
            $emailData['message']['bccRecipients'] = [];
            foreach ($mail->getBccAddresses() as $address) {
                $emailData['message']['bccRecipients'][] = [
                    'emailAddress' => [
                        'address' => $address[0]
                    ]
                ];
            }
        }

        // 发送邮件
        try {
            $client = new Client([
                'base_uri' => 'https://graph.microsoft.com/v1.0/',
                'timeout' => 30,
            ]);

            $response = $client->post("users/{$fromEmail}/sendMail", [
                'json' => $emailData,
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ]
            ]);

            return $response->getStatusCode() === 202;
            
        } catch (\Exception $e) {
            throw new \yii\base\Exception('通过 Graph API 发送邮件失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取 Microsoft OAuth 2.0 访问令牌
     * 
     * @return string|false
     */
    protected function getAccessToken()
    {
        // 检查令牌是否仍然有效
        if ($this->_accessToken && $this->_tokenExpiresAt && time() < $this->_tokenExpiresAt - 60) {
            return $this->_accessToken;
        }

        // 获取新令牌
        $client = new Client([
            'base_uri' => "https://login.microsoftonline.com/{$this->graphApiConfig['tenantId']}/",
            'timeout' => 30,
        ]);

        try {
            $response = $client->post('oauth2/v2.0/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->graphApiConfig['clientId'],
                    'client_secret' => $this->graphApiConfig['clientSecret'],
                    'scope' => 'https://graph.microsoft.com/.default'
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            
            if ($response->getStatusCode() === 200 && isset($data['access_token'])) {
                $this->_accessToken = $data['access_token'];
                $this->_tokenExpiresAt = time() + ($data['expires_in'] ?? 3600);
                return $this->_accessToken;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Yii::error('获取 Microsoft OAuth 令牌失败: ' . $e->getMessage());
            return false;
        }
    }
}
