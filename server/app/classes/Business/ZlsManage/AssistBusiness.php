<?php

declare(strict_types=1);

namespace Business\ZlsManage;

use Dao\ZlsManage\LogsDao;
use Dao\ZlsManage\UserDao;
use z;
use Zls\Action\FileUp;
use Zls\Action\Ini;

/**
 * Zls
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @link          ---
 * @since         v0.0.1
 * @updatetime    2018-11-08 17:55
 */
class AssistBusiness extends \Zls_Business
{
    public const AVATAR_PATH = 'static/avatar';
    public const AVATAR_TMP_PATH = 'static/avatar/tmp';

    /**
     * 上传头像图片至临时目录
     * @return array|string
     */
    public function uploadAvatar()
    {
        return $this->upload(self::AVATAR_TMP_PATH, '', ['jpg', 'png'], 'file', 1024);
    }

    /**
     * 移动头像图片
     * @desc 把头像图片从临时目录移动至最终目录并且返回路径
     *
     * @param string $path     头像路径
     * @param null   $filename 文件名,不包含后缀
     *
     * @return string
     */
    public function mvAvatar($path, $filename = null): string
    {
        $tmp = Z::urlPath(self::AVATAR_TMP_PATH);
        if (Z::strBeginsWith($path, $tmp)) {
            $newPath = str_replace($tmp, Z::urlPath(self::AVATAR_PATH), $path);
            if ($filename && ($oldFilename = pathinfo($newPath, PATHINFO_BASENAME))) {
                $newPath = str_replace($oldFilename, $filename, $newPath);
            }
            if (rename(Z::realPath(ltrim($path, '/')), Z::realPath(ltrim($newPath, '/')))) {
                $path = $newPath;
            }
        }

        return $path;
    }

    /**
     * 上传文件
     *
     * @param        $dir
     * @param string $name
     * @param array  $ext
     * @param string $formfield
     * @param int    $size
     *
     * @return array|string
     */
    public function upload($dir, $name = '', $ext = ['jpg', 'png'], $formfield = 'file', $size = 2048)
    {
        /** @var FileUp $fileUpload */
        $fileUpload = z::extension('Action\FileUp');
        $fileUpload->setFormField($formfield);
        $fileUpload->setMaxSize($size);
        $fileUpload->setExt($ext);
        $path = $fileUpload->saveFile($name, $dir);
        if (!$path) {
            return z::arrayGet($fileUpload->getError(), 'error');
        }
        if (is_array($path)) {
            $path = $path[0];
        }

        return ['url' => z::safePath($path, '', true), 'path' => $path];
    }

    /**
     * 获取日志列表
     *
     * @param      $page
     * @param      $pagesize
     * @param int  $unread
     * @param int  $type
     * @param null $userid
     *
     * @return array
     */
    public function getLogs($page, $pagesize, $unread = 0, $type = 0, $userid = null): array
    {
        $dao   = new LogsDao();
        $where = $unread ? ['status' => $dao::STATUS_NOT] : [];
        if ($type) {
            $where['type'] = $type;
        }
        if ($userid) {
            $where['userid'] = $userid;
        }
        $table     = $dao->getTable();
        $userTable = (new UserDao())->getTable();
        $lists     = $dao->getPage($page, $pagesize, '{page}', "{$table}.*,{$userTable}.username as username", function (\Zls_Database_ActiveRecord $db) use ($where, $table, $userTable) {
            $newWhere = [];
            foreach ($where as $key => $value) {
                $newWhere[$table . '.' . $key] = $value;
            }
            $db->where($newWhere);
            $db->join($userTable, $userTable . '.id=' . $table . '.operate_id', 'left');
        }, [$table . '.id' => 'desc']);

        return $lists;
    }

    /**
     * 获取未读日志总数
     *
     * @param int $lastid
     * @param int $userid
     *
     * @return int
     */
    public function getUnreadMessageCount($lastid = 0, $userid = 0): int
    {
        $logsDao = new LogsDao();

        return (int)$logsDao->selectCount(['id >' => $lastid, 'userid' => $userid, 'status' => $logsDao::STATUS_NOT]);
    }

    /**
     * 更新日志状态
     *
     * @param     $ids
     * @param int $uid
     *
     * @return bool
     */
    public function updateMessageStatus($ids, $uid = 0)
    {
        $logsDao = new LogsDao();

        return $logsDao->updateBatch(z::arrayMap($ids, static function ($v) use ($logsDao) {
            return ['id' => $v, 'status' => $logsDao::STATUS_READ];
        }), 'id');
    }

    /*
     * 读取系统配置
     * @return array
     */
    public function getSystemConfig(): array
    {
        $config = z::config('ini', true, []);
        // 根据实际情况过滤只返回需要显示的
        $data = [
            'debug'        => (bool)z::arrayGet($config, 'base.debug', true),
            'cdnHost'      => z::arrayGet($config, 'project.cdnHost', ''),
            'maintainMode' => (bool)z::arrayGet($config, 'base.maintainMode', true),
            'loginMode'    => (bool)z::arrayGet($config, 'base.loginMode', false),
            'ipWhitelist'  => implode(',', z::arrayGet($config, 'base.ipWhitelist', [])),
        ];

        return $data;
    }

    /**
     * 更新系统配置
     *
     * @param array $data
     * @param int   $userID
     * @param int   $tokenID
     *
     * @return bool
     */
    public function updateSystemConfig(array $data, int $userID, int $tokenID): bool
    {
        $config = z::config('ini', true, []);
        // 根据实际情况从$data读取需要的数据
        $config['base']['debug']        = z::arrayGet($data, 'debug');
        $config['project']['cdnHost']   = z::arrayGet($data, 'cdnHost');
        $config['base']['maintainMode'] = z::arrayGet($data, 'maintainMode');
        $config['base']['loginMode']    = z::arrayGet($data, 'loginMode');
        $config['base']['ipWhitelist']  = explode(';', z::arrayGet($data, 'ipWhitelist', ''));
        /**
         * @var Ini $Ini
         */
        $Ini  = z::extension('Action\Ini');
        $path = z::realPath('zls.ini', false, false);

        return  (bool)z::tap(!!@file_put_contents($path, $Ini->extended($config)), function ($value) use ($config, $userID, $tokenID) {
            if ($config['base']['loginMode']) {
                (new UserBusiness)->clearAllTokenSaveLastId($userID, $tokenID);
            }
        });
    }

    /**
     * 读取系统信息
     * @return array
     */
    public function getSystemInfo(): array
    {
        $sysInfo = [];
        switch (PHP_OS) {
            case "Linux":
                $sysInfo = $this->sysLinux();
                break;
            default:
                break;
        }
        /** @noinspection PhpComposerExtensionStubsInspection */
        $data = [
            'hostname'          => z::hostname(),
            'zls_version'       => IN_ZLS,
            'os'                => strtolower(PHP_OS),
            'php_version'       => PHP_VERSION,
            'software'          => z::server('SERVER_SOFTWARE'),
            'extensions'        => join(' ', get_loaded_extensions()),
            'disable_functions' => join(' ', explode(',', ini_get('disable_functions'))),
            'total_space'       => z::convertRam(disk_total_space(z::realPath('.', true, false))),
            'free_space'        => z::convertRam(disk_free_space(z::realPath('.', true, false))),
            'memory'            => [
                'total' => z::convertRam(z::arrayGet($sysInfo, 'memTotal', 0)),
                'used'  => z::convertRam(z::arrayGet($sysInfo, 'memUsed', 0)),
                'free'  => z::convertRam(z::arrayGet($sysInfo, 'memFree', 0)),
                'usage' => z::arrayGet($sysInfo, 'memPercent', 0),
            ],
            'disk'              => [
                'total' => z::convertRam(disk_total_space(z::realPath('.', true, false))),
                'free'  => z::convertRam(disk_free_space(z::realPath('.', true, false))),
            ],
            'composer'          => z::tap(json_decode(@file_get_contents(z::realPath('composer.lock', false, false)), true), function (&$composer) {
                $composer = z::arrayGet($composer, 'packages', []);
                $composer = z::arrayFilter(z::arrayValues($composer, ['name', 'version']), function ($v) {
                    return $v['name'] !== 'zls/framework';
                });
                $composer = z::arrayMap($composer, static function ($v) {
                    $v['version'] = z::strBeginsWith($v['version'], 'v') ? substr($v['version'], 1) : $v['version'];

                    return $v;
                });
                $composer = array_merge([['name' => 'zls/framework', 'version' => IN_ZLS]], array_values($composer));
            }),
        ];

        return $data;
    }

    private function sysLinux()
    {
        if (false === ($str = @file("/proc/meminfo"))) {
            return false;
        }
        $str = implode("", $str);

        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);

        $res['memTotal'] = round($buf[1][0] * 1024, 2);
        $res['memFree'] = round($buf[2][0] * 1024, 2);
        $res['memCached'] = round($buf[3][0] * 1024, 2);
        $res['memUsed'] = $res['memTotal'] - $res['memFree'];
        $res['memPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memUsed'] / $res['memTotal'] * 100, 2) : 0;

        // $res['memRealUsed'] = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers']; //真实内存使用
        // $res['memRealFree'] = $res['memTotal'] - $res['memRealUsed']; //真实空闲
        // $res['memRealPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memRealUsed'] / $res['memTotal'] * 100, 2) : 0; //真实内存使用率

        // $res['memCachedPercent'] = (floatval($res['memCached']) != 0) ? round($res['memCached'] / $res['memTotal'] * 100, 2) : 0; //Cached内存使用率

        // $res['swapTotal'] = round($buf[4][0] / 1024, 2);
        // $res['swapFree'] = round($buf[5][0] / 1024, 2);
        // $res['swapUsed'] = round($res['swapTotal'] - $res['swapFree'], 2);
        // $res['swapPercent'] = (floatval($res['swapTotal']) != 0) ? round($res['swapUsed'] / $res['swapTotal'] * 100, 2) : 0;

        return $res;
    }
}
