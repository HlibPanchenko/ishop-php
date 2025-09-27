<?php

namespace wfm;

class Cache
{

    use TSingleton;

    public function set($key, $data, $seconds = 3600): bool
    {
        // $key - ключ по которому записываем данные в кеш
        // $seconds - время на которое данные будут записаны в кеше
        $content['data'] = $data;
        $content['end_time'] = time() + $seconds;
        // проверяем записались ли данные в файл кеша
        // serialize - массив в строчку
        if (file_put_contents(CACHE . '/' . md5($key) . '.txt', serialize($content))) {
            return true;
        } else {
            return false;
        }
    }

    public function get($key)
    {
        $file = CACHE . '/' . md5($key) . '.txt';
        if (file_exists($file)) {
            // восстановить исходные данные из строки в массив
            $content = unserialize(file_get_contents($file));
            if (time() <= $content['end_time']) {
                // данные еще актуальные
                return $content['data'];
            }
            // время жизни кеша закончилось, удаляем
            unlink($file);
        }
        return false;
    }

    public function delete($key)
    {
        $file = CACHE . '/' . md5($key) . '.txt';
        if (file_exists($file)) {
            unlink($file);
        }
    }

}