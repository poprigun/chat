<?php

namespace poprigun\chat;

use yii\web\AssetBundle;

class ChatAssets extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\YiiAsset',
    ];

    /**
     * Set up CSS and JS asset arrays based on the base-file names
     *
     * @param string $type whether 'css' or 'js'
     * @param array $files the list of 'css' or 'js' basefile names
     */
    protected function setupAssets($type, $files = [])
    {
        $srcFiles = [];
        $minFiles = [];
        foreach ($files as $file) {
            $srcFiles[] = "{$file}.{$type}";
            $minFiles[] = "{$file}.min.{$type}";
        }
        if (empty($this->$type)) {
            $this->$type = $srcFiles;
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setSourcePath(__DIR__ . '/assets');
        $this->setupAssets('css', ['chat']);
        $this->setupAssets('js', ['chat','date_format']);
        parent::init();
    }

    /**
     * Sets the source path if empty
     * @param string $path the path to be set
     */
    protected function setSourcePath($path)
    {
        if (empty($this->sourcePath)) {
            $this->sourcePath = $path;
        }
    }
}