<?php

namespace MageSuite\Navigation\Model\Navigation;

class ImageTeaser extends \Magento\Framework\DataObject
{
    protected $imageUrl = null;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;
    /**
     * @var \MageSuite\Navigation\Service\Category\CustomUrlGenerator
     */
    protected $customUrlGenerator;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Catalog\Model\Category $category,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\Navigation\Service\Category\CustomUrlGenerator $customUrlGenerator,
        array $data = []
    )
    {
        parent::__construct($data);

        $this->category = $category;
        $this->customUrlGenerator = $customUrlGenerator;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImageUrl()
    {
        if (!$this->imageUrl) {
            $this->imageUrl = '';

            $image = $this->category->getImageTeaser();

            if ($image) {
                $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

                if (is_string($image)) {
                    $this->imageUrl = $mediaBaseUrl . 'catalog/category/' . $image;
                } elseif (is_array($image) && isset($image[0]) && isset($image[0]['name'])) {
                    $this->imageUrl = $mediaBaseUrl . 'catalog/category/' . $image[0]['name'];
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Something went wrong while getting the image url.')
                    );
                }
            }
        }

        return $this->imageUrl;
    }

    /**
     * @return string
     */
    public function getHeadline()
    {
        return $this->category->getImageTeaserHeadline();
    }

    /**
     * @return string
     */
    public function getSubHeadline()
    {
        return $this->category->getImageTeaserSubheadline();

    }

    /**
     * @return string
     */
    public function getParagraph()
    {
        return $this->category->getImageTeaserParagraph();
    }

    /**
     * @return string
     */
    public function getButtonLabel()
    {
        return $this->category->getImageTeaserButtonLabel();
    }

    /**
     * @return string
     */
    public function getButtonUrl()
    {
        return $this->category->getImageTeaserButtonLink() ? $this->customUrlGenerator->generate($this->category->getImageTeaserButtonLink()) : '';
    }

    /**
     * @return string
     */
    public function getSrcSet()
    {

    }
}