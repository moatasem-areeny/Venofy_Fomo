<?php

namespace Venofy\Fomo\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get Venofy Website URL
     *
     * @return string
     */
    public function getVenofyWebsiteUrl(): string
    {
        return 'https://venofy.com/';
    }

    /**
     * Check if the module is enabled
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'venofy_general/settings/enable',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the Venofy Pixel
     *
     * @return string|null
     */
    public function getPixel(): ?string
    {
        return $this->scopeConfig->getValue(
            'venofy_general/settings/pixel',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the Website IPs
     *
     * @return string[]
     */
    public function getWebsiteIPs(): array
    {
        // Consider making this dynamic or configurable
        return ['165.84.218.161', '68.66.248.36'];
    }
    /**
     * Get the Venofy Pixel Code
     *
     * @return string|null
     */
    public function getPixelCode(): ?string
    {
        preg_match('/pixel\/([\w]+)/', $this->getPixel(), $matches);
        return $matches[1] ?? null;
    }
}