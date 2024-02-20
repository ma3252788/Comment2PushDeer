
<?php
/**
 * PushDeer推送评论通知
 *
 * @package Comment2PushDeer
 * @author 马春杰
 * @version 1.0.0
 * @link https://www.machunjie.com/opensource/1662.html
 * @link_gitee https://gitee.com/public_sharing/Comment2PushDeer
 * @link_github https://github.com/ma3252788/Comment2PushDeer
 */
class Comment2PushDeer_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
    
        Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('Comment2PushDeer_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_Feedback')->trackback = array('Comment2PushDeer_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_XmlRpc')->pingback = array('Comment2PushDeer_Plugin', 'sc_send');
        
        return _t('请配置此插件的 PDKEY, 以使您的PushDeer推送生效');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $key = new Typecho_Widget_Helper_Form_Element_Text('PDKEY', NULL, NULL, _t('PDKEY'), _t('PDKEY 需要在 <a href="http://pushdeer.com/">PushDeer官网</a> 注册<br />'));
        $form->addInput($key->addRule('required', _t('您必须填写一个正确的 PDKEY')));
    }
    
    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * PushDeer推送
     *
     * @access public
     * @param array $comment 评论结构
     * @param Typecho_Widget $post 被评论的文章
     * @return void
     */
    public static function sc_send($comment, $post)
    {
        if ($comment['status'] == 'approved') {
            // 发送通知逻辑
        
            $options = Typecho_Widget::widget('Widget_Options');
    
            $PDKEY = $options->plugin('Comment2PushDeer')->PDKEY;
    
            $text = "有新评论啦";
            $desp = "**".$comment['author']."** 在你的博客中说到：\n\n > ".$comment['text'];
    
    
            $postdata = http_build_query(
                array(
                    'text' => $text,
                    'desp' => $desp,
                    'pushkey' => $PDKEY,
                    )
                );
    
            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                    )
                );
            $context  = stream_context_create($opts);
            
            $result = file_get_contents('https://api2.pushdeer.com/message/push', false, $context);
            
            return  $comment;
            
        }
        
    }
}
