<?php

namespace Dynamic\FoxyStripe\ORM;

use Dynamic\FoxyStripe\Model\FoxyCart;
use Dynamic\FoxyStripe\Model\Order;
use SilverStripe\ORM\DataExtension;

class CustomerExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = array(
        'Customer_ID' => 'Int',
    );

    /**
     * @var array
     */
    private static $has_many = array(
        'Orders' => Order::class,
    );

    /**
     * @var array
     */
    private static $indexes = array(
        'Customer_ID' => true, // make unique
    );

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        // if Member data was imported from FoxyCart, PasswordEncryption will be set to 'none'.
        // Change to sh1_v2.4 to ensure SilverStripe is using the same hash as FoxyCart API 1.1
        $this->owner->PasswordEncryption = 'sha1_v2.4';

        // Send updated customer data to Foxy Cart via API
        $response = FoxyCart::putCustomer($this->owner);

        // Grab customer_id record from FoxyCart response, store in Member
        if ($response) {
            $foxyResponse = new \SimpleXMLElement($response);
            $this->owner->Customer_ID = (int) $foxyResponse->customer_id;
        }
    }
}
