<?php


namespace IIDO\ShopBundle\Model;


/**
 *
 *
 */
class IidoShopProductCategoryModel extends \Model
{

    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = 'tl_iido_shop_product_category';



    /**
     * Get the category URL
     *
     * @param \PageModel $page
     *
     * @return string
     */
    public function getUrl(\PageModel $page)
    {
        $page->loadDetails();

        return $page->getFrontendUrl('/' . self::getParameterName($page->rootId) . '/' . $this->alias);
    }



    /**
     * Get all Category Products
     *
     * @param integer $archiveID
     */
    public static function getProducts( $categoryID, $archiveID )
    {
        $arrProducts    = array();
        $objProducts    = IidoShopProductModel::findBy("published", "1");

        if( $objProducts )
        {
            while( $objProducts->next() )
            {
                $arrCategories = \StringUtil::deserialize($objProducts->categories, TRUE);

                if( count($arrCategories) && in_array($categoryID, $arrCategories) )
                {
                    $arrProducts[] = $objProducts->current();
                }
            }
        }

        return new \Contao\Model\Collection($arrProducts, IidoShopProductModel::getTable());
    }

}
