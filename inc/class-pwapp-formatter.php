<?php

// set HTML Purifier
require_once PWAPP_PLUGIN_PATH . 'libs/htmlpurifier-4.6.0/library/HTMLPurifier.safe-includes.php';
require_once PWAPP_PLUGIN_PATH . 'libs/htmlpurifier-html5/htmlpurifier_html5.php';

if ( ! class_exists( 'PWAPP_Formatter' ) ) {

    /**
     * Class PWAPP_Formatter
     *
     * Contains different methods for formatting exported content
     */
    class PWAPP_Formatter
    {

        /**
         *
         * Init an HTMLPurifier object for filtering exported content and return it.
         *
         * @return HTMLPurifier
         *
         */
        public static function init_purifier()
        {

            $config = HTMLPurifier_Config::createDefault();

            $config->set('Cache.DefinitionImpl', null); // disable cache
            $config->set('Core.Encoding', 'UTF-8');

            $config->set('HTML.AllowedElements', 'div,a,p,ol,li,ul,img,blockquote,em,span,h1,h2,h3,h4,h5,h6,i,u,strong,b,sup,br,cite,iframe,small,video,audio,source');
            $config->set('HTML.AllowedAttributes', '*.style,*.width,*.height,*.src,a.href,a.target,img.srcset,img.sizes,iframe.frameborder,iframe.marginheight,iframe.marginwidth,iframe.scrolling,iframe.allowfullscreen,*.poster,*.preload,*.controls,*.type,*.data-type');

            $config->set('Attr.AllowedFrameTargets', '_blank, _parent, _self, _top');
            $config->set('URI.AllowedSchemes', array('http' => true, 'https' => true, 'mailto' => true, 'news' => true, 'tel' => true, 'callto' => true, 'skype' => true, 'sms' => true, 'whatsapp' => true));

            $config->set('HTML.SafeIframe', 1);
            $config->set('URI.SafeIframeRegexp', "%^(https?:)?(http?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player.vimeo.com|www\.dailymotion.com|w.soundcloud.com|fast.wistia.net|fast.wistia.com|wi.st|flickrit.com|www.spreaker.com|spreaker.com|instagram.com|www.instagram.com|embed.spotify.com|play.spotify.com|spotify.com|player.youku.com|youku.com)%");

            // extend purifier
            $Html5Purifier = new PWAPPHtmlPurifier();
            return $Html5Purifier->pwapp_extended_purifier($config);
        }


        /**
         *
         * Format an article's or comment's date
         *
         * The date_i18n() method will translate months and days names if the locale files are found in the
         * /wp-content/languages folder.
         *
         * @param $date_timestamp
         */
        public static function format_date($date_timestamp)
        {
            if (date('Y') == date('Y', $date_timestamp))
                return date_i18n('D, F d', $date_timestamp);

            return date_i18n('F d, Y', $date_timestamp);
        }


        /**
         *
         * This method can truncate a string up to a number of characters while preserving whole words and HTML tags.
         *
         * @param string $text String to truncate.
         * @param integer $length Length of returned string, including ellipsis.
         * @param string $ending Ending to be appended to the trimmed string.
         * @param boolean $exact If false, $text will not be cut mid-word
         * @param boolean $considerHtml If true, HTML tags would be handled correctly
         * @param boolean $stripTags If true, all the tags except some allowed tags will be removed
         *
         * @return string = Trimmed string.
         */
        public static function truncate_html($text, $length = 200, $ending = '...', $exact = false, $considerHtml = true, $stripTags = true)
        {

            if ($considerHtml) {

                // remove all unwanted script tags
                $text = self::remove_script_tags($text);

                // if no_images is true, remove all images from the content
                if ($stripTags)
                    $text = strip_tags($text, '<p><a><span><br><i><u><strong><b><sup><em>');

                // if the plain text is shorter than the maximum length, return the whole text
                if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                    return $text;
                }
                // splits all html-tags to scanable lines
                preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
                $total_length = strlen($ending);
                $open_tags = array();
                $truncate = '';

                foreach ($lines as $line_matchings) {
                    // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                    if (!empty($line_matchings[1])) {
                        // if it's an "empty element" with or without xhtml-conform closing slash
                        if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                            // do nothing
                            // if tag is a closing tag
                        } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                            // delete tag from $open_tags list
                            $pos = array_search($tag_matchings[1], $open_tags);
                            if ($pos !== false) {
                                unset($open_tags[$pos]);
                            }
                            // if tag is an opening tag
                        } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                            // add tag to the beginning of $open_tags list
                            array_unshift($open_tags, strtolower($tag_matchings[1]));
                        }
                        // add html-tag to $truncate'd text
                        $truncate .= $line_matchings[1];
                    }
                    // calculate the length of the plain text part of the line; handle entities as one character
                    $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                    if ($total_length + $content_length > $length) {
                        // the number of characters which are left
                        $left = $length - $total_length;
                        $entities_length = 0;
                        // search for html entities
                        if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                            // calculate the real length of all entities in the legal range
                            foreach ($entities[0] as $entity) {
                                if ($entity[1] + 1 - $entities_length <= $left) {
                                    $left--;
                                    $entities_length += strlen($entity[0]);
                                } else {
                                    // no more characters left
                                    break;
                                }
                            }
                        }
                        $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                        // maximum length is reached, so get off the loop
                        break;
                    } else {
                        $truncate .= $line_matchings[2];
                        $total_length += $content_length;
                    }
                    // if the maximum length is reached, get off the loop
                    if ($total_length >= $length) {
                        break;
                    }
                }
            } else {
                if (strlen($text) <= $length) {
                    return $text;
                } else {
                    $truncate = substr($text, 0, $length - strlen($ending));
                }
            }
            // if the words shouldn't be cut in the middle...
            if (!$exact) {
                // ...search the last occurance of a space...
                $spacepos = strrpos($truncate, ' ');
                if ($spacepos !== false) {
                    // ...and cut the text in this position
                    $truncate = substr($truncate, 0, $spacepos);
                }
            }
            // add the defined ending to the text
            $truncate .= $ending;
            if ($considerHtml) {
                // close all unclosed html-tags
                foreach ($open_tags as $tag) {
                    $truncate .= '</' . $tag . '>';
                }
            }
            return $truncate;
        }


        /**
         * Method used to remove script tags and everything in between them
         *
         * @param $text
         * @return string
         */
        public static function remove_script_tags($text)
        {
            $text = preg_replace("/<\s*script[^>]*>[\s\S]*?(<\s*\/script[^>]*>|$)/i", " ", $text);
            return $text;
        }

    }
}
