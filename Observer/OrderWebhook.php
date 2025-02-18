<?php

namespace Venofy\Fomo\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\HTTP\Client\Curl;
use Venofy\Fomo\Helper\Data;
use Psr\Log\LoggerInterface;

class OrderWebhook implements ObserverInterface
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    private  $_countryFactory;

    /**
     * Constructor to inject dependencies.
     *
     * @param Curl $curl
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Curl $curl,
        Data $helper,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->_countryFactory = $countryFactory;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Execute the observer event to send data to Venofy via webhook.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        // Check if module is enabled before proceeding
        if (!$this->helper->isModuleEnabled()) {
            return;
        }

        // Get the order object
        $order = $observer->getEvent()->getOrder();

        // Prepare data to send
        $data = $this->prepareOrderData($order);

        if ($data) {
            $this->sendWebhookRequest($data);
        }
    }

    /**
     * Prepare order data for webhook.
     *
     * @param Order $order
     * @return string|null
     */
    private function prepareOrderData(Order $order): ?string
    {
        // Retrieve customer details
        $customerName = $order->getCustomerName();
        $city = $order->getBillingAddress()->getCity();
        $countryCode = $order->getBillingAddress()->getCountryId();
        $country = $this->_countryFactory->create()->loadByCode($countryCode);

        // Create data array
        $data = [
            'event' => 'order.created',
            'name' => $customerName,
            'city' => $city,
            'country_code' => $countryCode,
            'country' => $country->getName(),
            'pixel_code' => $this->helper->getPixelCode(),
        ];

        // Encode data to JSON, return null if encoding fails
        $jsonData = json_encode($data);

        return ($jsonData === false) ? null : $jsonData;
    }

    /**
     * Send the webhook request to Venofy.
     *
     * @param string $data
     * @return void
     */
    private function sendWebhookRequest(string $data): void
    {
        try {
            $secret = 'd52ce459ee8a7fe8f466caf004a70e6e';

            // Generate HMAC signature
            $signature = hash_hmac('sha256', $data, $secret);

            // Set headers with the signature
            $headers = [
                'Content-Type' => 'application/json',
                'X-Venofy-Signature' => $signature,
            ];

            // Set headers in the Curl request
            $this->curl->setHeaders($headers);

            // Send the POST request to Venofy
            $this->curl->post($this->helper->getVenofyWebsiteUrl() . 'webhook-magento', $data);
        } catch (\Exception $e) {
            $this->logger->error('Webhook request failed: ' . $e->getMessage());
        }
    }
}