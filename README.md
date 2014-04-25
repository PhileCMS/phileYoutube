phileYoutube
===========

A plugin that generates Youtube videos based on IDs. It can be used in your theme, or in your Markdown.

### Features

* use in Markdown
* use in templates with Twig
* auto grab the title

### 1.1 Installation (composer)
```
php composer.phar require phile/youtube:*
```
### 1.2 Installation (Download)

* Install [Phile](https://github.com/PhileCMS/Phile)
* Clone this repo into `plugins/phile/youtube`

### 2. Activation

After you have installed the plugin. You need to add the following line to your `config.php` file:

* add `$config['plugins']['phile\\youtube'] = array('active' => true);` to your `config.php`

### Markdown Usage

You can use this plugin in your Markdown files. It allows videos to be easy rendered without using any HTML in your Markdown.

#### Basic Examples:

Put the code in there. Watch the HTML spew out.

```html
youtube=8GLMe371RuI
```

Output:

```html
<div class="youtube-video"><iframe width="853" height="480" src="//www.youtube.com/embed/8GLMe371RuI?rel=0" frameborder="0" allowfullscreen=""></iframe><div class="youtube-title"><h2>All About PhileCMS</h2></div></div>
```

### Theme Usage

There will now be a new twig function called `youtube`. It takes a YouTube ID, and renders the HTML for the video!

#### Basic Examples:

Put the code in there. Watch the HTML spew out. *Assumes you have set `Video: 8GLMe371RuI` in your pages meta*.

```html
{{ youtube(meta.video) }}
```

Output:

```html
<div class="youtube-video"><iframe width="853" height="480" src="//www.youtube.com/embed/8GLMe371RuI?rel=0" frameborder="0" allowfullscreen=""></iframe><div class="youtube-title"><h2>All About PhileCMS</h2></div></div>
```

#### Config

Here are the settings. See the above output for where everything goes.

```
'wrapper_class' => 'youtube-video', // parent class for iframe
'title_class' => 'youtube-title', // div clas for video title
'show_title' => true, // get the title
'title_tag' => 'h2', // wrap the title in this tag, can be false for none
'video_height' => 480, // standard height
'video_width' => 853 // standard width
```

**Fixed heights and widths?**

Because clients are crazy and will try to add a video at a bad size. I like to control what they are doing in the theme. I know what size will work better than they do.

### Why Use?

Clients are crazy. You want to make sure the HTML output is good and not a huge mess. Also autofetching the title is sweet.
