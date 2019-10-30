<?php


namespace ExportBestCustomers\Export;


use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\ImportExport\Export\AbstractExport;
use Thelia\Model\CustomerQuery;
use Thelia\Model\Map\AddressTableMap;
use Thelia\Model\Map\CustomerTableMap;
use Thelia\Model\Map\OrderProductTableMap;
use Thelia\Model\Map\OrderTableMap;

class BestCustomers extends AbstractExport
{

    const FILE_NAME = 'best_customers';

    /** @var array !@TODO:i18n */
    protected $orderAndAliases = [
        CustomerTableMap::COL_LASTNAME => 'Nom',
        CustomerTableMap::COL_FIRSTNAME => 'Prenom',
        CustomerTableMap::COL_EMAIL => 'Email',
        'cellphone' => 'Mobile',
        'phone' => 'Telephone',
        'order_sum' => 'Revenu total en (€)',
    ];

    /**
     * !@TODO : Choose number of customers to export (make config for module)
     * !@TODO : Choose TimeRange from where to calculate who the best customers are
     */
    protected function getData()
    {
        $query = CustomerQuery::create();

        $joinAddress = new Join(CustomerTableMap::COL_ID, AddressTableMap::COL_CUSTOMER_ID, Criteria::LEFT_JOIN);
        $joinOrder = new Join(CustomerTableMap::COL_ID,OrderTableMap::COL_CUSTOMER_ID,Criteria::LEFT_JOIN);
        $joinOrderProduct = new Join(OrderTableMap::COL_ID,OrderProductTableMap::COL_ORDER_ID,Criteria::LEFT_JOIN);

        $query
            ->addJoinObject($joinAddress)
            ->addJoinObject($joinOrder)
            ->addJoinObject($joinOrderProduct)
            ->select([
                CustomerTableMap::COL_LASTNAME,
                CustomerTableMap::COL_FIRSTNAME,
                CustomerTableMap::COL_EMAIL
            ])
            ->withColumn(AddressTableMap::COL_CELLPHONE, 'cellphone')
            ->withColumn(AddressTableMap::COL_PHONE, 'phone')
            ->withColumn('SUM(order_product.price)', 'order_sum')
            ->where("order.status_id IN (2,3,4)")
            ->orderBy('order_sum',Criteria::DESC)
            ->groupBy(CustomerTableMap::COL_ID)
            ->limit(100)
        ;

        return $query->find()->toArray();
    }
}