<?php

class TopController extends AppController {
    
    public $name = 'Top';
    
    public $uses = array('Word', 'Relation');
    public $components = array('Session');
    
    const RIGHT_FORM_NAME = 'rForm';
    
    const POST_ADD_WORD_FROM = 'addWFrom';
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
        
        $init = (isset($this->request->query['init'])) ? $this->request->query['init'] : 1;
        $formData = $this->__initFormData($queryData, $init);
        
        // echo '<pre>' . var_export($formData) . '</pre>';
        // exit;
        
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
        
        $postType = $this->__getPostType($postData);
        
        if ($postType == self::POST_ADD_WORD_FROM) {
            $this->__addWord($postData, 'From');
        } elseif ($postType == self::POST_ADD_WORD_TO) {
            $this->__addWord($postData, 'To');
        } elseif ($postType == self::POST_ADD_RELATION) {
            $this->__addRelation($postData);
        }
        
        if (!empty($postData[self::RIGHT_FORM_NAME]['wFromWord'])) {
            $this->redirect('index?init=0&word=' . $postData['rForm']['wFromWord']);
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
        }
        
        return array(self::RIGHT_FORM_NAME => $formData);
    }
    
    private function __getPostType($postData) {
        if (isset($postData[self::POST_ADD_WORD_FROM])) {
            return self::POST_ADD_WORD_FROM;
        } elseif (isset($postData[self::POST_ADD_WORD_TO])) {
            return self::POST_ADD_WORD_TO;
        } elseif (isset($postData[self::POST_ADD_RELATION])) {
            return self::POST_ADD_RELATION;
        }
    }
    
    private function __addWord($postData, $type) {
        
        // Prepare data
        $wordData = array(
            'id'       => $postData[self::RIGHT_FORM_NAME]['w' . $type . 'Id'],
            'word'     => $postData[self::RIGHT_FORM_NAME]['w' . $type . 'Word'],
            'language' => $postData[self::RIGHT_FORM_NAME]['w' . $type . 'Language'],
        );
        
        // Add word
        $addedWord = $this->Word->addWord($wordData);
        
        // Set POST data for form display
        $formData[self::RIGHT_FORM_NAME]['w' . $type . 'Id'] = $addedWord['id'];
        $formData[self::RIGHT_FORM_NAME]['w' . $type . 'Word'] = $addedWord['word'];
        $formData[self::RIGHT_FORM_NAME]['w' . $type . 'Language'] = $addedWord['language'];
        $this->Session->write('formData', $formData);
    }
    
    private function __addRelation($postData) {
        
        // Prepare data
        $reData = array(
            'id'               => $postData[self::RIGHT_FORM_NAME]['reId'],
            'word_id_from'     => $postData[self::RIGHT_FORM_NAME]['wFromId'],
            'sense_id_from'    => null,
            'word_id_to'       => $postData[self::RIGHT_FORM_NAME]['wToId'],
            'sense_id_to'      => null,
            'relation_type_id' => $postData[self::RIGHT_FORM_NAME]['reType'],
            'relation_detail_id' => null,
        );
        
        // Add relation
        $addedRe = $this->Relation->addRelation($reData);
        
        // Set POST data for form display
        $formData[self::RIGHT_FORM_NAME]['wToId'] = $postData[self::RIGHT_FORM_NAME]['wToId'];
        $formData[self::RIGHT_FORM_NAME]['wToWord'] = $postData[self::RIGHT_FORM_NAME]['wToWord'];
        $formData[self::RIGHT_FORM_NAME]['wToLanguage'] = $postData[self::RIGHT_FORM_NAME]['wToLanguage'];
        $formData[self::RIGHT_FORM_NAME]['reId'] = $addedRe['id'];
        $formData[self::RIGHT_FORM_NAME]['reType'] = $addedRe['relation_type_id'];
        $this->Session->write('formData', $formData);
    }
}
