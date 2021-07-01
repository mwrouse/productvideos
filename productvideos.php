<?php
if ( ! defined('_TB_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'productvideos/vendor/autoload.php';

class ProductVideos extends Module
{
    /* @var boolean error */
    protected $hooksList = [];

    protected static $cachedHooksList;

    protected $_tabs = [
        'ProductVideos' => 'ProductVideos', // class => label
    ];

    const ENABLE_TAB_HOOK = "productvideos_enable_tabhook";


    public function __construct()
    {
        $this->name = 'productvideos';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Michael Rouse';
        $this->tb_min_version = '1.0.0';
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->need_instance = 0;
        $this->table_name = 'productvideos';
        $this->bootstrap = true;

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => '1.6.99.99'];

        // List of hooks
        $this->hooksList = [
            'displayProductVideos',
            'displayProductTabContent',
            'displayAdminProductsExtra',
            'actionProductUpdate',
            'actionAddVideosToImages'
        ];

        parent::__construct();

        $this->displayName = $this->l('Product Videos');
        $this->description = $this->l('Add videos of your product');
    }

    /**
     * Standard Hook for displaying content
     */
    public function hookDisplayProductVideos($params)
    {
        $product = $params['product'];
        if (!isset($product) || is_null($product))
            return;

        $this->addVideosToSmarty($this->get($product, 'id', 'id_product'));

        return $this->display(__FILE__, 'hookDisplayProductVideos.tpl');
    }

    /**
     * Default hook for tieing into to the product tabs
     */
    public function hookDisplayProductTabContent($params)
    {
        $isEnabled = $this->isProductTabHookEnabled();
        if (!$isEnabled)
            return;

        $product = $params['product'];
        if (!isset($product) || !isset($product->id))
            return;

        $hasVideos = $this->addVideosToSmarty($product->id);

        if (!$hasVideos)
            return "";

        return $this->display(__FILE__, 'hookDisplayProductTabContent.tpl');
    }


    /**
     * Adds videos to the product images
     */
    public function hookActionAddVideosToImages($params)
    {
        $product = $params['product'];
        $images = $params['images'];

        if (!isset($product) || is_null($product) || !isset($images) || is_null($images))
            return;

        $attributes = [
            [
                'name' => 'height',
                'value' => $params['height']
            ]
        ];

        foreach ($images as $k => $v) {
            $images[$k]['is_video'] = 0;
        }


        $videos = $this->getVideosForProduct($product->id, $attributes);
        foreach ($videos as $video) {
            if ($video['included']) {
                $images[$video['id']] = [
                    'cover' => 0,
                    'legend' => $video['title'],
                    'id_image' => $video['id'],
                    'thumbnail' => $video['thumbnail_url'],
                    'position' => count($images) + 1,
                    'is_video' => true,
                    'embed' => base64_encode($video['embed']),
                ];
            }
        }

        return serialize($images);
    }


    /**
     * Hook to add a tab to the product page (when editing a product)
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $productId = Tools::getValue('id_product');
        if (!isset($productId))
            return "Could not load Product ID from Tools::getValue('id_product')";

        $this->addVideosToSmarty($productId);

        return $this->display(__FILE__, 'views/admin/hook/displayAdminProductsExtra.tpl');
    }


    /**
     * When a product is updated (saved)
     */
    public function hookActionProductUpdate($params)
    {
        if (Tools::isSubmit('submitAddproduct') || Tools::isSubmit('submitAddproductAndStay'))
        {
            $productId = Tools::getValue('id_product');

            $video_ids = Tools::getValue('video_ids');
            //$video_titles = Tools::getValue('video_titles');
            //$video_urls = Tools::getValue('video_urls');
            $included_videos = Tools::getValue('video_image_include');

            $deleted_videos = Tools::getValue('deleted_videos');
            $this->removeVideos($deleted_videos);

            $max = count($video_ids);
            $saved = [];

            for ($i = 0; $i < $max; $i++)
            {
                $id = $video_ids[$i];
                $video = [
                    'id' => $id,
                    'title' => Tools::getValue($id.'_title'),
                    'url' => Tools::getValue($id.'_url'),
                    'included' => in_array($id, $included_videos) ? 1 : 0,
                ];
                array_push($saved, $video);
            }

            $this->saveVideosForProduct($productId, $saved);
        }
    }


    /**
     * Returns all videos for a product
     */
    public function getVideosForProduct($productId, $attributes = [])
    {
        if (is_null($productId))
            return [];

        $result = Db::getInstance()->ExecuteS('
            SELECT `id_video`, `id`, `id_product`, `title`, `url`, `included` FROM `'._DB_PREFIX_.$this->table_name.'` WHERE `id_product`='.$productId.';
        ');

        if (!$result)
            return [];

        foreach ($result as $i => $video)
        {
            $result[$i] = $this->addEmbeddCode($video, $attributes);
        }

        return $result;
    }


    /**
     * Removes all the videos for a product
     */
    public function saveVideosForProduct($productId, $videos)
    {
        foreach ($videos as $video)
        {
            if (!$this->doesProductAlreadyHaveVideo($productId, $video)) {
                Db::getInstance()->insert(
                    $this->table_name,
                    [
                        'id' => $video['id'],
                        'id_product' => $productId,
                        'title' => pSQL($video['title']),
                        'url' => $video['url'],
                        'included' => $video['included'],
                    ]
                );
            }
            else {
                Db::getInstance()->update(
                    $this->table_name,
                    [
                        'id' => $video['id'],
                        'id_product' => $productId,
                        'title' => pSQL($video['title']),
                        'url' => $video['url'],
                        'included' => $video['included']
                    ],

                        'id="'.$video['id'].'"'

                );
            }
        }
    }


    /**
     * Determines if a video already exists for a product
     */
    public function doesProductAlreadyHaveVideo($productId, $video)
    {
        $doesURLExist = Db::getInstance()->getValue('SELECT id_video FROM '._DB_PREFIX_.$this->table_name.' WHERE (id_product='.$productId.') AND (id="'.$video['id'].'")');
        if (!$doesURLExist)
            return false;

        return true;
    }


    /**
     * Removes all videos in the array
     */
    public function removeVideos($videoIds)
    {
        //Db::getInstance()->delete($this->table_name, 'id_product='.$productId);
        if (!is_array($videoIds) || count($videoIds) == 0)
            return;

        foreach ($videoIds as $videoId)
        {
            Db::getInstance()->delete(
                $this->table_name,
                'id="'.$videoId.'"'
            );
        }
    }

    /**
     * Adds videos to smarty for the template
     */
    private function addVideosToSmarty($productId)
    {
        $videos = $this->getVideosForProduct($productId);

        $this->context->smarty->assign([
            'videos' => $videos
        ]);

        return count($videos) > 0;
    }



    /**
     * Adds an embedded code for the video
     */
    private function addEmbeddCode($video, $forcedAttributes = [])
    {
        if (!isset($this->MediaEmbed)) {
            $this->MediaEmbed = new MediaEmbed\MediaEmbed();
        }

        $MediaObject = $this->MediaEmbed->parseUrl($video['url']);

        $attributes = $this->getSavedAttributes();
        foreach ($attributes as $attr) {
            $MediaObject->setAttribute($attr['name'], $attr['value']);
        }

        foreach ($forcedAttributes as $attr) {
            $MediaObject->setAttribute($attr['name'], $attr['value']);
        }

        $video['embed'] = $MediaObject->getEmbedCode();
        $video['thumbnail_url'] = $MediaObject->image();
        return $video;
    }



    private function get($product, $key, $arrayKey = null)
    {
        if (is_null($arrayKey))
            $arrayKey = $key;

        if (is_object($product))
            return $product->{$key};

        return $product[$arrayKey];
    }




    /**********************
     * Module Config Page *
     **********************/


    /**
     * @return string
     *
     * @since 1.0.0
     */
    public function getContent()
    {
        try {
            $content = $this->postProcess();

            $baseLink = AdminController::$currentIndex.'&token=' . Tools::getAdminTokenLite('AdminModules') . '&module_name=' . $this->name;

            $this->context->smarty->assign([
                    'attributes' => $this->getSavedAttributes(),
                    'enabled' => $this->isProductTabHookEnabled(),
                    'post_action' => $baseLink.'&configure='.$this->name
            ]);

            $content .= $this->display(__FILE__, 'views/admin/config.tpl');

            return $content;
        } catch (Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();

            return '';
        }
    }


    /**
     * @return bool|string
     *
     * @since 1.0.0
     * @throws PrestaShopException
     * @throws HTMLPurifier_Exception
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitStoreConf') ) {
            $languages = Language::getLanguages(false);
            $values = [];
            $updateImagesValues = false;

            $names = Tools::getValue('attribute_names');
            $values = Tools::getValue('attribute_values');

            if (!is_array($names))
                $names = [$names];
            if (!is_array($values))
                $values = [$values];

            $max = count($names);
            if (count($values) < $max)
                $max = count($values);

            $final = [];

            for ($i = 0; $i < $max; $i++) {
                array_push($final, [
                    'name' => $names[$i],
                    'value' => $values[$i]
                ]);
            }

            Configuration::updateValue('video_attributes', serialize($final), true);

            $isEnabled = Tools::getValue(static::ENABLE_TAB_HOOK);
            Configuration::updateValue(static::ENABLE_TAB_HOOK, $isEnabled);

            return $this->displayConfirmation($this->l('The attributes have been updated.'));
        }

        return '';
    }

    /**
     * @return array
     *
     * @since 1.0.0
     * @throws PrestaShopException
     */
    public function getSavedAttributes()
    {
        try {
            $savedAttributes = Configuration::get('video_attributes');
            if (!isset($savedAttributes))
                return [];

            $result = unserialize($savedAttributes);

            if (!is_array($result))
                return [];

            return $result;
        }
        catch (Exception $e) {
            Logger::addLog("ProductVideos hook error: {$e->getMessage()}");
            return [];
        }
    }


    /**
     * Checks if the product tab hook is enabled
     */
    public function isProductTabHookEnabled()
    {
        try {
            $isEnabled = Configuration::get(static::ENABLE_TAB_HOOK);
            if (!isset($isEnabled))
                return true;

            return $isEnabled;
        }
        catch (Exception $e) {
            Logger::addLog("ProductVideos isProductTabHookEnabled error: {$e->getMessage()}");
            return true;
        }
    }



    /***************************
     * Installing/Uninstalling *
     ***************************/



    public function install()
    {
        if ( ! parent::install()
            || ! $this->_createDatabases()
        ) {
            return false;
        }

        foreach ($this->hooksList as $hook) {
            if ( ! $this->registerHook($hook)) {
                return false;
            }
        }

        Configuration::updateValue('video_attributes', [], true);
        Configuration::updateValue(static::ENABLE_TAB_HOOK, true);

        return true;
    }

    public function uninstall()
    {
        if ( ! parent::uninstall()
            || ! $this->_eraseDatabases()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Create Database Tables
     */
    private function _createDatabases()
    {
        $sql = 'CREATE TABLE  `'._DB_PREFIX_.$this->table_name.'` (
                `id_video` INT( 12 ) AUTO_INCREMENT,
                `id` VARCHAR(255) NOT NULL,
                `id_product` INT( 12 ) NOT NULL,
                `title` VARCHAR( 255 ) NOT NULL,
                `url` VARCHAR(1000) NOT NULL,
                `included` BOOLEAN DEFAULT 0,
                PRIMARY KEY (  `id_video` )
                ) ENGINE =' ._MYSQL_ENGINE_;

        if (!Db::getInstance()->Execute($sql)) {
            return false;
        }

        return true;
    }

    /**
     * Remove Database Tables
     */
    private function _eraseDatabases()
    {
        if (!Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.$this->table_name.'`')) {
            return false;
        }

        return true;
    }
}
