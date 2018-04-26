<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle;


use Symfony\Component\HttpKernel\Bundle\Bundle;
use IIDO\ShopBundle\DependencyInjection\IIDOShopExtension;


/**
 * Configures the Contao IIDO Shop Bundle.
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class IIDOShopBundle extends Bundle
{

    /**
     * Register extension
     *
     * @return IIDOShopExtension
     */
    public function getContainerExtension()
    {
        return new IIDOShopExtension();
    }
}