<?php

namespace Neverovsky\Kafka\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\WriteFactory;

class ProcessManager
{
    const PID_FILE_EXT = '.pid';

    private $filesystem;

    private $writeFactory;

    private $directoryList;

    /**
     * ProcessManager constructor.
     * @param Filesystem $filesystem
     * @param WriteFactory $writeFactory
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Filesystem $filesystem,
        WriteFactory $writeFactory,
        DirectoryList $directoryList
    )
    {
        $this->filesystem = $filesystem;
        $this->writeFactory = $writeFactory;
        $this->directoryList = $directoryList;
    }


    /**
     * Проверяем запущен ли слушатель
     *
     * @param string $consumerName
     * @return bool
     */
    public function isRun($consumerName)
    {
        $pid = $this->getPid($consumerName);
        if ($pid) {
           return $this->checkIsProcessExists($pid);
        }

        return false;
    }

    /**
     * Проверка: работает ли процессс на самом деле
     *
     * @param int $pid
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @return bool
     */
    private function checkIsProcessExists($pid)
    {
        if (!function_exists('exec')) {
            throw new \RuntimeException('Функция exec не доступна');
        }

        exec(escapeshellcmd('ps -p ' . $pid), $output, $code);

        $code = (int)$code;

        switch ($code) {
            case 0:
                return true;
                break;
            case 1:
                return false;
                break;
            default:
                throw new \RuntimeException('Exec возвратил не нулевой ответ', $code);
                break;
        }
    }

    /**
     * Получаем PID слушателя по имени
     *
     * @param string $consumerName
     * @return bool|int
     */
    public function getPid($consumerName)
    {
        $pidFile = $consumerName . static::PID_FILE_EXT;
        /** @var WriteInterface $directory */
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        if ($directory->isExist($pidFile)) {
            return (int)$directory->readFile($pidFile);
        }

        return false;
    }


    /**
     * Получаем путь к файлу с PID
     *
     * @param string $consumerName
     * @return string
     */
    public function getPidFilePath($consumerName)
    {
        return $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/' . $consumerName . static::PID_FILE_EXT;
    }

    /**
     * Сохраняем PID процесса в фаил
     *
     * @param string $pidFilePath
     */
    public function savePid($pidFilePath)
    {
        $file = $this->writeFactory->create($pidFilePath, DriverPool::FILE, 'w');
        $file->write(function_exists('posix_getpid') ? posix_getpid() : getmypid());
        $file->close();
    }
}
