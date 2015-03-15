<?php


class Product {

    public static function remove($id)
    {
        return unlink(DATA_DIR . DIRECTORY_SEPARATOR . "$id.json");
    }

    public static function save($product)
    {
        if ($product) {
            $product->submitted = time() * 1000;
            $files = array_filter(glob(DATA_DIR . DIRECTORY_SEPARATOR . '*.json'), 'is_file');
            $num = 1;
            foreach ($files as $file) {
                $fileId = intval(basename($file, ".json"));
                if ($num <= $fileId) {
                    $num = $fileId + 1;
                }
            }

            if (file_put_contents(DATA_DIR . DIRECTORY_SEPARATOR . $num . '.json', json_encode($product)) !== false) {
                return array(
                    'success' => true,
                    'id' => $num,
                    'submitted' => $product->submitted,
                );
            }
        }

        return array('success' => false);
    }

    public static function getAll()
    {
        $products = [];
        $files = array_filter(glob(DATA_DIR . DIRECTORY_SEPARATOR . '*.json'), 'is_file');

        foreach ($files as $file) {
            $fileId = intval(basename($file, ".json"));
            $raw = file_get_contents($file);
            $data = json_decode($raw);
            if ($data && is_object($data)) {
                $data->id = $fileId;
                $products[] = $data;
            }
        }

        return $products;
    }
}