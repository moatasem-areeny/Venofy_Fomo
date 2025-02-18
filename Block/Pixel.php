<?php

namespace Venofy\Fomo\Block;

use Magento\Framework\View\Element\Template;
use Venofy\Fomo\Helper\Data;

class Pixel extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Constructor.
     *
     * @param Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        // Inject the helper dependency
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Check if the pixel is enabled.
     *
     * @return bool
     */
    public function isPixelEnabled(): bool
    {
        return $this->helper->isModuleEnabled(); // Return the status of the pixel module
    }

    /**
     * Get the pixel code.
     *
     * @return string
     */
    public function getPixel(): string
    {
        return $this->helper->getPixel(); // Return the pixel code from the helper
    }
}