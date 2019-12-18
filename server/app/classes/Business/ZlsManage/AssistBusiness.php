<?php
declare (strict_types=1);

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
    public function updateMessageStatus($ids, $uid = 0): bool
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
            'ipWhitelist'  => implode(',', z::arrayGet($config, 'base.ipWhitelist', [])),
        ];

        return $data;
    }

    /**
     * 更新系统配置
     *
     * @param array $data
     *
     * @return bool
     */
    public function updateSystemConfig(array $data): bool
    {
        $config = z::config('ini', true, []);
        // 根据实际情况从$data读取需要的数据
        $config['base']['debug']        = z::arrayGet($data, 'debug');
        $config['project']['cdnHost']   = z::arrayGet($data, 'cdnHost');
        $config['base']['maintainMode'] = z::arrayGet($data, 'maintainMode');
        $config['base']['ipWhitelist']  = explode(';', z::arrayGet($data, 'ipWhitelist', ''));
        /**
         * @var Ini $Ini
         */
        $Ini  = z::extension('Action\Ini');
        $path = z::realPath('zls.ini', false, false);

        return !!@file_put_contents($path, $Ini->extended($config));
    }

    /**
     * 读取系统信息
     * @return array
     */
    public function getSystemInfo(): array
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        $data = [
            'hostname'          => z::hostname(),
            'zls_version'       => IN_ZLS,
            'os'                => strtolower(PHP_OS),
            'php_version'       => PHP_VERSION,
            'software'          => z::server('SERVER_SOFTWARE'),
            'extensions'        => join(' ', get_loaded_extensions()),
            'disable_functions' => join(' ', explode(',', ini_get('disable_functions'))),
            'free_space'        => z::convertRam(disk_free_space(z::realPath('.', true, false))),
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
}
