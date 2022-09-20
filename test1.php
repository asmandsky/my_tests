<?php
header('Content-Type: text/html; charset=utf-8');
require_once(dirname(__FILE__) . "/core/core.php");

interface IReplace
{
    public function find($find);
    public function request();
    public function replace($item);
}

class ReplaceXmlArticles implements IReplace
{
    private $find = array();

    private $dsp;

    public function __construct()
    {
        $this->dsp = Dispatcher::getInstance();
    }

    public function find($find = array())
    {
        if(!empty($find)) {
            $this->find = $find;
        } else {
            return false;
        }
        return true;
    }

    public function request()
    {
        $sql = "SELECT `id`, `title`, `xml`
                  FROM `articles` 
                 WHERE `xml` LIKE ?
                   AND `status` = 1
              ORDER BY `id` DESC
                 LIMIT 0,1
        ";
        return $this->dsp->db->select($sql,$this->find['like']);
    }

    public function replace($item)
    {
        $what = $this->find['what'];
        $than = $this->find['than'];
        $text = preg_replace_callback($this->find['pattern_find'], function ($matches) use ($item, $what, $than) {
            echo "-- Replace [" . $item['id'] . "] - " . htmlspecialchars($matches[0]) . "<br />\n";
            return preg_replace($what, $than, $matches[0]);
        }, $item['xml'], -1, $count);
        if($count) {
            //$this->historyArtSave($item);
            //$this->updateArtXml($text,$item);
            //$this->logArtSave($item);
            echo "UPDATE `articles` SET `xml` = '" . htmlspecialchars($text) . "' WHERE `id` = " . $item['id'] . ";<br />\n";
        }
        return $text;
    }

    private function updateArtXml($xml,$item)
    {
        $sql = "UPDATE `articles` SET `xml` = ? WHERE `id` = ?";
        $this->dsp->db->execute($sql,$xml,$item['id']);
    }

    private function logArtSave($item)
    {
        $this->dsp->logs->LogItCustom('articles', 'update', $item['id'], "Изменение тела: " . $item['id']);
        $this->dsp->logs->LogItCustom('articles', 'update', $item['id'], "Сохранение");
    }

    private function historyArtSave($item)
    {
        $adminuser_id = 95;
        $this->dsp->db->execute('INSERT INTO `art_history` (`art_id`,`adminuser_id`,`datetime`,`xml`,`title`) VALUES (?,?,?,?,?)',
            $item['id'],
            $adminuser_id,
            date('Y-m-d H:i:s'),
            $item['xml'],
            $item['title']
        );
        $id_ah = $this->dsp->db->LastInsertId();
        echo "UPDATE `articles` AS `a` INNER JOIN `art_history` AS `ah` ON `ah`.`art_id` = `a`.`id` AND `ah`.`adminuser_id` = " . $adminuser_id . " AND `ah`.`id` = " . $id_ah . " SET `a`.`xml` = `ah`.`xml` WHERE `a`.`id` = " . $item['id'] . ";<br />\n";
    }
}

abstract class ReplaceFill
{

    protected $replace;

    abstract public function related();
    abstract public function links();

    protected function fill($find = array())
    {
        if(!$this->replace->find($find)) return;
        $items = $this->replace->request();
        if(empty($items)) return;
        foreach($items as $item) {
            $this->replace->replace($item);
        }
    }
}

class ReplaceDosye extends ReplaceFill
{

    protected $replace;

    public function __construct($replace)
    {
        $this->replace = $replace;
    }

    public function related()
    {
        $find['pattern_find'] = '~<REPLACE_related_dosye_multi>(.+?)<\/REPLACE_related_dosye_multi>~si';
        $find['what']         = '~<REPLACE_related_dosye_multi>(.+?)<\/REPLACE_related_dosye_multi>~si';
        $find['than']         = '';
        $find['like']         = '%<REPLACE_related_dosye_multi>%';
        $this->fill($find);
    }

    public function links()
    {
        $find['pattern_find'] = '~<a([^>]*)href="/dosye/([^<]+)/"([^>]*)>([^<]+)<\/a>~si';
        $find['what']         = '~<a([^>]*)href="/dosye/([^<]+)/"([^>]*)>([^<]+)<\/a>~si';
        $find['than']         = '$4';
        $find['like']         = '%/dosye/%';
        $this->fill($find);
    }

}

$replace = new ReplaceXmlArticles();
$dosye = new ReplaceDosye($replace);
$dosye->related();
//$dosye->links();


die();
