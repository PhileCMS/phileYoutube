<?php

namespace Phile\Plugin\Phile\Youtube;

use Phile\Core\ServiceLocator;

class YoutubeVideoInfo {

    /**
     * @var video id
     */
    protected $id;

    /**
     * @var array video info
     */
    protected $info = [];

    /**
     * constructor
     *
     * @param $id video id
     */
    public function __construct($id) {
        $this->id = $id;
    }

    /**
     * get video id
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * get video title
     *
     * @return string|null
     */
    public function getTitle() {
        $info = $this->getVideoInfo();
        return isset($info['title']) ? $info['title'] : null;
    }

    /**
     * request video info from youtube-API
     *
     * @return mixed
     */
    protected function getVideoInfo() {
        if ($this->info) {
            return;
        }

        /** @var \Phile\ServiceLocator\CacheInterface $cache */
        $cache = null;
        if (ServiceLocator::hasService('Phile_Cache')) {
            $cache = ServiceLocator::getService('Phile_Cache');
        }
        $cacheKey = 'plugin.phile.youtube.' . md5($this->id);
        if ($cache && $cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $url = 'http://www.youtube.com/oembed';
        $parts = [
          'url' => 'http://www.youtube.com/watch',
          'v' => $this->id,
          'format' => 'json'
        ];
        $url = $url . '?url=' . $parts['url'];
        unset($parts['url']);
        foreach ($parts as $key => $value) {
            $parts[$key] = "$key=$value";
        }
        $request = $url . '?' . implode('&amp;', $parts);
        // Use '@' to suppress errors.
        $video = @file_get_contents($request);
        $video = json_decode($video, true);

        if (!$video) {
            $video = [];
        }
        if ($cache) {
            $cache->set($cacheKey, $video);
        }
        $this->info = $video;
        return $this->info;
    }

}
