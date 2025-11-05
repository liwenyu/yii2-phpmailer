<?php

/**
 * Yii2 PHPMailer Message Component
 * 
 * 实现 Yii2 Message 接口，封装 PHPMailer 消息对象
 * 
 * @author liwenyu
 * @since 1.0.0
 */

namespace liwenyu\phpmailer;

use yii\mail\BaseMessage;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Message 类
 * 
 * 实现 Yii2 Message 接口，封装 PHPMailer
 */
class Message extends BaseMessage
{
    /**
     * @var array 邮件属性
     */
    private $_charset = 'utf-8';
    private $_from;
    private $_to = [];
    private $_cc = [];
    private $_bcc = [];
    private $_replyTo = [];
    private $_subject = '';
    private $_textBody = '';
    private $_htmlBody = '';
    private $_attachments = [];
    private $_embeddedFiles = [];

    /**
     * @inheritdoc
     */
    public function getCharset()
    {
        return $this->_charset;
    }

    /**
     * @inheritdoc
     */
    public function setCharset($charset)
    {
        $this->_charset = $charset;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFrom()
    {
        return $this->_from;
    }

    /**
     * @inheritdoc
     */
    public function setFrom($from)
    {
        $this->_from = $this->normalizeEmail($from);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTo()
    {
        return $this->_to;
    }

    /**
     * @inheritdoc
     */
    public function setTo($to)
    {
        $this->_to = $this->normalizeEmail($to);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCc()
    {
        return $this->_cc;
    }

    /**
     * @inheritdoc
     */
    public function setCc($cc)
    {
        $this->_cc = $this->normalizeEmail($cc);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBcc()
    {
        return $this->_bcc;
    }

    /**
     * @inheritdoc
     */
    public function setBcc($bcc)
    {
        $this->_bcc = $this->normalizeEmail($bcc);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReplyTo()
    {
        return $this->_replyTo;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($replyTo)
    {
        $this->_replyTo = $this->normalizeEmail($replyTo);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject)
    {
        $this->_subject = $subject;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTextBody($text)
    {
        $this->_textBody = $text;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTextBody()
    {
        return $this->_textBody;
    }

    /**
     * @inheritdoc
     */
    public function setHtmlBody($html)
    {
        $this->_htmlBody = $html;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHtmlBody()
    {
        return $this->_htmlBody;
    }

    /**
     * 添加附件
     * 
     * @param string $file 文件路径
     * @param array $options 选项
     * @return static
     */
    public function attach($file, array $options = [])
    {
        $this->_attachments[] = [
            'file' => $file,
            'name' => $options['fileName'] ?? basename($file),
            'encoding' => $options['encoding'] ?? 'base64',
            'type' => $options['contentType'] ?? '',
        ];
        return $this;
    }

    /**
     * 添加内容作为附件
     * 
     * @param string $content 内容
     * @param array $options 选项
     * @return static
     */
    public function attachContent($content, array $options = [])
    {
        $this->_attachments[] = [
            'content' => $content,
            'name' => $options['fileName'] ?? 'attachment',
            'encoding' => $options['encoding'] ?? 'base64',
            'type' => $options['contentType'] ?? 'application/octet-stream',
        ];
        return $this;
    }

    /**
     * 嵌入文件（用于 HTML 邮件中的图片）
     * 
     * @param string $file 文件路径
     * @param array $options 选项
     * @return string CID
     */
    public function embed($file, array $options = [])
    {
        $cid = $options['cid'] ?? 'cid_' . uniqid();
        $this->_embeddedFiles[] = [
            'file' => $file,
            'cid' => $cid,
            'name' => $options['fileName'] ?? basename($file),
            'type' => $options['contentType'] ?? '',
        ];
        return $cid;
    }

    /**
     * 嵌入内容
     * 
     * @param string $content 内容
     * @param array $options 选项
     * @return string CID
     */
    public function embedContent($content, array $options = [])
    {
        $cid = $options['cid'] ?? 'cid_' . uniqid();
        $this->_embeddedFiles[] = [
            'content' => $content,
            'cid' => $cid,
            'name' => $options['fileName'] ?? 'embedded',
            'type' => $options['contentType'] ?? 'image/png',
        ];
        return $cid;
    }

    /**
     * 将消息应用到 PHPMailer 实例
     * 
     * @param PHPMailer $mail
     */
    public function applyToPHPMailer($mail)
    {
        // 字符集
        $mail->CharSet = $this->_charset;

        // 发件人
        if ($this->_from) {
            if (is_array($this->_from)) {
                $email = array_keys($this->_from)[0];
                $name = $this->_from[$email];
                $mail->setFrom($email, $name ?: '');
            } else {
                $mail->setFrom($this->_from);
            }
        }

        // 收件人
        if ($this->_to) {
            if (is_array($this->_to)) {
                foreach ($this->_to as $email => $name) {
                    if (is_numeric($email)) {
                        $mail->addAddress($name);
                    } else {
                        $mail->addAddress($email, $name);
                    }
                }
            } else {
                $mail->addAddress($this->_to);
            }
        }

        // 抄送
        if ($this->_cc) {
            if (is_array($this->_cc)) {
                foreach ($this->_cc as $email => $name) {
                    if (is_numeric($email)) {
                        $mail->addCC($name);
                    } else {
                        $mail->addCC($email, $name);
                    }
                }
            } else {
                $mail->addCC($this->_cc);
            }
        }

        // 密送
        if ($this->_bcc) {
            if (is_array($this->_bcc)) {
                foreach ($this->_bcc as $email => $name) {
                    if (is_numeric($email)) {
                        $mail->addBCC($name);
                    } else {
                        $mail->addBCC($email, $name);
                    }
                }
            } else {
                $mail->addBCC($this->_bcc);
            }
        }

        // 回复地址
        if ($this->_replyTo) {
            if (is_array($this->_replyTo)) {
                foreach ($this->_replyTo as $email => $name) {
                    if (is_numeric($email)) {
                        $mail->addReplyTo($name);
                    } else {
                        $mail->addReplyTo($email, $name);
                    }
                }
            } else {
                $mail->addReplyTo($this->_replyTo);
            }
        }

        // 主题
        $mail->Subject = $this->_subject;

        // 邮件内容
        if (!empty($this->_htmlBody)) {
            $mail->isHTML(true);
            $mail->Body = $this->_htmlBody;
            if (!empty($this->_textBody)) {
                $mail->AltBody = $this->_textBody;
            }
        } else {
            $mail->isHTML(false);
            $mail->Body = $this->_textBody;
        }

        // 附件
        foreach ($this->_attachments as $attachment) {
            if (isset($attachment['file'])) {
                $mail->addAttachment($attachment['file'], $attachment['name']);
            } elseif (isset($attachment['content'])) {
                $mail->addStringAttachment($attachment['content'], $attachment['name'], 'base64', $attachment['type']);
            }
        }

        // 嵌入文件
        foreach ($this->_embeddedFiles as $embedded) {
            if (isset($embedded['file'])) {
                $mail->addEmbeddedImage($embedded['file'], $embedded['cid'], $embedded['name'], 'base64', $embedded['type']);
            } elseif (isset($embedded['content'])) {
                $mail->addStringEmbeddedImage($embedded['content'], $embedded['cid'], $embedded['name'], 'base64', $embedded['type']);
            }
        }
    }

    /**
     * 标准化邮箱地址
     * 
     * @param string|array $email
     * @return array|string
     */
    protected function normalizeEmail($email)
    {
        if (is_string($email)) {
            return $email;
        }
        
        if (is_array($email)) {
            $result = [];
            foreach ($email as $key => $value) {
                if (is_numeric($key)) {
                    $result[$value] = '';
                } else {
                    $result[$key] = $value;
                }
            }
            return $result;
        }
        
        return $email;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return $this->getSubject() . "\n" . $this->getTextBody();
    }
}
