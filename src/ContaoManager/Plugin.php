<?php
/*******************************************************************
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *******************************************************************/

namespace IIDO\ShopBundle\ContaoManager;

use IIDO\ShopBundle\IIDOShopBundle;
use IIDO\BasicBundle\IIDOBasicBundle;

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;


/**
 * Plugin for the Contao Manager.
 *
 * @author Stephan Preßl <development@prestep.at>
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(IIDOShopBundle::class)
                ->setLoadAfter([IIDOBasicBundle::class]),
        ];
    }



//    /**
//     * {@inheritdoc}
//     */
//    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
//    {
//        return $resolver
//            ->resolve(__DIR__.'/../Resources/config/routing.yml')
//            ->load(__DIR__.'/../Resources/config/routing.yml');
//    }
}
