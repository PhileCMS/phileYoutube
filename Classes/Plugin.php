<?php
/**
 * Plugin class
 */
namespace Phile\Plugin\Phile\Youtube;

/**
 * Render a youtube video based on it's ID
 * Usage: {{ youtube(id) }}
 * Usage: youtube=id
 */
class Plugin extends \Phile\Plugin\AbstractPlugin implements \Phile\Gateway\EventObserverInterface {

    /**
     * the constructor
     */
    public function __construct() {
        \Phile\Core\Event::registerEvent('template_engine_registered', $this);
        \Phile\Core\Event::registerEvent('after_parse_content', $this);
    }

    /**
     * generate video-embedding HTML
     *
     * @param YoutubeVideoInfo $video
     * @return string
     */
    public function getVideoHtml(YoutubeVideoInfo $video) {
        $title = '';
        if ($this->settings['show_title']) {
            $title = $this->getTitleHtml($video);
        }
        $id = $video->getId();
        return "<div class=\"{$this->settings['wrapper_class']}\"><iframe width=\"{$this->settings['video_width']}\" height=\"{$this->settings['video_height']}\" src=\"//www.youtube.com/embed/{$id}?rel=0\" frameborder=\"0\" allowfullscreen></iframe>{$title}</div>";
    }

    /**
     * generate title HTML
     *
     * @param YoutubeVideoInfo $video
     * @return string
     */
    protected function getTitleHtml(YoutubeVideoInfo $video) {
        $title = $video->getTitle();
        if (empty($title)) {
            return '';
        }
        $tags = ['',''];
        if ($this->settings['title_tag']) {
            $tags = [
              "<{$this->settings['title_tag']}>",
              "</{$this->settings['title_tag']}>"
            ];
        }
        $title = "<div class=\"{$this->settings['title_class']}\">{$tags[0]}{$title}{$tags[1]}</div>";
        return $title;
    }

	/**
	 * event method
	 * @param string $eventKey
	 * @param null   $data
	 *
	 * @return mixed|void
	 */
	public function on($eventKey, $data = null) {
		if ($eventKey === 'template_engine_registered') {
			$youtube = new \Twig_SimpleFunction('youtube', function ($id) {
				$video = new YoutubeVideoInfo($id);
				return $this->getVideoHtml($video);
			});
			$data['engine']->addFunction($youtube);
		} else if ($eventKey === 'after_parse_content') {
			// store the starting content
			$content = $data['content'];
			// this parse happens after the markdown
			// which means that the potential ID is wrapped
			// in p tags
			$regex = "/(<p>)(youtube)=(.*?)(<\/p>)/";
			// add the modified content back in the data
			$data['content'] = preg_replace_callback($regex, function($matches) {
				$video = new YoutubeVideoInfo($matches[3]);
				return $this->getVideoHtml($video);
			}, $content);
		}
	}
}
