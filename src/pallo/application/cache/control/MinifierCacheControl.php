<?php

namespace pallo\application\cache\control;

/**
 * Cache control implementation for the asset minifier
 */
class MinifierCacheControl extends AbstractCacheControl {

    /**
     * Name of this cache control
     * @var string
     */
    const NAME = 'minifier';

    /**
     * Array with the minifiers to clear
     * @var array
     */
    private $minifiers;

    /**
     * Constructs a new image cache control
     * @param pallo\web\image\ImageUrlGenerator $imageUrlGenerator
     * @return null
     */
    public function __construct(array $minifiers) {
        $this->minifiers = $minifiers;
    }

    /**
     * Gets whether this cache is enabled
     * @return boolean
     */
    public function isEnabled() {
        return true;
    }

    /**
	 * Clears this cache
	 * @return null
     */
    public function clear() {
        foreach ($this->minifiers as $minifier) {
            $minifier->clearCache();
        }
    }

}