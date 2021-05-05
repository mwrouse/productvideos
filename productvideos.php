<?php
if ( ! defined('_TB_VERSION_')) {
    exit;
}


class ProductVideos extends Module
{
    /* @var boolean error */
    protected $hooksList = [];

    protected static $cachedHooksList;

    protected $_tabs = [
        'ProductVideos' => 'ProductVideos', // class => label
    ];

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
            'displayProductVideosTab',
            'displayProductTabContent',
            'displayAdminProductsExtra',
            'actionProductUpdate'
        ];

        parent::__construct();

        $this->displayName = $this->l('Product Videos');
        $this->description = $this->l('Add videos of your product');
    }

    /**
     * Standard Hook for displaying content
     */
    public function hookDisplayProductVideosTab($params)
    {
        $product = $params['product'];
        if (!isset($product) || !isset($product->id))
            return;

        $videos = $this->getVideosForProduct($product->id);

        $this->context->smarty->assign([
            'videos' => $videos
        ]);
        return $this->display(__FILE__, 'productvideos.tpl');
    }

    /**
     * Default hook for tieing into to the product tabs
     */
    public function hookDisplayProductTabContent($params)
    {
        return $this->hookDisplayProductVideosTab($params);
    }


    /**
     * Hook to add a tab to the product page (when editing a product)
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $productId = Tools::getValue('id_product');
        if (!isset($productId))
            return "Could not load Product ID from Tools::getValue('id_product')";

        $videos = $this->getVideosForProduct($productId);

        $this->context->smarty->assign([
            'videos' => $videos
        ]);
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

            $video_titles = Tools::getValue('video_titles');
            $video_urls = Tools::getValue('video_urls');
            $deleted_videos = Tools::getValue('deleted_videos');

            $this->removeVideos($deleted_videos);

            $max = count($video_titles);
            if (count($video_urls) < $max)
                $max = count($video_urls);

            $saved = [];

            for ($i = 0; $i < $max; $i++)
            {
                $video = [
                    'title' => $video_titles[$i],
                    'url' => $video_urls[$i]
                ];
                array_push($saved, $video);
            }

            $this->saveVideosForProduct($productId, $saved);
        }
    }


    /**
     * Returns all videos for a product
     */
    public function getVideosForProduct($productId)
    {
        $result = Db::getInstance()->ExecuteS('
            SELECT `id_video`, `id_product`, `title`, `url` FROM `'._DB_PREFIX_.$this->table_name.'` WHERE `id_product`='.$productId.';
        ');

        if (!$result)
            return [];

        foreach ($result as $i => $video)
        {
            $result[$i] = $this->addEmbeddCode($video);
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
                        'id_product' => $productId,
                        'title' => $video['title'],
                        'url' => $video['url']
                    ]
                );
            }
        }
    }


    /**
     * Determines if a video already exists for a product
     */
    public function doesProductAlreadyHaveVideo($productId, $video)
    {
        $doesURLExist = Db::getInstance()->getValue('SELECT id_video FROM '._DB_PREFIX_.$this->table_name.' WHERE (id_product='.$productId.') AND (url="'.$video['url'].'")');
        if (!$doesURLExist)
            return false;

        return true;
    }


    /**
     * Removes all videos in the array
     */
    public function removeVideos($videoIds)
    {
        if (!is_array($videoIds))
            return;

        foreach ($videoIds as $videoId)
        {
            Db::getInstance()->delete(
                $this->table_name,
                'id_video='.$videoId
            );
        }
    }




    /**
     * Adds an embedded code for the video
     */
    private function addEmbeddCode($video)
    {
        return $video;
    }


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
                `id_product` INT( 12 ) NOT NULL,
                `title` VARCHAR( 255 ) NOT NULL,
                `url` VARCHAR(1000) NOT NULL,
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
