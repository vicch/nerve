<?php

class Relation extends AppModel {
    
    private $__selectReTypes = array(
        '',
        1 => 'Synonym',
        2 => 'Antonym',
        3 => 'Verb -> Object',
        4 => 'Object <- Verb',
        5 => 'Subject <- Modifier',
        6 => 'Modifier -> Subject',
        7 => 'Differentiation',
        8 => 'Association',
        9 => 'Translation',
        10 => 'Sematically contain ->',
        11 => 'Sematically belong to ->',
        12 => 'Literally contain ->',
        13 => 'Literally belong to ->',
        14 => 'Explained by ->',
        15 => 'Explain ->',
    );
    
    private $__relatedReType = array(
        1 => 1,
        2 => 2,
        3 => 4,
        4 => 3,
        5 => 6,
        6 => 5,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 11,
        11 => 10,
        12 => 13,
        13 => 12,
        14 => 15,
        15 => 14,
    );
    
    public function getSelectReTypes() {
        return $this->__selectReTypes;
    }
    
    public function addRelation($postRe) {
        
        $reData = array(
            'word_id_from'     => $postRe['word_id_from'],
            'sense_id_from'    => $postRe['sense_id_from'],
            'word_id_to'       => $postRe['word_id_to'],
            'sense_id_to'      => $postRe['sense_id_to'],
        );
        
        $checkConditions = array(
            'deleted'  => AppModel::ENTRY_NOT_DELETED,
        );
        $checkConditions = array_merge($checkConditions, $reData);
        
        $reExist = $this->find('count', array('conditions' => $checkConditions));
        
        if ($reExist == 0) {
            
            // Original relation
            $relation = new Relation;
            $reData['relation_type_id'] = $postRe['relation_type_id'];
            // $reData['relation_detail_id'] = ;
            $relation->set($reData);
            $relation->save();
            $reData['id'] = $relation->getLastInsertId();
            
            // Reversed relation
            $reverseRe = new Relation;
            $reverseReData = array(
                'parent_id'        => $reData['id'],
                'word_id_from'     => $reData['word_id_to'],
                'sense_id_from'    => $reData['sense_id_to'],
                'word_id_to'       => $reData['word_id_from'],
                'sende_id_to'      => $reData['sense_id_from'],
                'relation_type_id' => $this->__relatedReType[$reData['relation_type_id']],
                // 'relation_detail_id',
            );
            $reverseRe->set($reverseReData);
            $reverseRe->save();
        }
    
        return $reData;
    }
}