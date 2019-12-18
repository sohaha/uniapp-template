<?php
declare (strict_types=1);

namespace Business\ZlsManage;

use Z;

/**
 * 读取系统日志
 * Class SysLogBusiness
 * @package Business
 */
class SysLogBusiness extends \Zls_Business
{
    public function domains($file = null): array
    {
        return $this->exportData('DomainCheck', $file);
    }

    /**
     * 统一输入格式
     *
     * @param     $name
     * @param     $file
     * @param int $line
     *
     * @return array
     */
    public function exportData($name, $file, $line = 0): array
    {
        $data        = [];
        $dirs        = [];
        $content     = '';
        $size        = 0;
        $currentLine = 0;
        $this->listDir($name, $dirs);
        if ($file && z::arrayKeyExists($file, $dirs, false)) {
            $size = filesize($dirs[$file]['file']);
            if ($line > 0) {
                $tail = $this->tail($dirs[$file]['file'], $line, true);
            } elseif ($size > (1024 * 500)) {
                $content = "日志文件过大（{$size}kb），不支持在线查看全部内容。\n\n";
                $tail    = $this->tail($dirs[$file]['file'], false, 10);
            } else {
                $tail = $this->tail($dirs[$file]['file'], true, true);
            }
            if ($tail) {
                $content     .= $tail['0'];
                $currentLine = $tail['2'];
            } else {
                $logfile = z::safePath($dirs[$file]['file']);
                $content = "读取日志文件: {$logfile} 失败";
            }
        }
        $data['lists']       = $this->listsReverse($dirs);
        $data['content']     = $content ? $content . "\n" : "";
        $data['current']     = $file;
        $data['currentLine'] = $currentLine;
        $data['size']        = $size;
        $data['types']       = $this->getTypes();

        return $data;
    }

    /**
     * 日志排序
     *
     * @param $dirs
     *
     * @return array
     */
    private function listsReverse($dirs): array
    {
        return array_reverse(z::arrayMap($dirs, function ($v, $k) {
            return $k;
        }, false));
    }

    /**
     * 读取日志
     *
     * @param          $file
     * @param int|bool $start 开始读取行数，true表示从头开始,false表示从底部开始
     * @param int|bool $sum   读取行数，true表示读取整个文件
     *
     * @return array|bool
     */
    public function tail($file, $start = null, $sum = 10)
    {
        if (!$fp = fopen($file, 'rb')) {
            return false;
        }
        $lines = '';
        $total = 0;
        while (fgets($fp)) {
            $total++;
        }
        fseek($fp, 0, SEEK_SET);
        if ($start === false) {
            $start = $total - $sum;
        } elseif ($start === true) {
            $start = 0;
        } else {
            $start = (int)$start;
        }
        if ($sum === true) {
            $sum = $total;
        }
        $current = 0;
        while ($sum > 0 && ($data = fgets($fp))) {
            ++$current;
            if ($current > $start) {
                $lines .= $data;
                $sum--;
            }
        }
        fclose($fp);

        return [$lines, $total, $current];
    }

    /**
     * 读取日志文件
     *
     * @param        $dir
     * @param        $arr
     * @param string $Subfix
     * @param string $lastDir
     */
    private function listDir($dir, &$arr, $Subfix = '.log', $lastDir = null)
    {
        if ($lastDir === null) {
            $confing = z::config();
            $lastDir = $dir = z::realPath($confing->getStorageDirPath() . $dir, true);
        }
        if (is_dir($dir) && ($dh = opendir($dir))) {
            while (false !== ($file = readdir($dh))) {
                if ('.' === $file || '..' === $file) {
                    continue;
                }
                $filePath = Z::realPath($dir . '/' . $file);
                if ((is_dir($filePath))) {
                    $this->listDir($dir . '/' . $file, $arr, $Subfix, $lastDir);
                } elseif (z::strEndsWith($file, $Subfix)) {
                    $filemtime                                 = filemtime($filePath);
                    $arr[str_replace($lastDir, '', $filePath)] = [
                        'file' => $filePath,
                        'time' => $filemtime ? date('Y-m-d H:i:s', $filemtime) : '',
                    ];
                }
            }
            closedir($dh);
        }
    }

    /**
     * 获取类型列表
     */
    public function getTypes(): array
    {
        $confing   = z::config();
        $dir       = $confing->getStorageDirPath();
        $ignoreDir = ['cache', 'taskSingle'];
        $types     = [];
        if (is_dir($dir) && ($dh = opendir($dir))) {
            while (false !== ($file = readdir($dh))) {
                if ('.' === $file || '..' === $file || in_array($file, $ignoreDir, true) || is_file($dir . $file)) {
                    continue;
                }
                $types[] = $file;
            }
            closedir($dh);
        }

        return $types;
    }

    public function delete($name, $file)
    {
        $this->listDir($name, $dirs);
        if ($file && z::arrayKeyExists($file, $dirs, false)) {
            return @unlink($dirs[$file]['file']);
        }

        return false;
    }
}
