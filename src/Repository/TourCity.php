<?php

namespace Tourscool\Repository;

/**
 * @description 城市景点
 */
class TourCity extends Base
{
    /**
     * @description 景点描述信息 id=> 信息
     */
    public function cityDescIdMaps($tourCityIds, $languageId)
    {
        $tourCityIds = array_filter($tourCityIds, 'is_numeric');
        if (empty($tourCityIds)) {
            return [];
        }
        $tourCityIdString = implode(',', $tourCityIds);
        $sql = "select `name`,`tour_city_id` from `tour_city_description` WHERE tour_city_id in ({$tourCityIdString})
 AND `language_id` = {$languageId}";

        $cityNames = $this->db->fetchAll($sql);

        return array_combine(array_column($cityNames, 'tour_city_id'), $cityNames);
    }

    /**
     * @description 产品描述等信息 id 映射
     */
    public function productDescIdsMap($productIds, $languageId)
    {
        $productIds = array_filter($productIds, 'is_numeric');
        if (empty($productIds)) {
            return [];
        }
        $productIdString = implode(',', $productIds);
        $sql = <<<SQL
select p.default_price,p.image,p.product_id,pd.name from product p , product_description pd 
where p.product_id = pd .product_id 
and p.product_id in ({$productIdString}) and pd.language_id = {$languageId}
SQL;

        $products = $this->db->fetchAll($sql);
        return array_combine(array_column($products, 'product_id'), $products);
    }


}