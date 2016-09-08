<?php

class WMPHtmlPurifier
{

    /**
     * Method used to extend html purifier to support HTML5 tags
     *
     * @param $config
     * @return HTMLPurifier
     */
    public function wmp_extended_purifier($config)
    {

        // check if the htmlpurifier class is loaded
        if (class_exists('HTMLPurifier')) {

            // set extra config
            $config->set('CSS.AllowTricky', true);
            $config->set('Cache.SerializerPath', '/tmp');

            // Set some HTML5 properties
            $config->set('HTML.DefinitionID', 'WPMP-VALIDATION-PAGES'); // unique id
            $config->set('HTML.DefinitionRev', 1);

            if ($def = $config->maybeGetRawHTMLDefinition()) {

                // More details: http://developers.whatwg.org/sections.html
                $def->addElement('section', 'Block', 'Flow', 'Common');
                $def->addElement('nav', 'Block', 'Flow', 'Common');
                $def->addElement('article', 'Block', 'Flow', 'Common');
                $def->addElement('aside', 'Block', 'Flow', 'Common');
                $def->addElement('header', 'Block', 'Flow', 'Common');
                $def->addElement('footer', 'Block', 'Flow', 'Common');

                // Content model actually excludes several tags, not modelled here
                $def->addElement('address', 'Block', 'Flow', 'Common');
                $def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');

                // More details: http://developers.whatwg.org/grouping-content.html
                $def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
                $def->addElement('figcaption', 'Inline', 'Flow', 'Common');

                // More details: http://developers.whatwg.org/the-video-element.html#the-video-element
                $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
                    'src' => 'URI',
                    'type' => 'Text',
                    'width' => 'Length',
                    'height' => 'Length',
                    'poster' => 'URI',
                    'preload' => 'Enum#auto,metadata,none',
                    'controls' => 'Text',
                ));

                // More details: http://developers.whatwg.org/the-video-element.html#the-audio-element
                $def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
                    'src' => 'URI',
                    'type' => 'Text',
                    'width' => 'Length',
                    'height' => 'Length',
                    'preload' => 'Enum#auto,metadata,none',
                    'controls' => 'Text',
                ));

                $def->addElement('source', 'Block', 'Flow', 'Common', array(
                    'src' => 'URI',
                    'type' => 'Text',
                ));

                // More details: http://developers.whatwg.org/text-level-semantics.html
                $def->addElement('s', 'Inline', 'Inline', 'Common');
                $def->addElement('var', 'Inline', 'Inline', 'Common');
                $def->addElement('sub', 'Inline', 'Inline', 'Common');
                $def->addElement('sup', 'Inline', 'Inline', 'Common');
                $def->addElement('mark', 'Inline', 'Inline', 'Common');
                $def->addElement('wbr', 'Inline', 'Empty', 'Core');

                // More details: http://developers.whatwg.org/edits.html
                $def->addElement('ins', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
                $def->addElement('del', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));

                // TinyMCE
                $def->addAttribute('img', 'data-mce-src', 'Text');
                $def->addAttribute('img', 'data-mce-json', 'Text');

                // Responsive images
                $def->addAttribute('img', 'srcset', 'Text');
                $def->addAttribute('img', 'sizes', 'Text');

                // Others
                $def->addAttribute('iframe', 'allowfullscreen', 'Text'); // maybe type should be "Bool" instead if "Text" ?
                $def->addAttribute('table', 'height', 'Text');
                $def->addAttribute('td', 'border', 'Text');
                $def->addAttribute('th', 'border', 'Text');
                $def->addAttribute('tr', 'width', 'Text');
                $def->addAttribute('tr', 'height', 'Text');
                $def->addAttribute('tr', 'border', 'Text');

                $def->addAttribute('img', 'data-type', 'Text');

                // add custom CSS3 properties
                $css_definition = $config->getDefinition('CSS');

                $info['min-width'] = $info['min-height'] = new HTMLPurifier_AttrDef_CSS_Composite(array(
                        new HTMLPurifier_AttrDef_CSS_Length('0'),
                        new HTMLPurifier_AttrDef_CSS_Percentage(true)
                    )
                );

                // wrap all new attr-defs with decorator that handles !important
                // CSS.AllowImportant should be set to 1 to allow !important
                $allow_important = $config->get('CSS.AllowImportant');
                foreach ($info as $k => $v) {
                    $css_definition->info[$k] = new HTMLPurifier_AttrDef_CSS_ImportantDecorator($v, $allow_important);
                }
            }

            return new HTMLPurifier($config);

        }

        // by default return false;   
    }
}
