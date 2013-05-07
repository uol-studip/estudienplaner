<?php
require_once dirname(__file__).'/zsb_controller.php';
require_once 'lib/classes/DataFieldStructure.class.php';
require_once 'lib/classes/DataFieldEntry.class.php';

class ZsbDatenfelderController extends ZsbController {

    function before_filter($action, $args) {
        parent::before_filter($action, $args);
        if (!PersonalRechte::hasPermission()) {
            throw new AccessDeniedException(_("Unbefugter Zutritt!"));
            return;
        }
    }

    function datenfelder_action() {
        if (Request::get('datenfeld_id')) {
            $this->datenfelder_details();
            return;
        }

        $datafields = DataFieldStructure::getDataFieldStructures('plugin', $this->plugin->getPluginId());
        $this->datenfelder = array();
        foreach ($datafields as $datafield) {
            $this->datenfelder[] = array(
                'content' => array(
                    basename($datafield->getName(), '.profile'),
                    $datafield->getType(),
                    preg_match('/\.profile$/', $datafield->getName()) ? _('Profil') : _('Profiltext'),
                    $datafield->getCachedNumEntries(),
                    $datafield->getPriority()),
                'url' => URLHelper::getLink("?", array('datenfeld_id' => $datafield->getId())),
                'item' => $datafield
            );
        }
    }

    function datenfelder_details() {
        $datenfeld_id = Request::get('datenfeld_id');
        if ($datenfeld_id == 'neu') {
            $this->datenfeld = new DataFieldStructure();
        } else {
            $this->datenfeld = new DataFieldStructure(array('datafield_id' => $datenfeld_id));
            $this->datenfeld->load();
        }

        if (Request::get('del_request_x')) {
            $this->question = createQuestion(
                _("Wollen Sie das Datenfeld wirklich löschen?"),
                 $approveParams,
                 $disapproveParams);
        }

        if (Request::get('absenden_x')) {
            $postfix = Request::option('context') === 'profile' ? '.profile' : '';
            $this->datenfeld->setID(Request::get('datenfeld_id'));
            $this->datenfeld->setName(Request::get('name') . $postfix);
            $this->datenfeld->setType(Request::get('feldtyp'));
            $this->datenfeld->setTypeParam(Request::get('typparam'));
            $this->datenfeld->setObjectClass($this->plugin->getPluginId());
            $this->datenfeld->setObjectType('plugin');
            $this->datenfeld->setPriority(Request::get('priority', 0));
            $this->datenfeld->setEditPerms('admin');
            $this->datenfeld->setViewPerms('all');
            $this->datenfeld->store();
            $this->flash_now('success', _("Änderungen wurden übernommen."));
        }

        $this->render_template('zsb_datenfelder/details', $this->layout);
    }

    function datenfelder_delete_action () {
        $this->datenfeld = new DataFieldStructure(array('datafield_id' => Request::get('datenfeld_id')));
        if ($this->datenfeld->getObjectType() == 'plugin' && $this->datenfeld->getObjectClass() == $this->plugin->getPluginId()) {
            $this->datenfeld->delete();
        }

        $this->datenfelder_action();
    }

}