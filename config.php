<?php
/**
 * config file
 */
return array(
	'wrapper_class' => 'youtube-video', // parent class for iframe
	'title_class' => 'youtube-title', // div clas for video title
	'show_title' => true, // get the title
	'title_tag' => 'h2', // wrap the title in this tag, can be false for none
	'video_height' => 480, // standard height
	'video_width' => 853, // standard width
  /**
   * additional player parameters
   *
   * see: https://developers.google.com/youtube/player_parameters#Parameters
   */
	'queryDefaults' => [
		'rel' => 0
    ],
);
