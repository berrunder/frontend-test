<?php


class Product {

    public static function remove($id)
    {
        return unlink(DATA_DIR . DIRECTORY_SEPARATOR . "$id.json");
    }
}