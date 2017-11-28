<?php

namespace Topxia\WebBundle\Handler;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\WebBundle\Handler\AuthenticationHelper;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

//身份验证成功的处理
class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    //身份验证成功
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {

        //检查登陆的禁止项
        $forbidden = AuthenticationHelper::checkLoginForbidden($request);

        if ($forbidden['status'] == 'error') {
            $exception = new AuthenticationException($forbidden['message']);
            throw $exception;
        }

        if ($request->isXmlHttpRequest()) {
            $content = array(
                'success' => true
            );
            return new JsonResponse($content, 200);
        }

        if ($this->getAuthService()->hasPartnerAuth()) {
            $url     = $this->httpUtils->generateUri($request, 'partner_login');
            $queries = array('goto' => $this->determineTargetUrl($request));
            $url     = $url.'?'.http_build_query($queries);
            return $this->httpUtils->createRedirectResponse($request, $url);
        }

        /*return parent::onAuthenticationSuccess($request, $token);*/
        
        $render = new Controller();
        return $render->redirect('http://115.182.90.203:10080');
       
    }

    private function getAuthService()
    {
        return ServiceKernel::instance()->createService('User.AuthService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
