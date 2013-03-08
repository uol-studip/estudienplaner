<?php
require_once dirname(__file__).'/zsb_controller.php';
require_once __DIR__ . '/../models/Textbaustein.class.php';

class ZsbTextbausteineController extends ZsbController {
    
    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        if (!PersonalRechte::isRoot()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
        if (Request::isAjax()) {
            $this->set_layout(null);
        }
        Navigation::activateItem('/zsb/textbausteine');
    }
    
    public function index_action() {
        $this->textbausteine = Textbaustein::loadAll();
    }
    
    public function edit_action ($id = null)
    {
        if (Request::isPost()) {
            $id   = $id ?: md5(uniqid('textbaustein', true));
            $code    = Request::get('code');
            $title   = Request::get('title');
            $content = Request::get('content');
            
            Textbaustein::store($id, $code, $title, $content);
            PageLayout::postMessage(MessageBox::success(_('Der Textbaustein wurde gespeichert.')));
            $this->redirect('zsb_textbausteine/index');
            return;
        }

        if ($id === null) {
            $this->code    = '';
            $this->title   = '';
            $this->content = '';
        } else {
            $t = Textbaustein::load($id);
            $this->code    = $t['code'];
            $this->title   = $t['title'];
            $this->content = $t['content'];
        }
    }
    
    public function delete_action($id)
    {
        Textbaustein::delete($id);
        PageLayout::postMessage(MessageBox::success(_('Der Textbaustein wurde gelöscht.')));
        $this->redirect('zsb_textbausteine/index');
    }
}
