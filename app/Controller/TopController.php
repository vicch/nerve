<?php

class TopController extends AppController {
    
    public $name = 'Top';
    
    public $uses = array('Word');
    
    public function index() {
        
        if (isset($this->request->query['word'])) {
            $word = $this->request->query['word'];
            $query = $this->Word->queryForView($word);
            $queryResult = $this->Word->find('all', $query);
            $formatResult = $this->Word->formatForView($queryResult);
            $this->set('data', $formatResult);
        }
    }
}
