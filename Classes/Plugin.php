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
	 * the consructor
	 */
	public function __construct() {
		\Phile\Event::registerEvent('template_engine_registered', $this);
		\Phile\Event::registerEvent('after_parse_content', $this);
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public function get_video($id) {
		$title = "";
		if ($this->settings['show_title']) {
			// returns a single line of XML that contains the video title.
			// Not a giant request. Use '@' to suppress errors.
			$videoTitle = @file_get_contents("http://gdata.youtube.com/feeds/api/videos/{$id}?v=2&fields=title");
			// look for that title tag and get the insides
			preg_match("/<title>(.+?)<\/title>/is", $videoTitle, $titleOfVideo);
			$tags = array('','');
			if ($this->settings['title_tag']) {
				$tags = array("<{$this->settings['title_tag']}>", "</{$this->settings['title_tag']}>");
			}
			$title = "<div class=\"{$this->settings['title_class']}\">{$tags[0]}{$titleOfVideo[1]}{$tags[1]}</div>";
		}
		return "<div class=\"{$this->settings['wrapper_class']}\"><iframe width=\"{$this->settings['video_width']}\" height=\"{$this->settings['video_height']}\" src=\"//www.youtube.com/embed/{$id}\" frameborder=\"0\" allowfullscreen></iframe>{$title}</div>";
	}

	/**
	 * event method
	 * @param string $eventKey
	 * @param null   $data
	 *
	 * @return mixed|void
	 */
	public function on($eventKey, $data = null) {
		if ($eventKey == 'template_engine_registered') {
			$youtube = new \Twig_SimpleFunction('youtube', function ($id) {
				return $this->get_video($id);
			});
			$data['engine']->addFunction($youtube);
		} else if ($eventKey == 'after_parse_content') {
			// store the starting content
			$content = $data['content'];
			// this parse happens after the markdown
			// which means that the potential ID is wrapped
			// in p tags
			$regex = "/(<p>)(youtube)=(.*?)(<\/p>)/";
			// add the modified content back in the data
			$data['content'] = preg_replace_callback($regex, function($matches) {
				return $this->get_video($matches[3]);
			}, $content);
		}
	}
}
