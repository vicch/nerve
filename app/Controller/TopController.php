<?php

class TopController extends AppController {
    
    public $name = 'Top';
    
    public $uses = array('Word');
    
    public function index() {
        $data = $this->Word->find('all');
    }
}
