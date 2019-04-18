<?php
namespace UI\Controllers;

use Phalcon\Mvc\Controller;
use Cmfx\Mvc\Dispatcher;
use UI\Models\Adminuser;
use UI\Models\SaasClients;

/**
 * Class BaseController
 * 
 * @package UI\Controllers
 * @method errorNoAccess($msg = null)
 * @method errorNeedToken($msg = null)
 * @method errorWrongToken($msg = null)
 * @method errorServerError($msg = null)
 *        
 */
class BaseController extends Controller
{

    protected $version = '1.0';

    protected $error_dict = [
        'NOACCESS' => '400',
        'NEEDTOKEN' => '401',
        'WRONGTOKEN' => '402',
        'SERVERERROR' => '500'
    ];

    protected $code_dict = [
        200 => '请求成功',
        400 => '无权限使用接口',
        401 => '授权验证失败',
        402 => '无效的授权验证',
        500 => '服务器错误'
    ];

    /**
     * 公共接口，无需授权
     * 
     * @var array
     *
     */
    protected $exclude = [
        'default@index'
    ];

    protected $adminid = 0;

    /**
     * @var []
     */
    protected $perms = [];

    /**
     * @var int
     */
    protected $curr_shop_id = null;

    /**
     * @var string
     */
    protected $curr_perms = null;

    /**
     * @var int
     */
    protected $curr_role_id = null;

    /**
     * @var Shops[]
     */
    protected $shops = [];

    /**
     * @var Adminuser
     */
    protected $adminuser = null;

    /**
     * @var SaasClients
     */
    protected $client = null;

    /**
     * 检验请求(登录状态及权限)
     * 
     * @param $dispatcher Dispatcher            
     * @return bool
     */
    public function beforeExecuteRoute($dispatcher)
    {
        $this->view->disableLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        return true;
    }

    public function __call($name, $arguments)
    {
        if (preg_match('/^error/i', $name)) {
            $error = strtoupper(substr($name, 5));
            $code = $this->getErrorCode($error);
            array_unshift($arguments, $code);
            call_user_func_array([
                &$this,
                'error'
            ], $arguments);
        }
    }

    private function getErrorCode($error)
    {
        if (is_numeric($error))
            return $error;
        return $this->error_dict[$error];
    }

    public function error($code, $msg = null)
    {
        $msg = $msg ?  : $this->code_dict[$code];
        $msg = $msg ?  : "未知错误";
        return $this->response->setJsonContent(array(
            'code' => $code,
            'msg' => $msg,
            'data' => null
        ));
    }

    public function success($data, $msg = null)
    {
        return $this->response->setJsonContent(array(
            'code' => 200,
            'msg' => $msg ?  : 'success',
            'data' => $data
        ));
    }
}
