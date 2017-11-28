<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Redis;
header('content-type:text/html;charset=utf-8');
class LoginController extends BaseController
{
    public function indexAction(Request $request)
    {
      //获取点击VP系统url中携带的token值
      $token = substr($request = $this->getRequest(),16,32);
      
      //判断是否从VP系统跳过的链接登陆
      $a = explode('http://',$request);
      $c=substr( $a['1'],0,14);
    
      //调用vp系统redis缓存中的用户信息
      $redis = new Redis();
      $redis->connect('115.182.90.204',16379);
      $redis->auth('vpplus');
      $get = $redis->hGetAll($token);
     
      $user = $this->getCurrentUser();

      if($c == 'vp.csii.com.cn' ){
        if ($user->isLogin()) {
         return $this->render('TopxiaWebBundle:Default:index.html.twig');
        }
    }else{
          $error = "亲,您现在的操作是非法请求,请从";
            return $this->render('TopxiaWebBundle:Default:error.html.twig', array('error' => $error));

    }



        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($this->getWebExtension()->isMicroMessenger() && $this->setting('login_bind.enabled', 0) && $this->setting('login_bind.weixinmob_enabled', 0)) {
            $inviteCode = $request->query->get('inviteCode');
            return $this->redirect($this->generateUrl('login_bind', array('type' => 'weixinmob', '_target_path' => $this->getTargetPath($request), 'inviteCode' => $inviteCode)));
        }

        return $this->render('TopxiaWebBundle:Login:index.html.twig', array(
            'last_username' => $get['LoginId'],
            //'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error' => $error,
            '_target_path' => $this->getTargetPath($request)
        ));

    }
    protected function getTestService()
    {
        return $this->getServiceKernel()->createService('Test.TestService');
    }

    protected function getBatchNotificationService()
    {
        return $this->getServiceKernel()->createService('User.BatchNotificationService');
    }

    public function ajaxAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Login:ajax.html.twig', array(
            '_target_path' => $this->getTargetPath($request)
        ));
    }

    public function checkEmailAction(Request $request)
    {
        $email = $request->query->get('value');
        $user  = $this->getUserService()->getUserByEmail($email);

        if ($user) {
            $response = array('success' => true, 'message' => $this->getServiceKernel()->trans('该Email地址可以登录'));
        } else {
            $response = array('success' => false, 'message' => $this->getServiceKernel()->trans('该Email地址尚未注册'));
        }

        return $this->createJsonResponse($response);
    }

    public function oauth2LoginsBlockAction($targetPath, $displayName = true)
    {
        $clients = OAuthClientFactory::clients();
        return $this->render('TopxiaWebBundle:Login:oauth2-logins-block.html.twig', array(
            'clients'     => $clients,
            'targetPath'  => $targetPath,
            'displayName' => $displayName
        ));
    }

    protected function getTargetPath($request)
    {
        if ($request->query->get('goto')) {
            $targetPath = $request->query->get('goto');
        } elseif ($request->getSession()->has('_target_path')) {
            $targetPath = $request->getSession()->get('_target_path');
        } else {
            $targetPath = $request->headers->get('Referer');
        }

        if ($targetPath == $this->generateUrl('login', array(), true)) {
            return $this->generateUrl('homepage');
        }

        $url = explode('?', $targetPath);

        if ($url[0] == $this->generateUrl('partner_logout', array(), true)) {
            return $this->generateUrl('homepage');
        }

        if ($url[0] == $this->generateUrl('password_reset_update', array(), true)) {
            $targetPath = $this->generateUrl('homepage', array(), true);
        }

        if (strpos($targetPath, '/app.php') === 0) {
            $targetPath = str_replace('/app.php', '', $targetPath);
        }

        return $targetPath;
    }

    protected function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }


}


	<div></div>
<div><input name="c" type="radio" value="1" onclick="change()"/>按钮A</div>
<div><input name="c" type="radio" value="2" onclick="change()"/>按钮B</div>
<div><input name="c" type="radio" value="2" onclick="change()"/>按钮C</div>
js代码：
function change(){
		

        var rad=document.getElementsByName("c");
        for(var i=0;i<rad.length;i++){
        if(rad[i].checked!=true){
        rad[i].parentNode.style.display="none";
        }
        }
    }