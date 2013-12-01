<?php

class Word extends AppModel {
    
    public function queryForView($word, $language = NULL) {
        $fields = array(
            'Word.id',
            'Word.language',
            'Word.word',
            'Senses.id',
            'Senses.pos',
            'Senses.order_num',
            'Senses.meaning',
            'Relation.id',
            'Relation.parent_id',
            'Relation.sense_id_from',
            'Relation.sense_id_to',
            'WordTo.id',
            'WordTo.language',
            'WordTo.word',
            'SensesTo.id',
            'SensesTo.pos',
            'SensesTo.order_num',
            'SensesTo.meaning',
            'Type.id',
            'Type.type',
            'Type.related_type_id',
            'Detail.id',
            'Detail.text',
        );
        $joins = array(
            array(
                'alias'      => 'Senses',
                'table'      => 'word_senses',
                'type'       => 'left',
                'conditions' => 'Word.id = Senses.word_id',
            ),
            array(
                'alias'      => 'Relation',
                'table'      => 'relations',
                'type'       => 'left',
                'conditions' => 'Word.id = Relation.word_id_from',
            ),
            array(
                'alias'      => 'WordTo',
                'table'      => 'words',
                'type'       => 'left',
                'conditions' => 'Relation.word_id_to = WordTo.id',
            ),
            array(
                'alias'      => 'SensesTo',
                'table'      => 'word_senses',
                'type'       => 'left',
                'conditions' => 'WordTo.id = SensesTo.word_id',
            ),
            array(
                'alias'      => 'Type',
                'table'      => 'relation_types',
                'type'       => 'inner',
                'conditions' => 'Relation.relation_type_id = Type.id',
            ),
            array(
                'alias'      => 'Detail',
                'table'      => 'relation_details',
                'type'       => 'left',
                'conditions' => 'Relation.relation_detail_id = Detail.id',
            ),
        );
        $conditions = array(
            'Word.word' => $word,
        );
        return array(
            'fields'     => $fields,
            'joins'      => $joins,
            'conditions' => $conditions,
        );
    }
    
    public function formatForView($queryResult) {
        $formatResult = array();
        $formatResult['word']      = $queryResult[0]['Word'];
        $formatResult['senses']    = $queryResult[0]['Senses'];
        $formatResult['relations'] = array();
        foreach ($queryResult as $index => $record) {
            $formatResult['relations'][] = array(
                'relation'  => $record['Relation'],
                'type'      => $record['Type'],
                'detail'    => $record['Detail'],
                'word_to'   => $record['WordTo'],
                'senses_to' => $record['SensesTo'],
            );
        }
        return $formatResult;
    }
    
}
