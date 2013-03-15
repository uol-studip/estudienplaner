<?php
require_once dirname(__file__).'/zsb_controller.php';
require_once __DIR__ . '/../models/Textbaustein.class.php';

// TODO: Remove in production code
# SimpleORMap::expireTableScheme();

class ZsbTextbausteineController extends ZsbController {

    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        if (!PersonalRechte::isRoot()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
        if (Request::isAjax()) {
            $this->set_layout(null);
            $this->set_content_type('text/html;Charset=windows-1252');
        }
        Navigation::activateItem('/zsb/textbausteine');
    }

    public function index_action() {
        $this->textbausteine = Textbaustein::loadAll();
    }

    public function edit_action ($id = null)
    {
        if (Request::submitted('preview')) {
            $this->preview = true;
        } elseif (Request::isPost()) {
            $id   = $id ?: md5(uniqid('textbaustein', true));
            $code     = Request::get('code');
            $language = Request::option('language');
            $title    = Request::get('title');
            $content  = Request::get('content');

            Textbaustein::store($id, $code, $language, $title, $content);
            PageLayout::postMessage(MessageBox::success(_('Der Textbaustein wurde gespeichert.')));
            $this->redirect('zsb_textbausteine/index');
            return;
        }

        $this->id = $id;
        if ($id === null) {
            $this->code     = '';
            $this->language = 'de';
            $this->title    = '';
            $this->content  = '';
        } else {
            $t = Textbaustein::load($id);
            $this->code     = $t['code'];
            $this->language = $t['language'];
            $this->title    = $t['title'];
            $this->content  = $t['content'];
        }
    }

    public function delete_action($id)
    {
        Textbaustein::delete($id);
        PageLayout::postMessage(MessageBox::success(_('Der Textbaustein wurde gelöscht.')));
        $this->redirect('zsb_textbausteine/index');
    }

    public function preview_action($id = null)
    {
        if ($id === null && Request::optionArray('ids')) {
            $ids = Request::optionArray('ids');
        } else {
            $ids = array($id);
        }

        $preview = '';
        foreach ($ids as $id) {
            $temp = Textbaustein::load($id);
            $preview .= sprintf("#{textbaustein:%s}\n\n", $temp['code']);
        }
        $result = Textbaustein::render($preview);
        $result = formatReady($result);

        $this->render_text($result);
    }

    public function copy_from_action()
    {
        $abschluss_id   = Request::option('abschluss_id');
        $studiengang_id = Request::option('studiengang_id');

        $query = "SELECT profil_id
                  FROM stg_profil
                  WHERE abschluss_id = :abschluss_id AND fach_id = :studiengang_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':abschluss_id', $abschluss_id);
        $statement->bindValue(':studiengang_id', $studiengang_id);
        $statement->execute();
        $profil_id = $statement->fetchColumn();

        if (!$profil_id) {
            $result = false;
        } elseif ($profil_id === Request::option('profil_id')) {
            $result = true;
        } else {
            $result = Textbaustein::loadCombination($profil_id);
            foreach ($result as $code => $tb) {
                $result[$code] = array_map(function ($item) { return $item['textbaustein_id']; }, $tb);
            }
        }
        $this->render_text(json_encode($result));
    }

    public function copy_to_action()
    {
        $abschluss_id     = Request::option('abschluss_id', null);
        $studiengang_id   = Request::option('studiengang_id', null);
        $status           = Request::option('status', null);
        $profil_id        = Request::option('profil_id');
        $textcombinations = Request::getArray('textcombinations');

        $query = "SELECT profil_id
                  FROM stg_profil
                  WHERE abschluss_id = IFNULL(:abschluss_id, abschluss_id)
                    AND fach_id = IFNULL(:studiengang_id, fach_id)
                    AND status = IFNULL(:status, status)
                    AND profil_id != :profil_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':abschluss_id', $abschluss_id);
        $statement->bindValue(':studiengang_id', $studiengang_id);
        $statement->bindValue(':status', $status);
        $statement->bindValue(':profil_id', $profil_id);
        $statement->execute();
        $profil_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        foreach ($profil_ids as $profil_id) {
            Textbaustein::removeCombination($profil_id);
            foreach ($textcombinations as $code => $ids) {
                Textbaustein::addCombination($profil_id, $code, $ids);
            }
        }

        $this->render_text(json_encode(count($profil_ids)));
    }
}
