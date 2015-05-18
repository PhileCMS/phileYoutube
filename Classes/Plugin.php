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

    protected $settings = [
        'queryDefaults' => []
    ];

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
     * @param string $string
     * @return string
     */
    public function getVideoHtml($string) {
        list($id, $query) = $this->parseQuery($string);

        $options = [];
        $options['query'] = $query;
        $defaults = ['query' => $this->settings['queryDefaults']];
        $options = array_replace_recursive($defaults, $options);

        $video = new YoutubeVideoInfo($id);

        $title = '';
        if ($this->settings['show_title']) {
            $title = $this->getTitleHtml($video);
        }

        $query = http_build_query($options['query']);
        return "<div class=\"{$this->settings['wrapper_class']}\"><iframe width=\"{$this->settings['video_width']}\" height=\"{$this->settings['video_height']}\" src=\"//www.youtube.com/embed/{$id}?{$query}\" frameborder=\"0\" allowfullscreen></iframe>{$title}</div>";
    }

    /**
     * parse video id string for query params
     *
     * <id>?foo=bar&baz=zap becomes [<id>, ['foo' => 'bar', 'baz' => 'zap']]
     *
     * @param $string
     * @return array
     */
    protected function parseQuery($string) {
        $parts = parse_url($string);
        if (!$parts || empty($parts['query'])) {
            return [$string, []];
        }

        // ampersands in page content are escaped by markup (markdown) parser
        $parts['query'] = str_replace('&amp;', '&', $parts['query']);

        $query = [];
        parse_str($parts['query'], $query);
        return [$parts['path'], $query];
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
     *
     * @param string $eventKey
     * @param null $data
     *
     * @return mixed|void
     */
    public function on($eventKey, $data = null) {
        if ($eventKey === 'template_engine_registered') {
            $youtube = new \Twig_SimpleFunction('youtube', function ($string) {
                return $this->getVideoHtml($string);
            });
            $data['engine']->addFunction($youtube);
        } else {
            if ($eventKey === 'after_parse_content') {
                // store the starting content
                $content = $data['content'];
                // this parse happens after the markdown
                // which means that the potential ID is wrapped
                // in p tags
                $regex = "/(<p>)(youtube)=(.*?)(<\/p>)/";
                // add the modified content back in the data
                $data['content'] = preg_replace_callback($regex, function ($matches) {
                    return $this->getVideoHtml($matches[3]);
                }, $content);
            }
        }
    }
}
