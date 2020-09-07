<?php
namespace MageSuite\Navigation\Service\Mapper;

class CategoryMapper
{
    /**
     * @var \MageSuite\ContentConstructorFrontend\Service\MediaResolver
     */
    protected $mediaResolver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageSuite\Navigation\Service\Category\CustomUrlGenerator
     */
    protected $customUrlGenerator;

    /**
     * @var \MageSuite\Media\Service\SrcSetResolver
     */
    protected $srcSetResolver;

    protected $category = null;

    protected $rawImageUrl = null;

    public function __construct(
        \MageSuite\ContentConstructorFrontend\Service\MediaResolver $mediaResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\Navigation\Service\Category\CustomUrlGenerator $customUrlGenerator,
        \MageSuite\Media\Service\SrcSetResolver $srcSetResolver
    ) {
        $this->mediaResolver = $mediaResolver;
        $this->storeManager = $storeManager;
        $this->customUrlGenerator = $customUrlGenerator;
        $this->srcSetResolver = $srcSetResolver;
    }

    public function mapCategory($category)
    {
        if (!$category->getImageTeaser()) {
            return [];
        }

        $this->category = $category;

        $slide = [
            'cta' => [
                'href' => $this->getCtaLink(),
                'label' => $this->getCtaLabel()
            ],
            'decodedImage' => $this->getDecodedImage(),
            'image' => [
                'raw' => $this->getImageUrl(),
                'decoded' => $this->getDecodedImage()
            ],
            'description' => $this->getDescription(),
            'slogan' => $this->getSlogan()
        ];

        return [$slide];
    }

    protected function getDecodedImage()
    {
        return sprintf('{{media url="%s"}}', $this->getRawImageUrl());
    }

    public function getImageUrl()
    {
        $rawImageUrl = $this->getRawImageUrl();

        if (empty($rawImageUrl)) {
            return null;
        }

        $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaBaseUrl . $rawImageUrl;
    }

    public function getRawImageUrl()
    {
        if (!empty($this->rawImageUrl)) {
            $this->rawImageUrl;
        }

        $image = $this->category->getImageTeaser();
        $image = ltrim($image, '/');
        $image = str_replace('media/catalog/category/', '', $image);

        if (is_string($image)) {
            $this->rawImageUrl = 'catalog/category/' . $image;
        } elseif (is_array($image) && isset($image[0]) && isset($image[0]['name'])) {
            $this->rawImageUrl = 'catalog/category/' . $image[0]['name'];
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while getting the image url.')
            );
        }

        return $this->rawImageUrl;
    }

    /**
     * @return string
     */
    public function getSlogan()
    {
        return $this->category->getImageTeaserSlogan() ?? '';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->category->getImageTeaserDescription() ?? '';
    }

    /**
     * @return string
     */
    public function getCtaLabel()
    {
        return $this->category->getImageTeaserCtaLabel() ?? '';
    }

    /**
     * @return string
     */
    public function getCtaLink()
    {
        return $this->category->getImageTeaserCtaLink() ? $this->customUrlGenerator->generate($this->category->getImageTeaserCtaLink()) : '';
    }

    /**
     * @return string
     */
    public function getSrcSet()
    {
        $imageTeaserUrl = $this->getImageUrl();

        return $imageTeaserUrl ? $this->srcSetResolver->resolveSrcSetByDensity($imageTeaserUrl) : '';
    }
}
