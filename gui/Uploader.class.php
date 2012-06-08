<?php


class Uploader {
    
    public function __construct() {
        
    }
    
    public function render() {
        $factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates/');
        $template = $factory->open('check.php');
        $template->set_layout('../../../../../templates/layouts/base_without_infobox');
        $template->set_attribute('js_files', $js_files);
        print $template->render();
    }
}