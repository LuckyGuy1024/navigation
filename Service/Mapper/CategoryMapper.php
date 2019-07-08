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

    protected $imageUrl = null;

    public function __construct(
        \MageSuite\ContentConstructorFrontend\Service\MediaResolver $mediaResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\Navigation\Service\Category\CustomUrlGenerator $customUrlGenerator,
        \MageSuite\Media\Service\SrcSetResolver $srcSetResolver

    ){
        $this->mediaResolver = $mediaResolver;
        $this->storeManager = $storeManager;
        $this->customUrlGenerator = $customUrlGenerator;
        $this->srcSetResolver = $srcSetResolver;
    }

    public function mapCategory($category)
    {
        if(!$category->getImageTeaser()){
            return [];
        }

        $this->category = $category;

        $slide = [
            'cta' => [
                'href' => $this->getButtonUrl(),
                'label' => $this->getButtonLabel()
            ],
            'decodedImage' => $this->getDecodedImage(),
            'image' => [
                'decoded' => $this->getDecodedImage(),
                'headline' => $this->getHeadline(),
                'subheadline' => $this->getSubHeadline()
            ],
            'description' => $this->getParagraph(),
            'slogan' => $this->getHeadline()
        ];

        return [$slide];
    }

    protected function getDecodedImage()
    {
        return sprintf('{{media url="%s"}}', $this->getImageUrl());
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
        return $this->category->getImageTeaserHeadline() ?? '';
    }

    /**
     * @return string
     */
    public function getSubHeadline()
    {
        return $this->category->getImageTeaserSubheadline() ?? '';

    }

    /**
     * @return string
     */
    public function getParagraph()
    {
        return $this->category->getImageTeaserParagraph() ?? '';
    }

    /**
     * @return string
     */
    public function getButtonLabel()
    {
        return $this->category->getImageTeaserButtonLabel() ?? '';
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
        $imageTeaserUrl = $this->getImageUrl();

        return $imageTeaserUrl ? $this->srcSetResolver->resolveSrcSetByDensity($imageTeaserUrl) : '';
    }
}