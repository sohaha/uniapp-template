<?php

declare(strict_types=1);

namespace Controller;

use Business\ZlsManage\AuthBusiness;
use Business\ZlsManage\PermissionsBusiness;
use Business\ZlsManage\UserBusiness;
use Dao\ZlsManage\AccessLogDao;
use Z;
use Zls\Action\ApiDoc;
use Zls_Controller;

/**
 * Class ZlsManage
 * @package Controller
 */
class ZlsManage extends Zls_Controller
{
    /**
     * 跳转到静态页面
     * @api-ignore 忽略权限控制
     */
    public function zIndex(): void
    {
        Z::redirect('/zls-manage/index.html');
    }

    protected $USER;
    /** @var UserBusiness $UserBusiness */
    protected $UserBusiness;

    /**
     * @param $method
     * @param $controllerShort
     * @param $args
     * @param $methodFull
     * @param $controller
     *
     * @return null|array|void
     */
    public function before($method, $controllerShort, $args, $methodFull, $controller)
    {
        $AuthBusiness       = new AuthBusiness();
        $router             = str_replace('_', '/', z::strCamel2Snake(str_replace('\\', '/', $controllerShort) . '/' . $method, ''));
        $this->UserBusiness = new UserBusiness;
        $comment            = ApiDoc::getComment($this, $methodFull);
        /** @var array 方法权限 $permission */
        $permission = $comment('permission');
        // 控制器权限（最高），
        // 如果用户没这个权限那么就会直接忽略方法权限的判断，进入路由判断
        $classPermission = $comment('permissionClass');
        // 对带有 api-ignore 并且没有指定权限的方法跳过权限控制
        if (!$permission && !$classPermission && (bool) $comment('ignore')) {
            return;
        }
        // 是否只需匹配权限配置内其中一个即可
        $singlePermission = $comment('single-permission');
        $token            = $this->getToken();
        $this->USER       = $this->UserBusiness->tokenToInfo($token);
        if (!$this->USER) {
            return [401, '请先登录'];
        }
        $this->USER['isSuper'] = $this->UserBusiness->isSuperAdmin((int) $this->USER['id']);
        $noVerification        = [];
        $permission            = array_reduce($permission ?: [], 'array_merge', []);
        $classPermission       = array_reduce($classPermission ?: [], 'array_merge', []);
        if (!$this->USER['isSuper'] && !in_array($router, $noVerification, true)) {
            $condition = [
                'router'          => $router,
                'permission'      => $permission,
                'classPermission' => $classPermission,
            ];
            $regular   = [
                'marks'   => $this->USER['marks'],
                'routers' => $this->USER['routers'],
            ];
            $auth      = $AuthBusiness->userAuth($this->USER['id'], $condition, $regular, $singlePermission);
            if ($auth !== true) {
                return [403, '对不起，权限不足', $auth];
            }
        }

        return null;
    }

    /**
     * 获取token
     * @return string
     */
    final protected function getToken(): string
    {
        return z::server('HTTP_TOKEN') ?: z::getPost('token', '');
    }

    /**
     * 获取用户信息
     *
     * @param null|string $key
     *
     * @return array|string
     */
    final public function getInfo($key = null)
    {
        return $key ? z::arrayGet($this->USER, $key) : $this->USER;
    }


    /**
     * 判断用户是否有对应权限
     *
     * @param array $permissions
     * @param null  $userid
     *
     * @return bool
     */
    final public function hasPermissions(array $permissions, $userid = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission, $userid)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 判断用户是否有对应权限
     *
     * @param string $permissions
     * @param null   $userid 用户ID 默认当前用户
     *
     * @return bool
     */
    final public function hasPermission(string $permissions, $userid = null): bool
    {
        if ($userid === null) {
            $groupid = $this->getInfo('group_id');
        } else {
            if (!$info = (new UserBusiness())->info($userid)) {
                return false;
            }
            $groupid = Z::arrayGet($info, 'group_id');
        }
        /** @var PermissionsBusiness $business */
        $business = Z::business('ZlsManage\PermissionsBusiness');
        $groupids = explode(',', $groupid);
        foreach ($groupids as $groupid) {
            if ($business->has($permissions, (int) $groupid)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $contents
     * @param $methodName
     * @param $controllerShort
     * @param $args
     * @param $methodFull
     * @param $class
     *
     * @return string
     */
    public function after($contents, $methodName, $controllerShort, $args, $methodFull, $class): string
    {
        if ($this->USER) {
            $jsonOpt = JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES;
            $dao     = new AccessLogDao();
            $data    = [
                'userid'      => $this->getInfo('id'),
                'route'       => Z::host(false, true),
                'param'       => json_encode(Z::get(), $jsonOpt),
                'input'       => json_encode(z::post() ?: (Z::postRaw() ?: ''), $jsonOpt),
                'content'     => json_encode($contents, $jsonOpt),
                'create_time' => date('Y-m-d H:i:s'),
            ];
            $dao->insert($data);
        }
        if ($contents === null) {
            return '';
        }

        return is_array($contents) ? Z::json($contents) : Z::json(211, $contents);
    }

    /**
     * @param $method
     * @param $controllerShort
     * @param $args
     * @param $methodFull
     * @param $controller
     *
     * @return array
     */
    public function call($method, $controllerShort, $args, $methodFull, $controller): array
    {
        return [404, 'HTTP/1.1 404 Not Found', [Z::server('REQUEST_METHOD', '')]];
    }
}
