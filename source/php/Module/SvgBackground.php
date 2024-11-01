<?php

namespace ModularitySvgBackground\Module;

use ModularitySvgBackground\Helper\CacheBust;

/**
 * Class SvgBackground
 * @package SvgBackground\Module
 */
class SvgBackground extends \Modularity\Module
{
    public $slug = 'svg-background';

    public $supports = array();

    private const EXTRA_SETTINGS = [
        'noTopMargin',
        'noBottomMargin',
        'noHeight',
        'flipVertically',
        'flipHorizontally',
    ];

    public function init()
    {
        $this->nameSingular = __("SVG-bakgrund", 'modularity-svg-background');
        $this->namePlural = __("SVG-bakgrunder", 'modularity-svg-background');
        $this->description = __("Display SVG background images that can overflow to upper or lower modules.", 'modularity-svg-background');
    }

    /**
     * Data array
     * @return array $data
     */
    public function data(): array
    {
        $data = array();

        //Append field config
        $data = array_merge($data, (array) \Modularity\Helper\FormatObject::camelCase(
            get_fields($this->ID)
        ));

        $svg_path = get_attached_file($data['svgFile']);
        $svg_code = file_get_contents($svg_path);

        $data['svgCode'] = $svg_code;

        $baseClass = "modularity-{$this->post_type}";

        $data['instanceClass'] = $baseClass . '-' . $this->ID;
        $data['baseClass'] = $baseClass;

        $classes = [$baseClass . '-wrapper'];

        $hasTruthyExtraSetting = !empty(array_filter(
            array_intersect_key($data, array_flip(self::EXTRA_SETTINGS))
        ));

        $ID = $this->ID;

        if ($hasTruthyExtraSetting) {
            add_filter('Modularity/Display/BeforeModule::classes', function($classes, $args, $post_type, $current_ID) use ($data, $ID) {
                if ($post_type === 'mod-svg-background' && $current_ID === $ID) {
                    if ($data['noBottomMargin']) {
                        $classes[] = 'no-bottom-margin';
                    }

                    if ($data['noTopMargin']) {
                        $classes[] = 'no-top-margin';
                    }

                    if ($data['noHeight']) {
                        $classes[] = 'no-height';
                        $classes[] = $data['overlap'] === 'up' ? 'overlap-up' : 'overlap-down';
                    }

                    if ($data['flipHorizontally']) {
                        $classes[] = 'flip-horizontally';
                    }

                    if ($data['flipVertically']) {
                        $classes[] = 'flip-vertically';
                    }
                }

                return $classes;
            }, 10, 4);
        }

        $data['classes'] = implode(' ', $classes);

        return $data;
    }

    /**
     * Blade Template
     * @return string
     */
    public function template(): string
    {
        return "svg-background.blade.php";
    }

    /**
     * Style - Register & adding css
     * @return void
     */
    public function style()
    {
        //Register custom css
        wp_register_style(
            'modularity-svg-background',
            MODULARITY_SVG_BACKGROUND_URL . '/dist/' . CacheBust::name('css/modularity-svg-background.css'),
            null,
            '1.0.0'
        );

        //Enqueue
        wp_enqueue_style('modularity-svg-background');
    }

    /**
     * Script - Register & adding scripts
     * @return void
     */
    public function script()
    {
        //Register custom css
        wp_register_script(
            'modularity-svg-background',
            MODULARITY_SVG_BACKGROUND_URL . '/dist/' . CacheBust::name('js/modularity-svg-background.js'),
            null,
            '1.0.0'
        );

        //Enqueue
        wp_enqueue_script('modularity-svg-background');
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}
