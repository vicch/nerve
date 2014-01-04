<?php

class TopController extends AppController {
    
    public $name = 'Top';
    
    public $uses = array('Word', 'WordSense', 'Relation', 'RelationDetail');
    public $components = array('Session');
    
    const RIGHT_FORM_NAME = 'rForm';
    
    const POST_ADD_WORD_FROM = 'addWFrom';
    const POST_ADD_SENSE_FROM= 'addSFrom';
    const POST_ADD_WORD_TO   = 'addWTo';
    const POST_ADD_RELATION  = 'addRe';
    
    public function index() {
        
        $word = '';
        if (isset($this->request->query['word'])) {
            $word = $this->request->query['word'];
        }
        $query = $this->Word->queryForView($word);
        $queryResult = $this->Word->find('all', $query);
        $queryData = $this->Word->formatForView($queryResult);
        
        // $init == 0 means redirected from addX() action
        $init = (isset($this->request->query['init'])) ? $this->request->query['init'] : 1;
        $formData = $this->__initFormData($queryData, $init);
        
        $this->set('data', $queryData);
        $this->data = $formData;
        
        $this->set('selectReTypes', $this->Relation->getSelectReTypes());
        $this->set('selectLangs', $this->Word->getSelectLangs());
    }
    
    /*
     * Add word action, disabled
     */
    public function addWord() {
        $postData = $this->request->data;
        if (isset($postData)) {
            $addedWord = $this->Word->addWord($postData);
        }
        if (!empty($addedWord)) {
            $this->redirect('index?word=' . $addedWord);
        } else {
            $this->redirect('index');
        }
    }
    
    /**
     * Add all action
     */
    public function addAll() {
        $postData = $this->request->data;
        $postFData = $postData[self::RIGHT_FORM_NAME];
        
        $postType = $this->__getPostType($postData);
        
        if ($postType == self::POST_ADD_WORD_FROM) {
            $this->__addWord($postFData, 'From');
        } elseif ($postType == self::POST_ADD_SENSE_FROM) {
            $this->__addSense($postFData, 'From');
        } elseif ($postType == self::POST_ADD_RELATION) {
            $this->__addRelation($postFData);
        } elseif ($postType == self::POST_ADD_WORD_TO) {
            $this->__addWord($postFData, 'To');
        }
        
        if (!empty($postData[self::RIGHT_FORM_NAME]['wFromWord'])) {
            $this->redirect('index?init=0&word=' . $postData[self::RIGHT_FORM_NAME]['wFromWord']);
        } else {
            $this->redirect('index');
        }
    }

    private function __initFormData($queryData, $init) {
        
        // If redirected from a form POST action, keep session form data
        // Otherwise, initialize empty form data
        if ($init) {
            $formData = array();
        } else {
            $sessionFormData = $this->Session->read('formData');
            $formData = $sessionFormData[self::RIGHT_FORM_NAME];
        }
        
        if (!isset($formData['wFromId'])) {
            $wFromData = array(
                'wFromId'       => $queryData['word']['id'],
                'wFromWord'     => $queryData['word']['word'],
                'wFromLanguage' => $queryData['word']['language'],
            );
            $formData = array_merge($formData, $wFromData);
            foreach ($queryData['senses'] as $key => $sense) {
                $senseData = array(
                    'sFromId_' . $key    => $sense['id'],
                    'sFromPos_' . $key   => $sense['pos'],
                    'sFromOrder_' . $key => $sense['order_num'],
                    'sFromMng_' . $key   => $sense['meaning'],
                );
                $formData = array_merge($formData, $senseData);
            }
        }
        
        return array(self::RIGHT_FORM_NAME => $formData);
    }
    
    private function __getPostType($postData) {
        if (isset($postData[self::POST_ADD_WORD_FROM])) {
            return self::POST_ADD_WORD_FROM;
        } elseif (isset($postData[self::POST_ADD_SENSE_FROM])) {
            return self::POST_ADD_SENSE_FROM;
        } elseif (isset($postData[self::POST_ADD_RELATION])) {
            return self::POST_ADD_RELATION;
        } elseif (isset($postData[self::POST_ADD_WORD_TO])) {
            return self::POST_ADD_WORD_TO;
        }
    }
    
    private function __addWord($postFData, $type) {
        
        // Prepare data
        $wordData = array(
            'id'       => $postFData['w' . $type . 'Id'],
            'word'     => $postFData['w' . $type . 'Word'],
            'language' => $postFData['w' . $type . 'Language'],
        );
        
        // Add word
        $addedWord = $this->Word->addWord($wordData);
        
        // Set POST data for form display
        $formData[self::RIGHT_FORM_NAME]['w' . $type . 'Id'] = $addedWord['id'];
        $formData[self::RIGHT_FORM_NAME]['w' . $type . 'Word'] = $addedWord['word'];
        $formData[self::RIGHT_FORM_NAME]['w' . $type . 'Language'] = $addedWord['language'];
        $this->Session->write('formData', $formData);
    }
    
    private function __addSense($postFData, $type) {
        
        // Prepare data
        $senseData = array(
            'word_id' => $postFData['w' . $type . 'Id'],
            'pos'     => $postFData['s' . $type . 'PosNew'],
            'meaning' => $postFData['s' . $type . 'MngNew'],
        );
        
        // Add sense
        $addedSense = $this->WordSense->addSense($senseData);
        
        // Set POST data for form display
        $formData[self::RIGHT_FORM_NAME]['s' . $type . 'Id_' . $addedSense['id']]      = $addedSense['id'];
        $formData[self::RIGHT_FORM_NAME]['s' . $type . 'Pos_' . $addedSense['id']]     = $addedSense['pos'];
        $formData[self::RIGHT_FORM_NAME]['s' . $type . 'Order_' . $addedSense['id']]   = $addedSense['order_num'];
        $formData[self::RIGHT_FORM_NAME]['s' . $type . 'Mng_' . $addedSense['id']] = $addedSense['meaning'];
        $this->Session->write('formData', $formData);
    }
    
    private function __addRelation($postFData) {
        
        // Prepare relation detail data
        $reDeData = array(
            'id'   => isset($postFData['reDetailId']) ? $postFData['reDetailId'] : null,
            'text' => isset($postFData['reDetailText']) ? $postFData['reDetailText'] : null,
        );
        
        // Add relation detail
        $addedReDe = $this->RelationDetail->addRelationDetail($reDeData);
        
        // Prepare relation data
        $reData = array(
            'id'               => $postFData['reId'],
            'word_id_from'     => $postFData['wFromId'],
            'sense_id_from'    => null,
            'word_id_to'       => $postFData['wToId'],
            'sense_id_to'      => null,
            'relation_type_id' => $postFData['reType'],
            'relation_detail_id' => isset($addedReDe['id']) ? $addedReDe['id'] : null,
        );
        
        // Add relation
        $addedRe = $this->Relation->addRelation($reData);
        
        // Set POST data for form display
        $formData[self::RIGHT_FORM_NAME]['wToId'] = $postFData['wToId'];
        $formData[self::RIGHT_FORM_NAME]['wToWord'] = $postFData['wToWord'];
        $formData[self::RIGHT_FORM_NAME]['wToLanguage'] = $postFData['wToLanguage'];
        $formData[self::RIGHT_FORM_NAME]['reId'] = $addedRe['id'];
        $formData[self::RIGHT_FORM_NAME]['reType'] = $addedRe['relation_type_id'];
        $formData[self::RIGHT_FORM_NAME]['reDetailId'] = isset($addedReDe['id']) ? $addedReDe['id'] : null;
        $formData[self::RIGHT_FORM_NAME]['reDetailText'] = isset($addedReDe['text']) ? $addedReDe['text'] : null;
        $this->Session->write('formData', $formData);
    }
}
