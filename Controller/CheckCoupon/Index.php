<?php

namespace Venofy\Fomo\Controller\CheckCoupon;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Venofy\Fomo\Helper\Data;
use Magento\SalesRule\Model\RuleFactory;

class Index extends Action
{
    protected $resultJsonFactory;
    protected $couponFactory;
    private $scopeConfig;
    protected $helper;

    /**
     * Constructor
     *
     * @param Data $helper
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param RuleFactory $couponFactory
     */
    public function __construct(
        Data $helper,
        Context $context,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        RuleFactory $couponFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->couponFactory = $couponFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Executes the logic for checking coupon
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        if (!$this->helper->isModuleEnabled()) {
            return $result->setData(['success' => false, 'message' => 'Module is disabled']);
        }

        $pixel = $this->helper->getPixel();
        $pixelCode = $this->getRequest()->getParam('pixel_code');

        // Check if pixel code is valid and request is from an allowed domain
        if (!$pixel || !$this->isRequestFromVenofy() || !$this->validPixelCode($pixel, $pixelCode)) {
            return $result->setData(['success' => false, 'message' => 'Unauthorized request or Invalid Pixel Code']);
        }

        // Fetch coupon data if everything is valid
        $couponData = $this->getCouponData();
        return $result->setData(['success' => true, 'data' => $couponData]);
    }

    /**
     * Fetch active coupon data with date validation
     *
     * @return array
     */
    private function getCouponData()
    {
        $todayDate = date('Y-m-d H:i:s');
        $couponRulesCollection = $this->couponFactory->create()->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('code', ['neq' => null])
            ->addFieldToFilter('from_date', ['lteq' => $todayDate])
            ->addFieldToFilter(
                ['to_date', 'to_date'],
                [
                    ['gteq' => $todayDate],
                    ['null' => true]
                ]
            )
            ->setPageSize(5); // Limit to 5 coupons

        $couponsData = [];
        foreach ($couponRulesCollection as $couponRule) {
            $couponsData[] = [
                'code' => $couponRule->getCode(),
                'discount' => $couponRule->getDiscountAmount(),
                'type' => in_array($couponRule->getSimpleAction(), ['by_fixed', 'cart_fixed']) ? 'fixed' : 'percentage',
            ];
        }

        return $couponsData;
    }

    /**
     * Check if the request is coming from an allowed domain based on IP
     *
     * @return bool
     */
    private function isRequestFromVenofy()
    {
        $allowedIPs = $this->helper->getWebsiteIPs();
        $origin = $_SERVER['HTTP_X_REAL_IP'] ?? '';

        return in_array($origin, $allowedIPs);
    }

    /**
     * Validate the pixel code from the request against the expected pixel code
     *
     * @param string $html
     * @param string $pixelCode
     * @return bool
     */
    private function validPixelCode($html, $pixelCode): bool
    {
        return str_contains($html, "/pixel/$pixelCode");
    }
}