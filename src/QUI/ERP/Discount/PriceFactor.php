<?php

/**
 * This file contains QUI\ERP\Discount\PriceFactor
 */

namespace QUI\ERP\Discount;

use QUI;

/**
 * Class PriceFactor
 * This pricefactors is used by Handler::DISCOUNT_SCOPE_TOTAL
 *
 * @package QUI\ERP\Discount
 */
class PriceFactor extends QUI\ERP\Products\Utils\PriceFactor implements QUI\ERP\Products\Interfaces\PriceFactorWithVatInterface
{
    /**
     * @var string
     */
    protected $type = Handler::DISCOUNT_PRICEFACTOR_TYPE;

    /**
     * @var string|null
     */
    protected $vat = null;

    /**
     * PriceFactor constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        if (isset($params['vat'])) {
            $this->vat = $params['vat'];
        }
    }

    /**
     * Return the vat type of the discount price factor
     *
     * @return QUI\ERP\Tax\TaxType
     */
    public function getVatType()
    {
        if (!$this->vat) {
            return QUI\ERP\Tax\Utils::getShopTaxType();
        }

        $standardTax = explode(':', $this->vat);

        if (!isset($standardTax[1])) {
            return QUI\ERP\Tax\Utils::getShopTaxType();
        }

        try {
            $Handler = new QUI\ERP\Tax\Handler();

            return $Handler->getTaxType($standardTax[1]);
        } catch (QUI\Exception $Exception) {
        }

        return QUI\ERP\Tax\Utils::getShopTaxType();
    }
}
