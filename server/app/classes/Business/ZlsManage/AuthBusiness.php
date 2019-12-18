<?php
declare (strict_types=1);

namespace Business\ZlsManage;

use Dao\ZlsManage\RulesRelaDao;
use z;

/**
 * Zls
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @link          ---
 * @since         v0.0.1
 * @updatetime    2018-11-08 17:55
 */
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
            $user = (new UserBusiness())->info((int)$user);
        }
        if (!$user) {
            return ['title' => '未知用户', 'router' => $router];
        }
        // 标识码优先级最高，一旦标识码匹配成功即忽略路由匹配
        $marks           = Z::arrayGet($regular, 'marks', []);
        $permission      = Z::arrayGet($condition, 'permission', []);
        $classPermission = Z::arrayGet($condition, 'classPermission', []);
        $adopt           = false;
        // 不需要条件那么就应该是默认拥有权限了
        if (!$permission) {
            $adopt = true;
        } else {
            foreach ($permission as $p) {
                if (in_array($p, $marks, true)) {
                    $adopt = true;
                    if ($singlePermission) {
                        break;
                    }
                } else {
                    $adopt = false;
                }
            }
            if ($classPermission) {
                foreach ($classPermission as $p) {
                    if (!in_array($p, $marks, true)) {
                        $adopt = false;
                        break;
                    }
                }
            }
        }
        $ban = ['title' => '没有找到匹配规则', 'router' => $router];
        // 标识码匹配不到，开始匹配路由
        if (!$adopt) {
            $routers      = Z::arrayGet($regular, 'routers', []);
            $rules        = [];
            $banRules     = [];
            $rulesRelaDao = new RulesRelaDao();
            $normalState  = $rulesRelaDao::STATUS_NORMAL;
            $banState     = $rulesRelaDao::STATUS_BAN;
            foreach ($routers as $r) {
                if ($r['status'] === $normalState) {
                    $rules[] = $r;
                } elseif ($r['status'] === $banState) {
                    $banRules[] = $r;
                }
            }
            foreach ($rules as $rule) {
                if ($this->verify($router, $rule['mark'], $rule['condition'], $user)) {
                    $adopt = true;
                    break;
                }
            }
            if ($adopt) {
                foreach ($banRules as $rule) {
                    if ($this->verify($router, $rule['mark'], $rule['condition'], $user)) {
                        $adopt = false;
                        $ban   = $rule;
                        break;
                    }
                }
            }
        }

        return (bool)$adopt ?: $ban;
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
}
