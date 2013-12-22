<?php

class RelationDetail extends AppModel {
    
    public function addRelationDetail($postReDe) {
        
        if (!empty($postReDe['text'])) {            
            $reDeData = array(
                'text' => $postReDe['text'],
            );
            
            $checkConditions = array(
                'deleted'  => AppModel::ENTRY_NOT_DELETED,
            );
            $checkConditions = array_merge($checkConditions, $reDeData);
            
            $reDeExist = $this->find('first', array('conditions' => $checkConditions));
            
            if (empty($reDeExist)) {
                $reDetail = new RelationDetail;
                $reDetail->set($reDeData);
                $reDetail->save();
                $reDeData['id'] = $reDetail->getLastInsertId();
            } else {
                $reDeData['id'] = $reDeExist['RelationDetail']['id'];
            }
        
            return $reDeData;
        }
        
        return array();
    }
}