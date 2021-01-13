<?php

declare(strict_types=1);

namespace Business\ZlsManage;

use Dao\ZlsManage\RulesRelaDao;
use z;

class AuthBusiness extends \Zls_Business
{
    /**
     * 验证指定用户是否有指定路由权限
     *
     * @param array|int $user
     * @param array     $condition        条件
     * @param array     $regular          当前用户拥有
     * @param bool      $singlePermission 是否只需匹配权限配置内其中一个即可
     *
     * @return bool|array 如果通过返回true,否则返回禁止的规则详情
     */
    public function userAuth($user, array $condition, array $regular, $singlePermission = false)
    {
        $router = Z::arrayGet($condition, 'router', '');
        if (!is_array($user)) {
            $user = (new UserBusiness())->info((int) $user);
        }
        if (!$user) {
            return ['title' => '未知用户', 'router' => $router];
        }
        // 先路由，后标识码补充
        $marks           = Z::arrayGet($regular, 'marks', []);
        $permission      = Z::arrayGet($condition, 'permission', []);
        $classPermission = Z::arrayGet($condition, 'classPermission', []);
        $adopt           = false;
        $routers         = Z::arrayGet($regular, 'routers', []);

        $ban = ['title' => '没有找到匹配规则', 'router' => $router];

        $fullRouter = $this->getFullRequestPath($router);

        foreach ($routers as $rules) {
            $rule = explode("\n", $rules['mark']);
            foreach ($rule as $key => $value) {
                // 正则路由
                if ($this->verify($fullRouter, $value, $rules['condition'], $user)) {
                    if ($rules['status'] == RulesRelaDao::STATUS_BAN) {
                        $adopt  = false;
                        break 2;
                    }
                    $adopt      = true;
                }

                $ban['router']  = $value;
            }
        }

        $adoptRoute = [
            "PUT" => [
                "/ZlsManage/UserApi/EditPassword.go"
            ],
            "POST" => [
                "/ZlsManage/UserApi/GetToken.go",
                "/ZlsManage/UserApi/ClearToken.go"
            ],
            "GET" => [
                "/ZlsManage/UserApi/UnreadMessageCount.go",
                "/ZlsManage/UserApi/UseriInfo.go",
                "/ZlsManage/UserManageApi/UserLists.go",
                "/ZlsManage/UserApi/Logs.go"
            ]
        ];
        if ($user['group_id'] == 1) {
            $adoptRoute['GET'] = array_merge([], $adoptRoute['GET'], [
                "/ZlsManage/SystemApi/SystemConfig.go",
                "/ZlsManage/SystemApi/SystemLogs.go"
            ]);
            $adoptRoute['POST'] = array_merge([], $adoptRoute['POST'], [
                "/ZlsManage/MenuApi/UserMenu.go"
            ]);
        }

        $req = strtolower(z::arrayGet($_SERVER, 'REQUEST_METHOD', ''));
        foreach ($adoptRoute as $aReq => $aRoute) {
            if (strtolower($aReq) !== $req) {
                continue;
            }

            foreach ($aRoute as $r) {
                if ($this->verify($fullRouter, $r, '', $user)) {
                    $adopt  = true;
                }
            }
        }

        // 不需要条件那么就应该是默认拥有权限了
        // 先标识码方法, 在标识码类
        if ($adopt) {
            if ($permission || $classPermission) {

                foreach ($permission as $p) {
                    if (in_array($p, $marks, true)) {
                        if ($singlePermission) {
                            break;
                        }
                    } else {
                        $adopt = false;
                        break;
                    }
                }

                foreach ($classPermission as $p) {
                    if (in_array($p, $marks, true)) {
                        if ($singlePermission) {
                            break;
                        }
                    } else {
                        $adopt = false;
                        break;
                    }
                }
            }
        }

        return (bool) $adopt ?: $ban;
    }

    private function verify($router, $rule, $condition, $user)
    {
        $rs       = false; //extract
        $ruleData = explode('::', strtolower($rule));
        $rule     = $ruleData[0];
        $type     = z::arrayGet($ruleData, 1);
        if (!!$type && (strtolower(Z::server('REQUEST_METHOD', '')) !== $type)) {
            return $rs;
        }
        if ($rule !== $router) {
            $matches = str_replace('*', '(.*)', $rule);
            $matches = str_replace('/', '\/', $matches);
            $rs      = preg_match('/' . $matches . '/i', $router);
        } else {
            $rs = true;
        }
        // todo 待完成
        // if (false || $rs && $condition) {
        //     if (preg_match_all('/\{(\w*?):(.*?):(.*?)\}/', $condition, $command)) {
        //         $len = count($command[0]);
        //         for ($i = 0; $i < $len; $i++) {
        //             $k = $command[1][$i];
        //             if (z::arrayKeyExists($k, $user)) {
        //                 switch (trim($command[2][$i])) {
        //                     case '>':
        //                         break;
        //                     default:
        //                 }
        //             }
        //         }
        //     }
        // }
        return $rs;
    }

    private function getFullRequestPath($router)
    {
        $methodUriSubfix = (z::config())->getMethodUriSubfix();

        $router = '/' . $router;
        if (substr($router, -3) !== $methodUriSubfix) {
            return $router . $methodUriSubfix;
        }

        return $router;
    }
}
