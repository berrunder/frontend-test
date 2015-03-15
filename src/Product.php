<?php


class Product {

    /**
     * @param int $id
     * @return mixed
     */
    public static function findOne($id)
    {
        return json_decode(file_get_contents(self::getDataDir() . "$id.json"));
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function remove($id)
    {
        return unlink(self::getDataDir() . "$id.json");
    }

    /**
     * @param \stdClass $product
     * @return array
     */
    public static function add($product)
    {
        if ($product) {
            $product->submitted = time() * 1000;
            $files = array_filter(glob(self::getDataDir() . '*.json'), 'is_file');
            $num = 1;
            foreach ($files as $file) {
                $fileId = intval(basename($file, ".json"));
                if ($num <= $fileId) {
                    $num = $fileId + 1;
                }
            }

            if (self::save($product, $num) !== false) {
                return array(
                    'success' => true,
                    'id' => $num,
                    'submitted' => $product->submitted,
                );
            }
        }

        return array('success' => false);
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        $products = array();
        $files = array_filter(glob(self::getDataDir() . '*.json'), 'is_file');

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

    /**
     * @param int $id
     * @param \stdClass $product
     * @return bool|int
     */
    public static function update($id, $product)
    {
        $oldProduct = self::findOne($id);
        if ($oldProduct) {
            $oldProduct->name = $product->name;
            $oldProduct->quantity = $product->quantity;
            $oldProduct->price = $product->price;

            return self::save($oldProduct, $id);
        }

        return false;
    }

    protected static function getDataDir()
    {
        return __DIR__ . DIRECTORY_SEPARATOR .  '..' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param \stdClass $product
     * @param int $id
     * @return int
     */
    public static function save($product, $id)
    {
        return file_put_contents(self::getDataDir() . $id . '.json', json_encode($product));
    }
}