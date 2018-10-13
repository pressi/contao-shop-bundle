<?php
/*******************************************************************
 *
 * (c) 2018 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/


namespace IIDO\ShopBundle\Payment;



abstract class DefaultPaymentMethod
{
    abstract public function success();

    abstract public function error();

    abstract public function newPayment();
}