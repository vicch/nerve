<?php

class WordSense extends AppModel {
    
    public function addSense($postSense) {
        
        $senseData = array(
            'word_id' => $postSense['word_id'],
            'pos'     => $postSense['pos'],
        );
        
        $checkConditions = array(
            'deleted'  => AppModel::ENTRY_NOT_DELETED,
        );
        $checkConditions = array_merge($checkConditions, $senseData);
        
        $order = array('order_num DESC');
        
        $senseExist = $this->find('first', array(
            'conditions' => $checkConditions, 
            'order' => $order,
        ));
        
        if (!empty($senseExist)) {
        // First sense of this 'part of speech'
            $senseData['order_num'] = intval($senseExist['WordSense']['order_num']) + 1;
        } else {
            $senseData['order_num'] = 1;
        }
        
        $senseData['meaning'] = $postSense['meaning'];
        
        $sense = new WordSense;
        $sense->set($senseData);
        $sense->save();
        $senseData['id'] = $sense->getLastInsertId();
    
        return $senseData;
    }
}
