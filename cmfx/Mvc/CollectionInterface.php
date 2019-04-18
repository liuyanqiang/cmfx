<?php
namespace Cmfx\Mvc;

interface CollectionInterface
{
    public static function findById($id);
    public static function findFirst(array $parameters = null);
    public static function find(array $parameters = null);
    public static function count(array $parameters = null);
    public function getSource();
    public function save(array $data = null);
    public function create(array $data = null);
    public function update(array $data = null);
    public function delete();
}
